<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plant_type_id')->nullable()->constrained()->onDelete('cascade')->comment('NULL for universal model');
            $table->string('version_name');
            $table->string('file_path');
            $table->decimal('accuracy', 5, 2)->nullable();
            $table->decimal('precision_score', 5, 2)->nullable();
            $table->decimal('recall_score', 5, 2)->nullable();
            $table->decimal('f1_score', 5, 2)->nullable();
            $table->timestamp('trained_at')->nullable();
            $table->integer('training_samples')->nullable();
            $table->integer('epochs')->nullable();
            $table->decimal('learning_rate', 10, 8)->nullable();
            $table->boolean('is_active')->default(false)->comment('Is this the production model?');
            $table->boolean('is_shadow')->default(false)->comment('Is this the shadow/candidate model?');
            $table->timestamp('deployed_at')->nullable();
            $table->foreignId('deployed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('is_active');
            $table->index('is_shadow');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('model_versions');
    }
};
