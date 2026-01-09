<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_jobs', function (Blueprint $table) {
            $table->string('dataset_path')->nullable()->after('job_id');
            $table->string('dataset_url')->nullable()->after('dataset_path');
        });
    }
    public function down(): void
    {
        Schema::table('training_jobs', function (Blueprint $table) {
            $table->dropColumn(['dataset_path', 'dataset_url']);
        });
    }
};
