<?php
namespace App\Filament\Widgets;
use App\Models\ScanHistory;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalScans = ScanHistory::count();
        $scansToday = ScanHistory::whereDate('created_at', today())->count();
        $scansThisWeek = ScanHistory::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $totalUsers = User::count();
        $avgConfidence = ScanHistory::whereNotNull('ai_confidence')
            ->avg('ai_confidence');
        $pendingCorrection = ScanHistory::where('ai_confidence', '<', 80)
            ->whereNull('researcher_correction')
            ->count();
        return [
            Stat::make('Total Scans', $totalScans)
                ->description($scansToday . ' today, ' . $scansThisWeek . ' this week')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 12, 15, 18, 22, 25, $scansToday]),
            Stat::make('Total Users', $totalUsers)
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
            Stat::make('Avg Confidence', number_format($avgConfidence ?? 0, 1) . '%')
                ->description('AI prediction confidence')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color($avgConfidence >= 85 ? 'success' : ($avgConfidence >= 70 ? 'warning' : 'danger')),
            Stat::make('Pending Review', $pendingCorrection)
                ->description('Low confidence scans')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),
        ];
    }
}
