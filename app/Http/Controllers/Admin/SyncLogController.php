<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiSyncLog;
use Illuminate\Contracts\View\View;

class SyncLogController extends Controller
{
    public function index(): View
    {
        return view('admin.sync-logs', [
            'logs' => ApiSyncLog::query()->latest()->paginate(20),
        ]);
    }
}
