<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlantType;
class PlantTypeController extends Controller
{
    public function index()
    {
        $plantTypes = PlantType::where('is_active', true)
            ->select(['id', 'name', 'slug', 'icon', 'description'])
            ->get();
        return response()->json([
            'data' => $plantTypes
        ]);
    }
}
