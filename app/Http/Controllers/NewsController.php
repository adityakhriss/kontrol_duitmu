<?php

namespace App\Http\Controllers;

use App\Models\ApiConfig;
use App\Models\InvestmentNews;
use Illuminate\Contracts\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        $newsConfig = ApiConfig::query()->where('provider', 'rss_news')->first();

        return view('news.index', [
            'news' => InvestmentNews::query()->latest('published_at')->paginate(10),
            'newsConfig' => $newsConfig,
        ]);
    }
}
