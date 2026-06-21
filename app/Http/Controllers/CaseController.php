<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessCase;
use App\Models\BeneficialOwner;
use App\Models\CaseDocument;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\AuditService;
use App\Services\RiskService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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
                        ->orWhere('uscc', 'like', "%{$value}%")
                        ->orWhere('ein', 'like', "%{$value}%")
                        ->orWhere('cnpj', 'like', "%{$value}%");
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
            'regions' => ['北京', '上海', '深圳', '广州', '杭州', '成都', '武汉', '南京', 'KY', 'VG', 'US', 'BR'],
            'countries' => ['CN', 'US', 'BR', 'OTHER'],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'business.name' => ['required', 'string', 'max:120'],
            'business.country' => ['required', 'string', 'in:CN,US,BR,OTHER'],
            'business.uscc' => ['required_if:business.country,CN', 'nullable', 'string', 'max:64'],
            'business.ein' => ['required_if:business.country,US', 'nullable', 'string', 'max:16', 'regex:/^\d{2}-\d{7}$/'],
            'business.cnpj' => ['required_if:business.country,BR', 'nullable', 'string', 'max:32'],
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
            'products' => ['required', 'array', 'min:1'],
            'products.*.name' => ['required', 'string', 'max:120'],
            'products.*.hs_code' => ['required', 'string', 'regex:/^\d{4}\.\d{2}(\.\d{2})?$/'],
            'business_license' => $request->input('action') === 'submit'
                ? ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240']
                : ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'tax_registration' => $request->input('action') === 'submit'
                ? ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240']
                : ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'other_documents' => ['nullable', 'array'],
            'other_documents.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'action' => ['required', 'in:draft,submit'],
        ], [
            'business.ein.regex' => 'EIN 格式不正确，应为 XX-XXXXXXX 格式。',
            'business.uscc.required_if' => '中国企业必须填写统一社会信用代码。',
            'business.ein.required_if' => '美国企业必须填写 EIN。',
            'business.cnpj.required_if' => '巴西经销商必须填写 CNPJ。',
            'products.required' => '请至少添加一个贸易产品。',
            'products.min' => '请至少添加一个贸易产品。',
            'products.*.name.required' => '产品名称必填。',
            'products.*.hs_code.required' => 'HS Code 必填。',
            'products.*.hs_code.regex' => 'HS Code 格式不正确，应为 1234.56（美国6位）或 1234.56.78（巴西NCM 8位）。',
            'business_license.required' => '提交核验时必须上传营业执照。',
            'business_license.mimes' => '营业执照仅支持 JPG、PNG、PDF 格式。',
            'business_license.max' => '营业执照文件大小不能超过 10MB。',
            'tax_registration.required' => '提交核验时必须上传税务登记证。',
            'tax_registration.mimes' => '税务登记证仅支持 JPG、PNG、PDF 格式。',
            'tax_registration.max' => '税务登记证文件大小不能超过 10MB。',
            'other_documents.*.mimes' => '附件仅支持 JPG、PNG、PDF 格式。',
            'other_documents.*.max' => '附件文件大小不能超过 10MB。',
        ]);

        if (!empty($data['business']['cnpj'])) {
            $cnpjDigits = preg_replace('/\D/', '', $data['business']['cnpj']);
            if (!$this->validateCnpj($cnpjDigits)) {
                return back()->withErrors(['business.cnpj' => 'CNPJ 校验位不正确，请检查输入。'])->withInput();
            }
        }

        $business = Business::create($data['business']);

        $case = BusinessCase::create([
            'case_no' => 'KYB-'.now()->year.'-'.str_pad((string) (BusinessCase::count() + 1), 4, '0', STR_PAD_LEFT),
            'business_id' => $business->id,
            'status' => 'draft',
            'created_by' => $request->user()->id,
            'assigned_to' => $request->user()->id,
            'summary' => ($data['business']['scope'] ?? null) ? Str::limit($data['business']['scope'], 80) : '企业身份核验申请。',
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

        foreach ($data['products'] as $product) {
            Product::create([
                'case_id' => $case->id,
                'name' => $product['name'],
                'hs_code' => $product['hs_code'],
            ]);
        }

        if ($request->hasFile('business_license')) {
            $this->storeDocument($case->id, $request->file('business_license'), CaseDocument::TYPE_BUSINESS_LICENSE);
        }

        if ($request->hasFile('tax_registration')) {
            $this->storeDocument($case->id, $request->file('tax_registration'), CaseDocument::TYPE_TAX_REGISTRATION);
        }

        if ($request->hasFile('other_documents')) {
            foreach ($request->file('other_documents') as $file) {
                $this->storeDocument($case->id, $file, CaseDocument::TYPE_OTHER);
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
            'beneficialOwners', 'products', 'documents', 'riskAssessment', 'reviews.user',
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
            'document_reviews' => ['nullable', 'array'],
            'document_reviews.*.id' => ['required', 'exists:case_documents,id'],
            'document_reviews.*.review_status' => ['required', 'in:approved,rejected,pending'],
            'document_reviews.*.review_comment' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $level = $user->isManager() ? 'final' : 'first';

        $hasLicense = $case->documents()->where('type', CaseDocument::TYPE_BUSINESS_LICENSE)->exists();
        $hasTaxCert = $case->documents()->where('type', CaseDocument::TYPE_TAX_REGISTRATION)->exists();

        if ($data['decision'] === 'approve') {
            if (!$hasLicense) {
                return back()->withErrors(['decision' => '审核通过前必须上传营业执照。'])->withInput();
            }
            if (!$hasTaxCert) {
                return back()->withErrors(['decision' => '审核通过前必须上传税务登记证。'])->withInput();
            }

            if ($level === 'final') {
                $licenseApproved = $case->documents()
                    ->where('type', CaseDocument::TYPE_BUSINESS_LICENSE)
                    ->where('review_status', 'approved')
                    ->exists();
                $taxApproved = $case->documents()
                    ->where('type', CaseDocument::TYPE_TAX_REGISTRATION)
                    ->where('review_status', 'approved')
                    ->exists();

                if (!$licenseApproved) {
                    return back()->withErrors(['decision' => '终审通过前营业执照必须审核通过。'])->withInput();
                }
                if (!$taxApproved) {
                    return back()->withErrors(['decision' => '终审通过前税务登记证必须审核通过。'])->withInput();
                }
            }
        }

        if (!empty($data['document_reviews'])) {
            foreach ($data['document_reviews'] as $docReview) {
                $doc = CaseDocument::find($docReview['id']);
                if ($doc && $doc->case_id === $case->id) {
                    $doc->update([
                        'review_status' => $docReview['review_status'],
                        'review_comment' => $docReview['review_comment'] ?? null,
                    ]);
                }
            }
        }

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
        $case->load(['business', 'beneficialOwners', 'products', 'riskAssessment', 'reviews.user', 'creator']);

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

    private function validateCnpj(string $cnpj): bool
    {
        if (strlen($cnpj) !== 14) {
            return false;
        }

        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        if (!$this->checkCnpjDigit(substr($cnpj, 0, 12), $cnpj[12])) {
            return false;
        }
        if (!$this->checkCnpjDigit(substr($cnpj, 0, 13), $cnpj[13])) {
            return false;
        }

        return true;
    }

    private function checkCnpjDigit(string $numbers, string $expected): bool
    {
        $length = strlen($numbers);
        $verifier = 0;

        for ($i = 1; $i <= $length; ++$i) {
            $multiplier = ($i >= 9) ? $i - 7 : $i + 1;
            $verifier += $numbers[$length - $i] * $multiplier;
        }

        $verifier = 11 - ($verifier % 11);
        if ($verifier >= 10) {
            $verifier = 0;
        }

        return (string) $verifier === $expected;
    }

    private function storeDocument(int $caseId, UploadedFile $file, string $type): CaseDocument
    {
        $path = $file->store('documents', 'private');

        return CaseDocument::create([
            'case_id' => $caseId,
            'type' => $type,
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'ocr_status' => 'pending',
            'review_status' => 'pending',
        ]);
    }
}
