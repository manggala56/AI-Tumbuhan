<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plant_type_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('job_id')->unique()->comment('Job ID from AI service');
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->decimal('learning_rate', 10, 8)->nullable();
            $table->integer('epochs')->nullable();
            $table->integer('batch_size')->nullable();
            $table->decimal('final_accuracy', 5, 2)->nullable();
            $table->integer('training_time_seconds')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('triggered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->index('status');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('training_jobs');
    }
};
