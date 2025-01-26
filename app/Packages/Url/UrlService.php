<?php

namespace App\Packages\Url;

use App\Packages\Url\Events\UrlVisited;
use App\Packages\Url\Jobs\ProcessBulkUrlCreated;
use App\Packages\Url\Models\Url;
use Illuminate\Database\DatabaseManager;
use Illuminate\Events\Dispatcher;
use Psr\Log\LoggerInterface;

class UrlService
{
    /**
     * @param UrlReadService $urlReadService
     * @param UrlWriteService $urlWriteService
     * @param Dispatcher $dispatcher
     * @param DatabaseManager $databaseManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected UrlReadService $urlReadService,
        protected UrlWriteService  $urlWriteService,
        protected Dispatcher  $dispatcher,
        protected DatabaseManager $databaseManager,
        protected LoggerInterface $logger,
    ) {
    }

    /**
     * @param string $shortUrl
     * @return Url
     */
    public function route(string $shortUrl): Url
    {
        $url = $this->urlReadService->findByShortUrl($shortUrl);

        $this->dispatcher->dispatch(new UrlVisited($url));

        return $url;
    }

    /**
     * @param string $jobId
     * @param string $origin
     * @param int $totalRows
     * @param string $destination
     * @return bool
     * @throws \Throwable
     */
    public function createFromCsv(string $jobId, string $origin, int $totalRows, string $destination): bool
    {
        try {
            $ids = $this->processCsv($origin, $totalRows, $destination);
        } catch (\Exception $e) {
            $this->logger->error("Failed to process CSV file", [
                'jobId' => $jobId,
                'message' => $e->getMessage(),
                'origin' => $origin,
                'destination' => $destination,
                'totalRows' => $totalRows,
                'raw_exception' => $e,
            ]);

            return false;
        }

        if ($ids) {
            // We've silenced all events fired by UrlWriteService::create()
            // so that we only  fire them if the entirety  of the whole job
            // was  successful. To finish  processing  as fast as possible,
            // the job below  will be  queued, and when executed, will fire
            // all the UrlCreated events we silenced in a different process.
            // This allows us to return the results to the user as fast as
            // possible and keeping experience seamless.
            dispatch(new ProcessBulkUrlCreated($ids));
        }

        return true;
    }

    /**
     * @param string $origin
     * @param int $totalRows
     * @param string $destination
     * @param int $maxAttempts
     *
     * @return array
     *
     * @throws \Throwable
     */
    protected function processCsv(string $origin, int $totalRows, string $destination, int $maxAttempts = 3): array
    {
        return $this->databaseManager->transaction(function () use ($totalRows, $origin, $destination) {
            $updateInternal = $this->calculateUpdateInterval($totalRows);
            $processed = 0;
            $ids = [];

            foreach ($this->readFile($origin) as $row) {
                $url = $this->urlWriteService->create($row[0], false, false);

                $ids[] = $url->id;

                $processed++;

                if ($this->shouldBroadcast($processed, $totalRows, $updateInternal)) {
                    $this->broadcastProgress($processed, $totalRows);
                }
            }

            return $ids;
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
        // buffer on the update internal and send updates
        // only every 20 rows processed.
        return $totalRows <= 20 ? 1 : (int) ceil($totalRows / 20);
    }

    /**
     * @param int $totalProcessed
     * @param int $totalRows
     * @param int $updateInterval
     * @return bool
     */
    protected function shouldBroadcast(int $totalProcessed, int $totalRows, int $updateInterval): bool
    {
        return ($totalProcessed % $updateInterval) === 0 || $totalProcessed === $totalRows;
    }

    /**
     * Using a generator, so we can stream the CSV file and avoid
     * loading the entire thing into memory.
     *
     * @param string $origin
     * @return \Generator
     */
    protected function readFile(string $origin): \Generator
    {

    }

    /**
     * @param int $processed
     * @param int $totalRows
     *
     * @return void
     */
    protected function broadcastProgress(int $processed, int $totalRows): void
    {

    }
}
