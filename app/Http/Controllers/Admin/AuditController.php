<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $query = Activity::with('causer')->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('log_name', 'like', "%{$search}%");
            });
        }

        if ($logName = $request->input('log_name')) {
            $query->where('log_name', $logName);
        }

        $activities = $query->paginate(20)->withQueryString();
        $logNames = Activity::select('log_name')->distinct()->whereNotNull('log_name')->pluck('log_name');

        return view('admin.audit.index', compact('activities', 'logNames'));
    }
}
