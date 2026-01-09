<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\PlantType;
class PlantTypeSeeder extends Seeder
{
    public function run()
    {
        $plants = [
            ['name' => 'Tomato', 'slug' => 'tomato', 'description' => 'Tomato plants and related varieties'],
            ['name' => 'Chilli', 'slug' => 'chilli', 'description' => 'Chilli pepper plants'],
            ['name' => 'Corn', 'slug' => 'corn', 'description' => 'Corn/Maize plants'],
            ['name' => 'Potato', 'slug' => 'potato', 'description' => 'Potato plants'],
        ];
        foreach ($plants as $plant) {
            PlantType::create($plant);
        }
    }
}
