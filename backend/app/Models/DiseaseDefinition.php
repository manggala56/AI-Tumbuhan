<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DiseaseDefinition extends Model
{
    protected $fillable = [
        'technical_name',
        'name',
        'cause',
        'cure',
    ];
}
