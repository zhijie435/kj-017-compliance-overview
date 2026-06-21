<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BusinessCase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DashboardController extends Controller
{
    private const TREND_MONTHS = 6;

    private const TODO_LIMIT = 6;

    private const TIMELINE_LIMIT = 10;

    public function index(Request $request)
    {
        $user = $request->user();

        $stats = $this->safe(fn () => $this->buildStats($user), $this->emptyStats(), 'stats', $user);
        $trend = $this->safe(fn () => $this->buildTrend($user), [], 'trend', $user);
        $todos = $this->safe(fn () => $this->buildTodos($user), collect(), 'todos', $user);
        $timeline = $this->safe(fn () => $this->buildTimeline($user), collect(), 'timeline', $user);

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'trend' => $trend,
            'todos' => $todos,
            'timeline' => $timeline,
        ]);
    }

    private function buildStats(User $user): array
    {
        $active = $this->quotedIn(BusinessCase::ACTIVE_STATUSES);
        $highRisk = $this->quotedIn(BusinessCase::HIGH_RISK_LEVELS);

        $aggregate = BusinessCase::visibleTo($user)
            ->selectRaw(
                'count(*) as total, '.
                'sum(case when status in ('.$active.') then 1 else 0 end) as pending, '.
                'sum(case when risk_level in ('.$highRisk.') then 1 else 0 end) as high_risk'
            )
            ->first();

        $monthStart = now()->startOfMonth();
        $approvedMonth = BusinessCase::visibleTo($user)
            ->where('status', BusinessCase::STATUS_APPROVED)
            ->where('decided_at', '>=', $monthStart)
            ->where('decided_at', '<', $monthStart->copy()->addMonth())
            ->count();

        return [
            'total' => (int) ($aggregate?->total ?? 0),
            'pending' => (int) ($aggregate?->pending ?? 0),
            'highRisk' => (int) ($aggregate?->high_risk ?? 0),
            'approvedMonth' => $approvedMonth,
        ];
    }

    private function buildTrend(User $user): array
    {
        $startOfMonth = now()->startOfMonth();

        $trend = [];
        $keys = [];
        foreach (range(self::TREND_MONTHS - 1, 0) as $monthsBack) {
            $date = $startOfMonth->copy()->subMonths($monthsBack);
            $key = $date->format('Y-m');
            $keys[] = $key;
            $trend[$key] = [
                'month' => $date->format('n月'),
                'total' => 0,
                'low' => 0,
                'medium' => 0,
                'high' => 0,
                'prohibited' => 0,
            ];
        }

        $cases = BusinessCase::visibleTo($user)
            ->where('created_at', '>=', $startOfMonth->copy()->subMonths(self::TREND_MONTHS - 1))
            ->select(['risk_level', 'created_at'])
            ->get();

        foreach ($cases as $case) {
            $key = $case->created_at?->format('Y-m');

            if (! isset($trend[$key])) {
                continue;
            }

            $trend[$key]['total']++;

            $level = $case->risk_level;
            if ($level !== null && array_key_exists($level, $trend[$key])) {
                $trend[$key][$level]++;
            }
        }

        return array_map(fn ($key) => $trend[$key], $keys);
    }

    private function buildTodos(User $user)
    {
        return BusinessCase::with(['business', 'assignee', 'creator'])
            ->active()
            ->visibleTo($user)
            ->orderByDesc('updated_at')
            ->limit(self::TODO_LIMIT)
            ->get();
    }

    private function buildTimeline(User $user)
    {
        $query = AuditLog::with('user')->latest();

        if (! $user->isAdmin() && ! $user->isManager()) {
            $visibleCaseIds = BusinessCase::visibleTo($user)->pluck('id');

            $query->where(function ($q) use ($user, $visibleCaseIds) {
                $q->where('user_id', $user->id)
                    ->orWhere(function ($sub) use ($visibleCaseIds) {
                        $sub->where('entity_type', 'case')
                            ->whereIn('entity_id', $visibleCaseIds);
                    });
            });
        }

        return $query->limit(self::TIMELINE_LIMIT)->get();
    }

    private function safe(callable $callback, mixed $fallback, string $label, User $user): mixed
    {
        try {
            return $callback();
        } catch (\Throwable $e) {
            Log::error('合规总览数据加载失败', [
                'section' => $label,
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);

            return $fallback;
        }
    }

    private function emptyStats(): array
    {
        return [
            'total' => 0,
            'pending' => 0,
            'highRisk' => 0,
            'approvedMonth' => 0,
        ];
    }

    private function quotedIn(array $values): string
    {
        return implode(',', array_map(
            fn ($value) => "'".str_replace("'", "''", (string) $value)."'",
            $values
        ));
    }
}
