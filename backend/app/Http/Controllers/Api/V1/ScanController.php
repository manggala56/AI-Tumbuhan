<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ScanHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
class ScanController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'plant_type_id' => 'required|exists:plant_types,id',
            'image' => 'required|image|max:10240', 
        ]);
        $imagePath = $request->file('image')->store('scans', 'public');
        $scan = ScanHistory::create([
            'user_id' => auth()->id(),
            'plant_type_id' => $request->plant_type_id,
            'image_path' => $imagePath,
        ]);
        $plantType = $scan->plantType;
        set_time_limit(120); 
        try {
            \Log::info("Sending image to AI Service at " . config('services.ai.url') . '/predict');
            $aiResponse = Http::timeout(60) 
                ->attach(
                    'file',
                    Storage::disk('public')->get($imagePath),
                    'image.jpg'
                )->post(config('services.ai.url') . '/predict', [
                    'plant_type' => $plantType->slug,
                ]);
            \Log::info("AI Service Response Status: " . $aiResponse->status());
            if ($aiResponse->successful()) {
                $aiData = $aiResponse->json();
                $scan->update([
                    'ai_result' => $aiData['main_prediction']['label'] ?? null,
                    'ai_confidence' => isset($aiData['main_prediction']) ? ($aiData['main_prediction']['confidence'] * 100) : null,
                    'ai_model_version' => $aiData['main_prediction']['model_version'] ?? null,
                    'shadow_result' => $aiData['shadow_prediction']['label'] ?? null,
                    'shadow_confidence' => isset($aiData['shadow_prediction']) ? ($aiData['shadow_prediction']['confidence'] * 100) : null,
                    'shadow_model_version' => $aiData['shadow_prediction']['model_version'] ?? null,
                ]);
                if ($scan->ai_confidence > 80 && $scan->ai_result && $scan->ai_result !== 'Background without leaves') {
                    $treatmentAdvice = $this->generateTreatmentAdvice(
                        $plantType->name,
                        $scan->ai_result,
                        $scan->ai_confidence
                    );
                    $scan->update(['treatment_advice' => $treatmentAdvice]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('AI Service Error: ' . $e->getMessage());
        }
        return response()->json([
            'id' => $scan->id,
            'scan_image_url' => Storage::url($scan->image_path),
            'disease_name' => $scan->ai_result,
            'confidence_score' => $scan->ai_confidence,
            'treatment_advice' => $scan->treatment_advice,
            'created_at' => $scan->created_at,
        ]);
    }
    private function generateTreatmentAdvice($plantType, $disease, $confidence)
    {
        $apiKey = config('services.gemini.key');
        if (!$apiKey) {
            \Log::warning('Gemini API Key is missing.');
            return "Saran pengobatan belum tersedia (API Key missing).";
        }
        $prompt = "Berikan saran pengobatan praktis dan langkah-langkah penanganan untuk tanaman '{$plantType}' yang terindikasi terkena penyakit '{$disease}' (Confidence: {$confidence}%). Jelaskan penyebab singkat dan cara mengatasinya dalam poin-poin yang mudah dipahami petani.";
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https:
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);
            if ($response->successful()) {
                $data = $response->json();
                $advice = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($advice) {
                    return $advice;
                }
            } else {
                \Log::error('Gemini API Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            \Log::error('Gemini Exception: ' . $e->getMessage());
        }
        return "Saran pengobatan untuk {$disease} pada {$plantType}:\n1. Isolasi tanaman sakit.\n2. Konsultasikan dengan ahli pertanian setempat.\n3. Cek kondisi lingkungan tumbuh.";
    }
}
