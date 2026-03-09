<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiConfig;
use App\Models\ApiSyncLog;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'totalUsers' => User::query()->count(),
            'activeUsers' => User::query()->where('is_active', true)->count(),
            'apiConfig' => ApiConfig::query()->where('provider', 'rss_news')->first(),
            'latestLogs' => ApiSyncLog::query()->latest()->take(5)->get(),
        ]);
    }
}
