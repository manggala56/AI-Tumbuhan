<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PlantType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'is_active',
    ];
    public function scanHistories()
    {
        return $this->hasMany(ScanHistory::class);
    }
}
