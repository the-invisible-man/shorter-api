<?php

namespace App\Packages\Url\Models;

use App\Model;
use Carbon\Carbon;

/**
 * @property string $id
 * @property string $status
 * @property string $original_csv_path
 * @property string $destination_csv_path
 * @property string $total_rows
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class BulkCsvJob extends Model
{

}
