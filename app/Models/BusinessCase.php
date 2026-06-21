<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BusinessCase extends Model
{
    protected $table = 'cases';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCREENING = 'screening';

    public const STATUS_PENDING_REVIEW = 'pending_review';

    public const STATUS_REVIEWING = 'reviewing';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const RISK_LOW = 'low';

    public const RISK_MEDIUM = 'medium';

    public const RISK_HIGH = 'high';

    public const RISK_PROHIBITED = 'prohibited';

    public const ACTIVE_STATUSES = [
        self::STATUS_SCREENING,
        self::STATUS_PENDING_REVIEW,
        self::STATUS_REVIEWING,
    ];

    public const TERMINAL_STATUSES = [
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
    ];

    public const RISK_LEVELS = [
        self::RISK_LOW,
        self::RISK_MEDIUM,
        self::RISK_HIGH,
        self::RISK_PROHIBITED,
    ];

    public const HIGH_RISK_LEVELS = [
        self::RISK_HIGH,
        self::RISK_PROHIBITED,
    ];

    protected $fillable = [
        'case_no', 'business_id', 'status', 'risk_level', 'risk_score',
        'assigned_to', 'created_by', 'summary', 'submitted_at', 'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'risk_score' => 'integer',
            'submitted_at' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function beneficialOwners(): HasMany
    {
        return $this->hasMany(BeneficialOwner::class, 'case_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'case_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CaseDocument::class, 'case_id');
    }

    public function riskAssessment(): HasOne
    {
        return $this->hasOne(RiskAssessment::class, 'case_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'case_id');
    }

    public function scopeActive($query): void
    {
        $query->whereIn('status', self::ACTIVE_STATUSES);
    }

    public function scopeTerminal($query): void
    {
        $query->whereIn('status', self::TERMINAL_STATUSES);
    }

    public function scopeHighRisk($query): void
    {
        $query->whereIn('risk_level', self::HIGH_RISK_LEVELS);
    }

    public function scopeVisibleTo($query, User $user): void
    {
        if ($user->isAdmin() || $user->isManager()) {
            return;
        }

        $query->where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
                ->orWhere('assigned_to', $user->id);
        });
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => '草稿',
            self::STATUS_SCREENING => '筛查中',
            self::STATUS_PENDING_REVIEW => '待初审',
            self::STATUS_REVIEWING => '审核中',
            self::STATUS_APPROVED => '已通过',
            self::STATUS_REJECTED => '已驳回',
            default => $this->status,
        };
    }
}
