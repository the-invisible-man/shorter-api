<?php

namespace App\Console\Commands;

use App\Packages\Organizations\Models\Client;
use App\Packages\Organizations\Models\LawFirm;
use App\Packages\Organizations\Models\Organization;
use App\Packages\Organizations\Models\Provider;
use App\Packages\Organizations\Models\Service;
use App\Packages\Organizations\Models\ServiceRevision;
use App\Packages\Organizations\ServicesService;
use App\Packages\Transactions\VisitsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ImportServicesAndVisits extends Command
{
    public const DEFAULT_VISIT_DURATION = 60;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-import:services
                            {csv_file : Path to the CSV file to import}
                            {--dry : Run through the script without writing the data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports services and service_revisions given a CSV file.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $csv = $this->argument('csv_file');

        $provider = Provider::first();
        $organization = Organization::first();

        // Check that the CSV file exists
        if (! file_exists($csv)) {
            $this->error("The file \"{$csv}\" does not exist.");

            return 0;
        }

        // We want to sort by date so that we don't insert more
        $collection = $this->toCollection($csv);

        DB::beginTransaction();

        $rowCounter = 1;

        foreach ($collection as $row) {
            // First, we're either creating or inserting a new service and/or service revision

            // Let's grab the service
            $service = Service::where('cpt_code', $row['cpt_code'])->first();

            if (! $service) {
                // We need to create this service
                $service = $this->getServicesService()->create($organization, $provider, [
                    'description' => $row['description'],
                    'cpt_code' => $row['cpt_code'],
                    'provider_rate' => $row['provider_rate'] * 100,
                    'lp_rate' => $row['lp_rate'] * 100,
                    'lop_rate' => $row['lop_rate'] * 100,
                    'duration' => self::DEFAULT_VISIT_DURATION, // They're all 60
                ]);
            }

            // Do we need to create a new revision
            if ($this->shouldCreateNewRevision($service->service_revision, $row)) {
                $this->getServicesService()->update($service, [
                    'provider_rate' => $row['provider_rate'] * 100,
                    'lp_rate' => $row['lp_rate'] * 100,
                    'lop_rate' => $row['lop_rate'] * 100,
                ]);
            }

            // Now we need to fetch the client details
            $client = Client::where('patient_id', $row['patient_id'])->first();
            $lawFirm = LawFirm::where('name', trim($row['law_firm']))->first();

            $this->info("Processing record for patient_id {$row['patient_id']}");

            $this->getVisitService()->create($service, $client, $lawFirm, [
                'total_duration' => self::DEFAULT_VISIT_DURATION,
                'csv_id' => $row['csv_id'],
                'service_provided_at' => $row['service_provided_at'],
            ]);

            $rowCounter++;
        }

        $this->info("\nImport complete. Total visits imported: {$rowCounter}");

        $shouldCommit = $this->shouldWriteToDB()
            && strtolower($this->ask('Do you wish to commit? [y/N]', 'n')) === 'y';

        if ($shouldCommit) {
            DB::commit();
            $this->info('All changes written successfully');
        } else {
            $this->info('All changes discarded');
        }

        return 0;
    }

    /**
     * @param ServiceRevision $revision
     * @param array           $row
     *
     * @return bool
     */
    protected function shouldCreateNewRevision(ServiceRevision $revision, array $row): bool
    {
        return $revision->lp_rate !== intval($row['lp_rate'])
            || $revision->lop_rate !== intval($row['lop_rate'])
            || $revision->provider_rate !== intval($row['provider_rate']);
    }

    /**
     * @param string $csv
     *
     * @return Collection|null
     */
    protected function toCollection(string $csv): ?Collection
    {
        $columnMappings = [];
        $transformed = [];

        // Check that necessary CSV file headings exist
        $handle = fopen($csv, 'r');

        if (! $handle) {
            $this->error("Unable to open file: \"{$csv}\"");

            return null;
        }

        while (($data = fgetcsv($handle)) !== false) {
            if (! $columnMappings) {
                $columnMappings = array_flip($data);

                if (count($data) !== count($columnMappings)) {
                    $this->error('There was a duplicate column. Unable to continue.');

                    return null;
                }

                continue;
            }

            $row = [
                'csv_id' => $data[$columnMappings['csv_id']],
                'patient_id' => $data[$columnMappings['patient_id']],
                'service_provided_at' => $data[$columnMappings['service_provided_at']],
                'cpt_code' => $data[$columnMappings['cpt_code']],
                'description' => $data[$columnMappings['description']],
                'law_firm' => $data[$columnMappings['law_firm']],
                'provider_rate' => $data[$columnMappings['provider_rate']],
                'lp_rate' => $data[$columnMappings['lp_rate']],
                'lop_rate' => $data[$columnMappings['lop_rate']],
            ];

            $transformed[] = $row;
        }

        // Wanna sort by date, so that we insert visits and service_revisions according to the date they were created on
        return collect($transformed)->sortBy(function ($row) {
            $date = Carbon::create($row['service_provided_at']);

            return $date->timestamp;
        });
    }

    /**
     * @return bool
     */
    protected function shouldWriteToDB(): bool
    {
        // When --dry is passed, $this->option() will return true, so we negate it
        return ! $this->option('dry');
    }

    /**
     * @return ServicesService
     */
    protected function getServicesService(): ServicesService
    {
        return app(ServicesService::class);
    }

    /**
     * @return VisitsService
     */
    protected function getVisitService(): VisitsService
    {
        return app(VisitsService::class);
    }
}
