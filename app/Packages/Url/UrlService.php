<?php

namespace App\Packages\Url;

use App\Packages\Url\Events\UrlVisited;
use App\Packages\Url\Exceptions\MaxRowLimit;
use App\Packages\Url\Jobs\FireBulkUrlCreateEvents;
use App\Packages\Url\Jobs\ProcessBulkCsv;
use App\Packages\Url\Models\BulkCsvJob;
use App\Packages\Url\Models\Url;
use App\Packages\Url\Repositories\JobRepository;
use App\Packages\Url\Structs\CsvProcessComplete;
use Illuminate\Database\DatabaseManager;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Broadcast;
use League\Csv\Reader;
use League\Csv\Writer;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\InvalidArgumentException;

class UrlService
{
    /**
     * @param UrlReadService $urlReadService
     * @param UrlWriteService $urlWriteService
     * @param Dispatcher $dispatcher
     * @param DatabaseManager $databaseManager
     * @param LoggerInterface $logger
     * @param JobRepository $jobRepository
     */
    public function __construct(
        protected UrlReadService $urlReadService,
        protected UrlWriteService  $urlWriteService,
        protected Dispatcher  $dispatcher,
        protected DatabaseManager $databaseManager,
        protected LoggerInterface $logger,
        protected JobRepository $jobRepository,
        protected CsvBulkJobService $jobService,
    ) {
    }

    /**
     * @param string $shortUrl
     * @return Url|null
     * @throws InvalidArgumentException
     */
    public function visitUrl(string $shortUrl): ?Url
    {
        $url = $this->urlReadService->findByShortUrl($shortUrl);

        if ($url) {
            $this->dispatcher->dispatch(new UrlVisited($url));
        }

        return $url;
    }


    /**
     * @param ProcessBulkCsv $job
     * @return bool
     * @throws \Throwable
     */
    public function createFromCsv(ProcessBulkCsv $job): bool
    {
        try {
            $this->jobService->updateJobStatus(
                $job->getJobRecord(),
                JobRepository::STATUS['in-progress'],
                0
            );

            $result = $this->processCsv($job);
        } catch (\Exception $e) {
            $this->logger->error("Failed to process CSV file", [
                'jobId' => $job->getJobId(),
                'message' => $e->getMessage(),
                'origin' => $job->getOrigin(),
                'destination' => $job->getDestination(),
                'total_rows' => $job->getTotalRows(),
                'raw_exception' => $e,
            ]);

            $this->jobRepository->update($job->getJobRecord(), JobRepository::STATUS['failed']);

            return false;
        }

        if ($result->getUrlIds()) {
            // We've silenced all events fired by UrlWriteService::create()
            // so that we only  fire them if the entirety  of the whole job
            // was  successful. To finish  processing  as fast as possible,
            // the job below  will be  queued, and when executed, will fire
            // in a different process all the UrlCreated events we silenced.
            //
            // This allows us to return the results to the user as fast as
            // possible, keeping the experience seamless, and event driven
            dispatch(new FireBulkUrlCreateEvents($result->getUrlIds()));
        }

        $this->jobService->updateJobStatus(
            $job->getJobRecord(),
            JobRepository::STATUS['completed'],
            $job->getTotalRows()
        );

        return true;
    }

    /**
     * @param ProcessBulkCsv $job
     * @param int $maxAttempts
     * @return CsvProcessComplete
     * @throws \Throwable
     */
    protected function processCsv(ProcessBulkCsv $job, int $maxAttempts = 3): CsvProcessComplete
    {
        return $this->databaseManager->transaction(function () use ($job) {
            $socketUpdateInterval = $this->calculateUpdateInterval($job->getTotalRows());
            $outputFile = $this->createOutputStream($job->getDestination());

            $ids = [];
            $processed = 0;
            $failures = 0;

            foreach ($this->readFile($job->getOrigin()) as $row) {
                try {
                    // We are running in a nested transaction, therefore failures
                    // caused by the two-step process of the create() method won't
                    // cause the overall job to fail. Ideally, such a thing shouldn't
                    // happen. But if it does, we'll just skip this url and mark it
                    // as empty in the output CSV.
                    $longUrl = $row[0];

                    // Crate the URL with cache and events off for bulk transactions.
                    $url = $this->urlWriteService->create($longUrl, false, false);

                    $destination = $url->toDestinationUrl();
                    $ids[] = $url->id;
                } catch (\Exception $e) {
                    $failures++;

                    // We will include the row, but no URL so that the user
                    // can visually check which URLs failed so that they can
                    // retry in a subsequent request.
                    $destination = '';

                    $this->logger->error("Failed to create single url in bulk create", [
                        'jobId' => $job->getJobId(),
                        'long_url' => $longUrl,
                        'message' => $e->getMessage(),
                        'raw_exception' => $e,
                    ]);

                    continue; // The "finally" block will still execute.
                } finally {
                    $outputFile->insertOne([
                        $longUrl,
                        $destination,
                    ]);

                    $processed++;

                    if ($this->shouldBroadcast($processed, $job->getTotalRows(), $socketUpdateInterval)) {
                        $this->jobService->broadcastJobProgress($job->getJobRecord(), $processed);
                    }
                }
            }

            return new CsvProcessComplete($job, $processed, $failures, $ids);
        }, $maxAttempts);
    }

    /**
     * This determines how often we push websocket evens to the client.
     * Managing the total number of events allows the application to send
     * fewer events which is advantageous in the case of Pusher or some
     * other third-party websocket service with event-based pricing tiers.
     *
     * @param int $totalRows
     * @return int
     */
    protected function calculateUpdateInterval(int $totalRows): int
    {
        // If the file is more than 20 rows, then we put a
        // buffer on the update interval by dividing the total
        // rows into 20 chunks. This way we'll only broadcast
        // at most 20 events, keeping the broadcasting lightweight.
        return $totalRows <= 100 ? 1 : (int) ceil($totalRows / 20);
    }

    /**
     * @param int $totalProcessed
     * @param int $totalRows
     * @param int $updateInterval
     * @return bool
     */
    protected function shouldBroadcast(int $totalProcessed, int $totalRows, int $updateInterval): bool
    {
        // We'll either broadcast if we're at the calculated interval
        // or if we've reached the total number of rows.
        return ($totalProcessed % $updateInterval) === 0 || $totalProcessed === $totalRows;
    }

    /**
     * Using a generator, so we can stream the CSV file and avoid
     * loading the entire thing into memory.
     *
     * @param string $origin
     * @return \Generator
     * @throws \League\Csv\Exception
     * @throws \League\Csv\UnavailableStream
     */
    protected function readFile(string $origin): \Generator
    {
        $csv = Reader::createFromPath($origin, 'r');

        foreach ($csv->getRecords() as $row) {
            yield $row;
        }
    }

    /**
     * @param string $destination
     * @return Writer
     * @throws \League\Csv\UnavailableStream
     */
    protected function createOutputStream(string $destination): Writer
    {
        return Writer::createFromPath($destination);
    }
}
