<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TrainingJob extends Model
{
    protected $fillable = [
        'plant_type_id',
        'job_id',
        'dataset_path',
        'dataset_url',
        'status',
        'learning_rate',
        'epochs',
        'batch_size',
        'final_accuracy',
        'training_time_seconds',
        'error_message',
        'started_at',
        'completed_at',
        'triggered_by',
    ];
    public function plantType()
    {
        return $this->belongsTo(PlantType::class);
    }
    public function triggeredBy()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
