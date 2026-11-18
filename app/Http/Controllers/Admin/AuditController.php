<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AuditController extends Controller
{
    /**
     * Display a listing of system audit activity logs.
     */
    public function index(Request $request): View
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $query = Activity::query()
            ->with(['causer', 'subject'])
            ->latest();

        $search = trim((string) $request->input('search', ''));

        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('description', 'like', "%{$search}%")
                    ->orWhere('log_name', 'like', "%{$search}%")
                    ->orWhere('event', 'like', "%{$search}%")
                    ->orWhere('subject_type', 'like', "%{$search}%")
                    ->orWhere('properties', 'like', "%{$search}%")
                    ->orWhere('attribute_changes', 'like', "%{$search}%");
            });
        }

        if ($request->filled('log_name')) {
            $query->where('log_name', (string) $request->input('log_name'));
        }

        if ($request->filled('event')) {
            $query->where('event', (string) $request->input('event'));
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_type', User::class)
                ->where('causer_id', $request->integer('causer_id'));
        }

        if ($from = $request->date('from')) {
            $query->whereDate('created_at', '>=', $from->toDateString());
        }

        if ($to = $request->date('to')) {
            $query->whereDate('created_at', '<=', $to->toDateString());
        }

        $summary = [
            'total' => (clone $query)->count(),
            'today' => (clone $query)->whereDate('created_at', today())->count(),
            'users' => (clone $query)
                ->where('causer_type', User::class)
                ->whereNotNull('causer_id')
                ->distinct()
                ->count('causer_id'),
            'modules' => (clone $query)
                ->whereNotNull('log_name')
                ->distinct()
                ->count('log_name'),
        ];

        $activities = $query->paginate(20)->withQueryString();
        $logNames = Activity::query()
            ->select('log_name')
            ->distinct()
            ->whereNotNull('log_name')
            ->orderBy('log_name')
            ->pluck('log_name');
        $events = Activity::query()
            ->select('event')
            ->distinct()
            ->whereNotNull('event')
            ->orderBy('event')
            ->pluck('event');
        $causers = User::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.audit.index', compact('activities', 'logNames', 'events', 'causers', 'summary'));
    }
}
