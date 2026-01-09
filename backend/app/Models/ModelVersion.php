<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ModelVersion extends Model
{
    protected $fillable = [
        'plant_type_id',
        'version_name',
        'file_path',
        'accuracy',
        'precision_score',
        'recall_score',
        'f1_score',
        'trained_at',
        'training_samples',
        'epochs',
        'learning_rate',
        'is_active',
        'is_shadow',
        'deployed_at',
        'deployed_by',
        'notes',
    ];
    public function plantType()
    {
        return $this->belongsTo(PlantType::class);
    }
    public function deployedBy()
    {
        return $this->belongsTo(User::class, 'deployed_by');
    }
}
