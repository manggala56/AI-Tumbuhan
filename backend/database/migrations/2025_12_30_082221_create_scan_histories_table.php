<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plant_type_id')->constrained()->onDelete('cascade');
            $table->string('image_path');
            $table->string('ai_result')->nullable()->comment('Disease name from main model');
            $table->decimal('ai_confidence', 5, 2)->nullable()->comment('Confidence score 0-100');
            $table->string('ai_model_version', 50)->nullable();
            $table->string('shadow_result')->nullable();
            $table->decimal('shadow_confidence', 5, 2)->nullable();
            $table->string('shadow_model_version', 50)->nullable();
            $table->text('treatment_advice')->nullable();
            $table->unsignedTinyInteger('user_rating')->nullable()->comment('1-5 stars');
            $table->text('user_comment')->nullable();
            $table->string('researcher_correction')->nullable()->comment('Corrected disease name');
            $table->foreignId('corrected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('corrected_at')->nullable();
            $table->boolean('is_training_ready')->default(false);
            $table->timestamp('approved_for_training_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->index('user_rating');
            $table->index('ai_confidence');
            $table->index('is_training_ready');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('scan_histories');
    }
};
