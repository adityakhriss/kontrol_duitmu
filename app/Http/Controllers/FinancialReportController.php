<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFinancialReportRequest;
use App\Models\FinancialReport;
use App\Services\Finance\FinancialReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;

class FinancialReportController extends Controller
{
    public function __construct(private readonly FinancialReportService $financialReportService)
    {
    }

    public function index(): View
    {
        $user = request()->user();

        return view('reports.index', [
            'eligibility' => $this->financialReportService->eligibility($user),
            'reports' => $user->financialReports()->with('aiFinancialInsight')->latest('generated_at')->get(),
            'latestReport' => $user->financialReports()->with('aiFinancialInsight')->latest('generated_at')->first(),
        ]);
    }

    public function store(StoreFinancialReportRequest $request): RedirectResponse
    {
        $eligibility = $this->financialReportService->eligibility($request->user());

        if (! $eligibility['eligible']) {
            return back()->withErrors([
                'report' => $eligibility['reason'],
            ]);
        }

        $report = $this->financialReportService->create($request->user(), $request->validated());

        return redirect()->route('reports.index')->with('status', 'Laporan keuangan berhasil dibuat: '.$report->title);
    }

    public function show(FinancialReport $report): View
    {
        abort_unless((int) $report->user_id === (int) request()->user()->id, 403);

        return view('reports.show', [
            'report' => $report->load('aiFinancialInsight'),
        ]);
    }

    public function downloadPdf(FinancialReport $report): Response
    {
        abort_unless((int) $report->user_id === (int) request()->user()->id, 403);

        $report->load('aiFinancialInsight');

        $pdf = Pdf::loadView('reports.pdf', [
            'report' => $report,
        ])->setPaper('a4');

        return $pdf->download(str($report->title)->slug('-').'.pdf');
    }
}
