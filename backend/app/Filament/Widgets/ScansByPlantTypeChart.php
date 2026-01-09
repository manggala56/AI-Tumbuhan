<?php
namespace App\Filament\Widgets;
use App\Models\ScanHistory;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
class ScansByPlantTypeChart extends ChartWidget
{
    protected static ?string $heading = 'Scans by Plant Type';
    protected static ?int $sort = 3;
    protected function getData(): array
    {
        $data = ScanHistory::query()
            ->select('plant_types.name', DB::raw('count(*) as total'))
            ->join('plant_types', 'scan_histories.plant_type_id', '=', 'plant_types.id')
            ->groupBy('plant_types.name')
            ->pluck('total', 'name');
        return [
            'datasets' => [
                [
                    'label' => 'Scans',
                    'data' => $data->values()->toArray(),
                    'backgroundColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                    ],
                ],
            ],
            'labels' => $data->keys()->toArray(),
        ];
    }
    protected function getType(): string
    {
        return 'doughnut';
    }
}
