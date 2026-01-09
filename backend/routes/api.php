<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\PlantTypeController;
use App\Http\Controllers\Api\V1\ScanController;
use App\Http\Controllers\Api\V1\FeedbackController;
use App\Http\Controllers\Api\V1\HistoryController;
use App\Http\Controllers\Api\V1\DatasetController;
use App\Http\Controllers\Api\V1\TrainingJobController;
Route::prefix('v1')->group(function () {
    Route::get('/plant-types', [PlantTypeController::class, 'index']);
    Route::get('/diseases', [App\Http\Controllers\Api\V1\DiseaseDefinitionController::class, 'index']);
    Route::get('/training-jobs/pending', [TrainingJobController::class, 'pending']); 
    Route::post('/training-jobs/{id}/start', [TrainingJobController::class, 'start']);
    Route::post('/training-jobs/{id}/complete', [TrainingJobController::class, 'complete']);
    Route::post('/training-jobs/{id}/failed', [TrainingJobController::class, 'failed']);
    Route::get('/datasets/{id}/download', [DatasetController::class, 'download']);
    Route::post('/scan', [ScanController::class, 'store']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/history', [HistoryController::class, 'index']);
        Route::post('/scan/{id}/feedback', [FeedbackController::class, 'store']);
    });
});
