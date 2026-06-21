<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'action' => ['nullable', 'string'],
        ]);

        $logs = AuditLog::with('user')
            ->when($filters['action'] ?? null, fn ($q, $v) => $q->where('action', $v))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Audit/Index', [
            'logs' => $logs,
            'filters' => $filters,
        ]);
    }
}
