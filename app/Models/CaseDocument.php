<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseDocument extends Model
{
    const TYPE_BUSINESS_LICENSE = 'business_license';
    const TYPE_TAX_REGISTRATION = 'tax_registration';
    const TYPE_ID_CARD = 'id_card';
    const TYPE_ARTICLES = 'articles';
    const TYPE_OTHER = 'other';

    const TYPE_LABELS = [
        self::TYPE_BUSINESS_LICENSE => '营业执照',
        self::TYPE_TAX_REGISTRATION => '税务登记证',
        self::TYPE_ID_CARD => '法人身份证件',
        self::TYPE_ARTICLES => '公司章程',
        self::TYPE_OTHER => '其他材料',
    ];

    const REQUIRED_TYPES = [
        self::TYPE_BUSINESS_LICENSE,
        self::TYPE_TAX_REGISTRATION,
    ];

    protected $fillable = [
        'case_id', 'type', 'filename', 'path', 'mime_type', 'size',
        'ocr_status', 'ocr_result', 'review_status', 'review_comment',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'ocr_result' => 'array',
        ];
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(BusinessCase::class, 'case_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }

    public function getIsRequiredAttribute(): bool
    {
        return in_array($this->type, self::REQUIRED_TYPES, true);
    }

    public function getReviewStatusLabelAttribute(): string
    {
        return match ($this->review_status) {
            'approved' => '审核通过',
            'rejected' => '审核驳回',
            default => '待审核',
        };
    }
}
