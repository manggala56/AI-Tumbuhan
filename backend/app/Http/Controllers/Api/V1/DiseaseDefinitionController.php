<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DiseaseDefinition;
class DiseaseDefinitionController extends Controller
{
    public function index()
    {
        return response()->json(DiseaseDefinition::all(['technical_name', 'name', 'cause', 'cure']));
    }
}
