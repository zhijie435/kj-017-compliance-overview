<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BusinessCase;
use App\Models\Review;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $total = BusinessCase::count();
        $pending = BusinessCase::whereIn('status', ['pending_review', 'reviewing', 'screening'])->count();
        $highRisk = BusinessCase::whereIn('risk_level', ['high', 'prohibited'])->count();
        $approvedMonth = BusinessCase::where('status', 'approved')
            ->whereMonth('decided_at', now()->month)->count();

        $trend = collect(range(5, 0))->map(function ($monthsBack) {
            $date = now()->subMonths($monthsBack);
            $base = BusinessCase::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);
            return [
                'month' => $date->format('n月'),
                'total' => (clone $base)->count(),
                'low' => (clone $base)->where('risk_level', 'low')->count(),
                'medium' => (clone $base)->where('risk_level', 'medium')->count(),
                'high' => (clone $base)->where('risk_level', 'high')->count(),
                'prohibited' => (clone $base)->where('risk_level', 'prohibited')->count(),
            ];
        })->values();

        $todos = BusinessCase::with(['business', 'assignee', 'creator'])
            ->whereIn('status', ['pending_review', 'reviewing', 'screening'])
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        $timeline = AuditLog::with('user')->latest()->limit(10)->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'total' => $total,
                'pending' => $pending,
                'highRisk' => $highRisk,
                'approvedMonth' => $approvedMonth,
            ],
            'trend' => $trend,
            'todos' => $todos,
            'timeline' => $timeline,
        ]);
    }
}
