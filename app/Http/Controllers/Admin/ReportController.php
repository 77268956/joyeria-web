<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reports) {}

    /**
     * Show the report catalogue.
     */
    public function index(): View
    {
        return view('admin.reports.index', [
            'reports' => $this->reports->catalogue(),
        ]);
    }

    /**
     * Show one report with its filters.
     */
    public function show(Request $request, string $report): View
    {
        $data = $this->reports->build($report, $request->input('from'), $request->input('to'));

        abort_if($data === null, 404);

        return view('admin.reports.show', [
            'report' => $data,
            'catalogue' => $this->reports->catalogue(),
        ]);
    }

    /**
     * Export a report as CSV (Excel-compatible) or PDF.
     */
    public function export(Request $request, string $report, string $format): StreamedResponse|Response
    {
        $data = $this->reports->build($report, $request->input('from'), $request->input('to'));

        abort_if($data === null, 404);

        return match ($format) {
            'csv' => $this->exportCsv($data),
            'pdf' => $this->exportPdf($data, $request),
            default => abort(404),
        };
    }

    /**
     * Stream the report as a UTF-8 CSV file.
     *
     * @param  array<string, mixed>  $data
     */
    protected function exportCsv(array $data): StreamedResponse
    {
        $filename = 'reporte_'.$data['key'].'_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($data): void {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [$data['title']]);

            if ($data['dated']) {
                fputcsv($handle, ['Desde', $data['from'], 'Hasta', $data['to']]);
            }

            fputcsv($handle, []);
            fputcsv($handle, $data['columns']);

            foreach ($data['rows'] as $row) {
                fputcsv($handle, $row);
            }

            fputcsv($handle, []);

            foreach ($data['totals'] as $total) {
                fputcsv($handle, [$total['label'], $total['value']]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Render the report as a downloadable PDF.
     *
     * @param  array<string, mixed>  $data
     */
    protected function exportPdf(array $data, Request $request): Response
    {
        return Pdf::loadView('admin.reports.pdf', [
            'report' => $data,
            'storeName' => $request->user()?->store?->name ?? config('app.name'),
            'generatedAt' => now(),
        ])->setPaper('letter', 'landscape')->download('reporte_'.$data['key'].'_'.now()->format('Ymd_His').'.pdf');
    }
}
