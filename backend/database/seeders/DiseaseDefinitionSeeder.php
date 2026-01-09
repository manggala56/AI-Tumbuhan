<?php
namespace Database\Seeders;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DiseaseDefinition;
class DiseaseDefinitionSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = base_path('../AI/app/models/plant_disease.json');
        if (!file_exists($jsonPath)) {
            $this->command->error("File not found: $jsonPath");
            $jsonPath = base_path('../../AI/app/models/plant_disease.json');
             if (!file_exists($jsonPath)) {
                $this->command->error("File not found in fallback path either: $jsonPath");
                return;
             }
        }
        $json = file_get_contents($jsonPath);
        $diseases = json_decode($json, true);
        if (!$diseases) {
             $this->command->error("Invalid JSON in: $jsonPath");
             return;
        }
        foreach ($diseases as $disease) {
            DiseaseDefinition::updateOrCreate(
                ['technical_name' => $disease['technical_name']],
                [
                    'name' => $disease['name'],
                    'cause' => $disease['cause'],
                    'cure' => $disease['cure'],
                ]
            );
        }
        $this->command->info('Disease definitions seeded from JSON file.');
    }
}
