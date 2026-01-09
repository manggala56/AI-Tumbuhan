<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\TrainingJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
class TrainingJobController extends Controller
{
    public function pending()
    {
        $job = TrainingJob::where('status', 'pending')
            ->with('plantType')
            ->orderBy('created_at', 'asc')
            ->first();
        if (!$job) {
            return response()->json(['message' => 'No pending jobs.'], 204);
        }
        return response()->json([
            'id' => $job->id,
            'plant_type_id' => $job->plant_type_id,
            'plant_type_slug' => $job->plantType->slug,
            'learning_rate' => $job->learning_rate,
            'epochs' => $job->epochs,
            'batch_size' => $job->batch_size,
            'dataset_url' => url("/api/v1/datasets/{$job->plant_type_id}/download"),
        ]);
    }
    public function start($id)
    {
        $job = TrainingJob::findOrFail($id);
        if ($job->status === 'completed') {
            return response()->json(['error' => 'Job already completed'], 400);
        }
        $job->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
        return response()->json(['status' => 'started']);
    }
    public function complete(Request $request, $id)
    {
        $job = TrainingJob::findOrFail($id);
        $request->validate([
            'model_file' => 'required|file',
            'accuracy' => 'required|numeric',
        ]);
        $file = $request->file('model_file');
        $fileName = 'models/job_' . $job->id . '_' . now()->timestamp . '.h5';
        $path = $file->storeAs('training_models', $fileName, 'public');
        $job->update([
            'status' => 'completed',
            'completed_at' => now(),
            'final_accuracy' => $request->accuracy * 100, 
            'dataset_path' => $path, 
            'error_message' => null,
            'training_time_seconds' => $job->started_at ? now()->diffInSeconds($job->started_at) : null,
        ]);
        try {
            $aiUrl = config('services.ai.url'); 
            if ($aiUrl) {
                $fileStream = fopen(Storage::disk('public')->path($path), 'r');
                $response = Http::attach(
                    'file', $fileStream, 'shadow_model.h5'
                )->post($aiUrl . '/deploy/shadow');
                if ($response->successful()) {
                }
            }
        } catch (\Exception $e) {
        }
        return response()->json(['status' => 'completed', 'model_path' => $path]);
    }
    public function failed(Request $request, $id)
    {
        $job = TrainingJob::findOrFail($id);
        $job->update([
            'status' => 'failed',
            'completed_at' => now(),
            'error_message' => $request->input('error', 'Unknown error'),
        ]);
        return response()->json(['status' => 'failed_recorded']);
    }
}
