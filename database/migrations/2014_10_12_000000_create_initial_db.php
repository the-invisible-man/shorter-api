<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // URL DOMAIN
        Schema::create('urls', function (Blueprint $table) {
            $table->bigIncrements('id')->primary();
            $table->string('short_url', 7)->unique()->nullable();
            $table->text('long_url');

            $table->timestamps();
        });

        Schema::create('bulk_csv_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('status', [
                'pending',
                'in-progress',
                'failed',
                'completed'
            ])->default('pending');

            $table->string('original_csv_path');
            $table->string('destination_csv_path');
            $table->integer('total_rows')->unsigned();

            $table->timestamps();
        });

        // ANALYTICS DOMAIN
        Schema::create('url_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('path')->unique()->index();
            $table->integer('count')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identifiers');
        Schema::dropIfExists('short_urls');
        Schema::dropIfExists('analytics');
    }
};
