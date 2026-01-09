<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ScanHistory;
class FeedbackController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);
        $scan = ScanHistory::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        $scan->update([
            'user_rating' => $request->rating,
            'user_comment' => $request->comment,
        ]);
        return response()->json(['message' => 'Feedback submitted successfully']);
    }
}
