<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ScanHistory;
use Illuminate\Support\Facades\Storage;
class HistoryController extends Controller
{
    public function index()
    {
        $history = ScanHistory::where('user_id', auth()->id())
            ->with('plantType')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        $history->getCollection()->transform(function ($scan) {
            return [
                'id' => $scan->id,
                'plant_type' => $scan->plantType->name ?? 'Unknown',
                'scan_image_url' => Storage::url($scan->image_path),
                'disease_name' => $scan->ai_result,
                'confidence_score' => $scan->ai_confidence,
                'created_at' => $scan->created_at,
            ];
        });
        return response()->json($history);
    }
}
