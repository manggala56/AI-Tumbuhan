<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\PlantType;
use App\Models\User;
class ScanHistory extends Model
{
    protected $fillable = [
        'user_id',
        'plant_type_id',
        'image_path',
        'ai_result',
        'ai_confidence',
        'ai_model_version',
        'shadow_result',
        'shadow_confidence',
        'shadow_model_version',
        'treatment_advice',
        'user_rating',
        'user_comment',
        'researcher_correction',
        'corrected_by',
        'corrected_at',
        'is_training_ready',
        'approved_for_training_at',
        'approved_by',
    ];
    public function plantType()
    {
        return $this->belongsTo(PlantType::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function correctedBy()
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
