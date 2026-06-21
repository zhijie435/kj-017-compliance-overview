<?php

namespace App\Http\Controllers;

use App\Models\BusinessCase;
use Inertia\Inertia;

class RiskController extends Controller
{
    public function index()
    {
        $cases = BusinessCase::with(['business', 'riskAssessment', 'assignee'])
            ->whereNotNull('risk_level')
            ->orderByDesc('risk_score')
            ->paginate(15);

        $distribution = collect(['low', 'medium', 'high', 'prohibited'])->map(function ($level) {
            return [
                'level' => $level,
                'count' => BusinessCase::where('risk_level', $level)->count(),
            ];
        });

        $flags = [
            'pep' => BusinessCase::whereHas('riskAssessment', fn ($q) => $q->where('pep_hit', true))->count(),
            'sanctions' => BusinessCase::whereHas('riskAssessment', fn ($q) => $q->where('sanctions_hit', true))->count(),
            'adverse' => BusinessCase::whereHas('riskAssessment', fn ($q) => $q->where('adverse_media', true))->count(),
            'shell' => BusinessCase::whereHas('riskAssessment', fn ($q) => $q->where('shell_company', true))->count(),
        ];

        return Inertia::render('Risk/Index', [
            'cases' => $cases,
            'distribution' => $distribution,
            'flags' => $flags,
        ]);
    }
}
