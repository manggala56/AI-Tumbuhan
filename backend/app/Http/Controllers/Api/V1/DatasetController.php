<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\ScanHistory;
use App\Models\PlantType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Str;
class DatasetController extends Controller
{
    public function download(Request $request, $plantTypeId)
    {
        $plantType = PlantType::findOrFail($plantTypeId);
        $scans = ScanHistory::where('plant_type_id', $plantTypeId)
            ->where('is_training_ready', true)
            ->get();
        if ($scans->isEmpty()) {
            return response()->json(['error' => 'No approved training data found for this plant type.'], 404);
        }
        $zipFileName = 'dataset_' . $plantType->slug . '_' . now()->timestamp . '.zip';
        $zipFilePath = storage_path('app/public/datasets/' . $zipFileName);
        if (!file_exists(dirname($zipFilePath))) {
            mkdir(dirname($zipFilePath), 0755, true);
        }
        $zip = new ZipArchive;
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($scans as $scan) {
                $label = $scan->researcher_correction ?? $scan->ai_result;
                if (empty($label)) continue; 
                $sourcePath = Storage::disk('public')->path($scan->image_path);
                if (file_exists($sourcePath)) {
                    $fileName = basename($scan->image_path);
                    $zip->addFile($sourcePath, $label . '/' . $fileName);
                }
            }
            $zip->close();
        } else {
            return response()->json(['error' => 'Failed to create zip file'], 500);
        }
        $url = Storage::url('datasets/' . $zipFileName);
        return response()->json([
            'download_url' => url($url),
            'file_name' => $zipFileName,
            'image_count' => $scans->count()
        ]);
    }
}
