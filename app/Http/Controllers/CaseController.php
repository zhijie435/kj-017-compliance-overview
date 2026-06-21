<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessCase;
use App\Models\BeneficialOwner;
use App\Models\CaseDocument;
use App\Models\Review;
use App\Models\User;
use App\Services\AuditService;
use App\Services\RiskService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CaseController extends Controller
{
    public function __construct(private AuditService $audit, private RiskService $risk) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'risk_level' => ['nullable', 'string'],
        ]);

        $cases = BusinessCase::with(['business', 'assignee', 'creator'])
            ->when($filters['q'] ?? null, function ($q, $value) {
                $q->whereHas('business', function ($b) use ($value) {
                    $b->where('name', 'like', "%{$value}%")
                        ->orWhere('uscc', 'like', "%{$value}%");
                })->orWhere('case_no', 'like', "%{$value}%");
            })
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['risk_level'] ?? null, fn ($q, $v) => $q->where('risk_level', $v))
            ->orderByDesc('updated_at')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Cases/Index', [
            'cases' => $cases,
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        return Inertia::render('Cases/Create', [
            'industries' => [
                '软件和信息技术服务业', '金融业', '电子商务', '跨境贸易', '制造业',
                '研究和试验发展', '虚拟资产', '投资与控股', '供应链管理', '文化创意',
            ],
            'regions' => ['北京', '上海', '深圳', '广州', '杭州', '成都', '武汉', '南京', 'KY', 'VG'],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'business.name' => ['required', 'string', 'max:120'],
            'business.uscc' => ['required', 'string', 'max:64'],
            'business.legal_rep' => ['required', 'string', 'max:60'],
            'business.registered_capital' => ['nullable', 'string', 'max:60'],
            'business.establish_date' => ['nullable', 'date'],
            'business.address' => ['nullable', 'string', 'max:200'],
            'business.scope' => ['nullable', 'string', 'max:1000'],
            'business.industry' => ['nullable', 'string', 'max:60'],
            'business.region' => ['nullable', 'string', 'max:60'],
            'ubos' => ['required', 'array', 'min:1'],
            'ubos.*.name' => ['required', 'string', 'max:60'],
            'ubos.*.id_type' => ['required', 'string'],
            'ubos.*.id_number' => ['required', 'string', 'max:40'],
            'ubos.*.ownership_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'ubos.*.is_pep' => ['boolean'],
            'action' => ['required', 'in:draft,submit'],
        ]);

        $business = Business::create($data['business']);

        $case = BusinessCase::create([
            'case_no' => 'KYB-'.now()->year.'-'.str_pad((string) (BusinessCase::count() + 1), 4, '0', STR_PAD_LEFT),
            'business_id' => $business->id,
            'status' => 'draft',
            'created_by' => $request->user()->id,
            'assigned_to' => $request->user()->id,
            'summary' => $data['business']['scope'] ? Str::limit($data['business']['scope'], 80) : '企业身份核验申请。',
        ]);

        foreach ($data['ubos'] as $ubo) {
            BeneficialOwner::create([
                'case_id' => $case->id,
                'name' => $ubo['name'],
                'id_type' => $ubo['id_type'],
                'id_number' => $ubo['id_number'],
                'ownership_percent' => $ubo['ownership_percent'],
                'is_pep' => $ubo['is_pep'] ?? false,
                'nationality' => $ubo['id_type'] === 'passport' ? '境外' : '中国',
                'verification_status' => 'pending',
            ]);
        }

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = $file->store('documents', 'private');
                CaseDocument::create([
                    'case_id' => $case->id,
                    'type' => 'other',
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'ocr_status' => 'pending',
                ]);
            }
        }

        $this->audit->record('case.create', 'case', $case->id, ['case_no' => $case->case_no, 'label' => '创建核验案件']);

        if ($data['action'] === 'submit') {
            $this->screenAndAdvance($case);
            $this->audit->record('case.submit', 'case', $case->id, ['case_no' => $case->case_no, 'label' => '提交核验']);
        }

        return redirect()->route('cases.show', $case->id)->with('success', '案件已创建。');
    }

    public function show(BusinessCase $case)
    {
        $case->load([
            'business', 'creator', 'assignee',
            'beneficialOwners', 'documents', 'riskAssessment', 'reviews.user',
        ]);

        return Inertia::render('Cases/Show', [
            'case' => $case,
        ]);
    }

    public function screen(BusinessCase $case, Request $request)
    {
        $case->status = 'screening';
        $case->save();
        $this->screenAndAdvance($case);
        $this->audit->record('case.screen', 'case', $case->id, ['case_no' => $case->case_no, 'label' => '触发风险筛查']);

        return redirect()->route('cases.show', $case->id)->with('success', '风险筛查已完成。');
    }

    public function review(BusinessCase $case, Request $request)
    {
        $data = $request->validate([
            'decision' => ['required', 'in:approve,reject,return'],
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $level = $user->isManager() ? 'final' : 'first';

        Review::create([
            'case_id' => $case->id,
            'user_id' => $user->id,
            'level' => $level,
            'decision' => $data['decision'],
            'comment' => $data['comment'],
        ]);

        $case->status = match ($data['decision']) {
            'approve' => $level === 'final' ? 'approved' : 'reviewing',
            'reject' => 'rejected',
            'return' => 'draft',
            default => $case->status,
        };

        if (in_array($case->status, ['approved', 'rejected'], true)) {
            $case->decided_at = now();
        }

        if ($data['decision'] === 'approve' && $level === 'first') {
            $case->assigned_to = User::where('role', 'manager')->value('id') ?? $case->assigned_to;
        }
        $case->save();

        $this->audit->record('case.review', 'case', $case->id, [
            'case_no' => $case->case_no, 'label' => '审核操作',
            'decision' => $data['decision'], 'level' => $level,
        ]);

        return redirect()->route('cases.show', $case->id)->with('success', '审核已记录。');
    }

    public function report(BusinessCase $case)
    {
        $case->load(['business', 'beneficialOwners', 'riskAssessment', 'reviews.user', 'creator']);

        $this->audit->record('case.report', 'case', $case->id, ['case_no' => $case->case_no, 'label' => '生成合规报告']);

        $html = view('reports.compliance', ['case' => $case])->render();

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function screenAndAdvance(BusinessCase $case): void
    {
        $assessment = $this->risk->screen($case);
        $case->risk_score = $assessment->score;
        $case->risk_level = $assessment->level;

        if ($assessment->level === 'prohibited') {
            $case->status = 'rejected';
            $case->decided_at = now();
        } else {
            $case->status = 'pending_review';
        }

        $case->submitted_at = $case->submitted_at ?? now();
        $case->save();
    }
}
