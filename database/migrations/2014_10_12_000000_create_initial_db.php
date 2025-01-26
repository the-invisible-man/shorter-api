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
        Schema::create('urls', function (Blueprint $table) {
            $table->bigIncrements('id')->primary();
            $table->string('short_url', 7)->unique()->nullable();
            $table->text('long_url');

            $table->timestamps();
        });

        Schema::create('url_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->bigInteger('url_id')->unsigned()->index();
            $table->integer('count')->default(0);
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
