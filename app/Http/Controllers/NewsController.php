<?php

namespace App\Http\Controllers;

use App\Models\ApiConfig;
use App\Models\InvestmentNews;
use Illuminate\Contracts\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        return view('news.index', [
            'news' => InvestmentNews::query()->latest('published_at')->paginate(12),
            'apiConfig' => ApiConfig::query()->where('provider', 'alpha_vantage')->first(),
        ]);
    }
}
