<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KYB 合规核验报告 - {{ $case->case_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'PingFang SC', 'Hiragino Sans GB', 'Microsoft YaHei', sans-serif;
            background: #f5f5f5;
            color: #1a1a1a;
            line-height: 1.6;
            padding: 40px;
        }
        .report-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 60px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e5e5e5;
        }
        .report-header {
            text-align: center;
            border-bottom: 2px solid #D4B062;
            padding-bottom: 30px;
            margin-bottom: 30px;
        }
        .report-title {
            font-size: 28px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 8px;
            letter-spacing: 2px;
        }
        .report-subtitle {
            font-size: 14px;
            color: #666;
            letter-spacing: 4px;
            text-transform: uppercase;
        }
        .report-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            font-size: 13px;
            color: #666;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e5e5;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-title::before {
            content: '';
            width: 4px;
            height: 18px;
            background: #D4B062;
            border-radius: 2px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 24px;
        }
        .info-item {
            display: flex;
            gap: 8px;
            font-size: 13px;
        }
        .info-label {
            color: #888;
            min-width: 100px;
            flex-shrink: 0;
        }
        .info-value {
            color: #1a1a1a;
            font-weight: 500;
            word-break: break-all;
        }
        .full-width {
            grid-column: 1 / -1;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-approved { background: #dcfce7; color: #166534; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-pending { background: #fef3c7; color: #92400e; }

        .ubo-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .ubo-item {
            padding: 12px;
            background: #fafafa;
            border: 1px solid #eee;
            border-radius: 6px;
        }
        .ubo-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .ubo-name {
            font-weight: 600;
            color: #1a1a1a;
        }
        .ubo-ownership {
            font-weight: 600;
            color: #D4B062;
        }
        .ubo-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 16px;
            font-size: 12px;
            color: #666;
        }

        .risk-section {
            display: flex;
            gap: 30px;
            align-items: flex-start;
        }
        .risk-score-display {
            text-align: center;
            flex-shrink: 0;
        }
        .risk-score-number {
            font-size: 48px;
            font-weight: 700;
            line-height: 1;
        }
        .risk-score-label {
            font-size: 12px;
            color: #888;
            margin-top: 4px;
        }
        .risk-level {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 10px;
        }
        .risk-low { background: #dcfce7; color: #166534; }
        .risk-medium { background: #fef3c7; color: #92400e; }
        .risk-high { background: #fee2e2; color: #991b1b; }
        .risk-prohibited { background: #fecaca; color: #7f1d1d; }

        .risk-factors {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .risk-factor {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            padding: 6px 0;
            border-bottom: 1px dashed #eee;
        }
        .risk-factor-name {
            color: #555;
        }
        .risk-factor-result {
            font-weight: 500;
        }
        .hit { color: #dc2626; }
        .no-hit { color: #16a34a; }

        .review-list {
            position: relative;
            padding-left: 24px;
        }
        .review-list::before {
            content: '';
            position: absolute;
            left: 6px;
            top: 6px;
            bottom: 6px;
            width: 2px;
            background: #e5e5e5;
        }
        .review-item {
            position: relative;
            margin-bottom: 20px;
        }
        .review-item::before {
            content: '';
            position: absolute;
            left: -22px;
            top: 6px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #D4B062;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #D4B062;
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }
        .review-user {
            font-weight: 600;
            font-size: 14px;
        }
        .review-level {
            font-size: 12px;
            color: #888;
        }
        .review-decision {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        .decision-approve { background: #dcfce7; color: #166534; }
        .decision-reject { background: #fee2e2; color: #991b1b; }
        .decision-return { background: #fef3c7; color: #92400e; }
        .review-comment {
            font-size: 13px;
            color: #555;
            line-height: 1.7;
        }
        .review-time {
            font-size: 12px;
            color: #999;
            margin-top: 4px;
        }

        .document-list {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .document-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: #fafafa;
            border: 1px solid #eee;
            border-radius: 6px;
            font-size: 13px;
        }
        .doc-icon {
            width: 32px;
            height: 32px;
            background: #fef3c7;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #92400e;
            font-size: 14px;
            font-weight: 600;
        }
        .doc-name {
            font-weight: 500;
            color: #1a1a1a;
        }

        .conclusion-box {
            padding: 20px;
            background: #fafafa;
            border: 1px solid #e5e5e5;
            border-left: 4px solid #D4B062;
            border-radius: 0 6px 6px 0;
        }
        .conclusion-title {
            font-weight: 600;
            font-size: 15px;
            margin-bottom: 10px;
            color: #1a1a1a;
        }
        .conclusion-text {
            font-size: 13px;
            color: #555;
            line-height: 1.8;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px dashed #ccc;
        }
        .signature-block {
            text-align: center;
            width: 200px;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            margin-bottom: 8px;
            height: 40px;
        }
        .signature-label {
            font-size: 13px;
            color: #666;
        }

        .report-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 12px;
            color: #999;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .report-container { box-shadow: none; border: none; }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="report-header">
            <h1 class="report-title">KYB 合规核验报告</h1>
            <p class="report-subtitle">Know Your Business Compliance Report</p>
        </div>

        <div class="report-meta">
            <span>报告编号：{{ $case->case_no }}</span>
            <span>生成日期：{{ now()->format('Y年m月d日') }}</span>
        </div>

        <div class="section">
            <div class="section-title">企业基本信息</div>
            <div class="info-grid">
                <div class="info-item full-width">
                    <span class="info-label">企业名称</span>
                    <span class="info-value">{{ $case->business->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">注册国家</span>
                    <span class="info-value">
                        @if($case->business->country === 'CN') 中国
                        @elseif($case->business->country === 'US') 美国
                        @elseif($case->business->country === 'BR') 巴西
                        @else 其他
                        @endif
                    </span>
                </div>
                @if($case->business->country === 'CN' && $case->business->uscc)
                <div class="info-item">
                    <span class="info-label">统一社会信用代码</span>
                    <span class="info-value">{{ $case->business->uscc }}</span>
                </div>
                @elseif($case->business->country === 'US' && $case->business->ein)
                <div class="info-item">
                    <span class="info-label">EIN (雇主识别号)</span>
                    <span class="info-value">{{ $case->business->ein }}</span>
                </div>
                @elseif($case->business->country === 'BR' && $case->business->cnpj)
                <div class="info-item">
                    <span class="info-label">CNPJ (巴西经销商税号)</span>
                    <span class="info-value">{{ $case->business->cnpj }}</span>
                </div>
                @elseif($case->business->uscc)
                <div class="info-item">
                    <span class="info-label">企业注册号</span>
                    <span class="info-value">{{ $case->business->uscc }}</span>
                </div>
                @endif
                <div class="info-item">
                    <span class="info-label">法定代表人</span>
                    <span class="info-value">{{ $case->business->legal_rep }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">注册资本</span>
                    <span class="info-value">{{ $case->business->registered_capital ?: '—' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">成立日期</span>
                    <span class="info-value">{{ $case->business->establish_date ? $case->business->establish_date->format('Y年m月d日') : '—' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">所属行业</span>
                    <span class="info-value">{{ $case->business->industry ?: '—' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">注册地区</span>
                    <span class="info-value">{{ $case->business->region ?: '—' }}</span>
                </div>
                <div class="info-item full-width">
                    <span class="info-label">注册地址</span>
                    <span class="info-value">{{ $case->business->address ?: '—' }}</span>
                </div>
                <div class="info-item full-width">
                    <span class="info-label">经营范围</span>
                    <span class="info-value">{{ $case->business->scope ?: '—' }}</span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">最终受益人(UBO)</div>
            <div class="ubo-list">
                @foreach($case->beneficialOwners as $ubo)
                <div class="ubo-item">
                    <div class="ubo-header">
                        <span class="ubo-name">{{ $ubo->name }}</span>
                        <span class="ubo-ownership">持股 {{ $ubo->ownership_percent }}%</span>
                    </div>
                    <div class="ubo-details">
                        <div><span style="color:#999;">证件类型：</span>{{ $ubo->id_type === 'passport' ? '护照' : '身份证' }}</div>
                        <div><span style="color:#999;">证件号码：</span>{{ $ubo->id_number }}</div>
                        <div><span style="color:#999;">国籍：</span>{{ $ubo->nationality ?: '—' }}</div>
                        <div><span style="color:#999;">PEP：</span>{{ $ubo->is_pep ? '是' : '否' }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="section">
            <div class="section-title">风险评估</div>
            <div class="risk-section">
                <div class="risk-score-display">
                    @if($case->riskAssessment)
                    <div class="risk-score-number" style="color:
                        @if($case->risk_score >= 70) #dc2626
                        @elseif($case->risk_score >= 45) #f87171
                        @elseif($case->risk_score >= 20) #fbbf24
                        @else #34d399
                        @endif
                    ">
                        {{ $case->risk_score }}
                    </div>
                    <div class="risk-score-label">风险评分</div>
                    <div class="risk-level risk-{{ $case->risk_level }}">
                        @if($case->risk_level === 'low') 低风险
                        @elseif($case->risk_level === 'medium') 中风险
                        @elseif($case->risk_level === 'high') 高风险
                        @else 禁止准入
                        @endif
                    </div>
                    @else
                    <div class="risk-score-number" style="color: #999;">—</div>
                    <div class="risk-score-label">未评估</div>
                    @endif
                </div>
                <div class="risk-factors">
                    @if($case->riskAssessment)
                        @foreach($case->riskAssessment->factors as $factor)
                        <div class="risk-factor">
                            <span class="risk-factor-name">{{ $factor['label'] }}</span>
                            <span class="risk-factor-result {{ $factor['hit'] ? 'hit' : 'no-hit' }}">
                                {{ $factor['hit'] ? '命中' : '未命中' }}
                                <span style="color:#999; font-weight:normal;">({{ $factor['weight'] }}分)</span>
                            </span>
                        </div>
                        @endforeach
                    @else
                        <p style="color:#999; font-size:13px;">暂无风险评估数据</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">证照文件</div>
            <div class="document-list">
                @foreach($case->documents as $doc)
                <div class="document-item">
                    <div class="doc-icon">
                        {{ strtoupper(pathinfo($doc->filename, PATHINFO_EXTENSION) ?: 'DOC') }}
                    </div>
                    <div>
                        <div class="doc-name">
                            @if($doc->type === 'license') 营业执照
                            @elseif($doc->type === 'articles') 公司章程
                            @elseif($doc->type === 'id_card') 法人身份证
                            @elseif($doc->type === 'bank_statement') 银行开户证明
                            @else 其他文件
                            @endif
                        </div>
                        <div style="font-size:11px; color:#999;">{{ $doc->filename }}</div>
                    </div>
                </div>
                @endforeach
                @if(!$case->documents->count())
                <p style="color:#999; font-size:13px; grid-column: 1/-1;">暂无证照文件</p>
                @endif
            </div>
        </div>

        <div class="section">
            <div class="section-title">审核记录</div>
            <div class="review-list">
                @foreach($case->reviews as $review)
                <div class="review-item">
                    <div class="review-header">
                        <div>
                            <span class="review-user">{{ $review->user->name }}</span>
                            <span class="review-level"> · {{ $review->level === 'final' ? '终审' : '初审' }}</span>
                        </div>
                        <span class="review-decision decision-{{ $review->decision }}">
                            @if($review->decision === 'approve') 通过
                            @elseif($review->decision === 'reject') 驳回
                            @else 退回补正
                            @endif
                        </span>
                    </div>
                    <p class="review-comment">{{ $review->comment }}</p>
                    <p class="review-time">{{ $review->created_at->format('Y年m月d日 H:i') }}</p>
                </div>
                @endforeach
                @if(!$case->reviews->count())
                <p style="color:#999; font-size:13px;">暂无审核记录</p>
                @endif
            </div>
        </div>

        <div class="section">
            <div class="section-title">核验结论</div>
            <div class="conclusion-box">
                <div class="conclusion-title">
                    结论：
                    <span class="status-badge {{ $case->status === 'approved' ? 'status-approved' : ($case->status === 'rejected' ? 'status-rejected' : 'status-pending') }}">
                        @if($case->status === 'approved') 已通过
                        @elseif($case->status === 'rejected') 已驳回
                        @elseif($case->status === 'draft') 草稿
                        @elseif($case->status === 'screening') 筛查中
                        @elseif($case->status === 'pending_review') 待初审
                        @elseif($case->status === 'reviewing') 复审中
                        @endif
                    </span>
                </div>
                <p class="conclusion-text">
                    @if($case->status === 'approved')
                        经核验，该企业主体身份真实有效，最终受益人结构清晰，风险等级可接受，符合 KYB 合规准入标准，准予通过并归档。
                    @elseif($case->status === 'rejected')
                        经核验，该企业存在重大合规风险或材料不完整，不符合 KYB 合规准入标准，予以驳回，禁止准入。
                    @else
                        该企业核验申请正在处理中，请等待审核完成。
                    @endif
                </p>
            </div>
        </div>

        <div class="signature-section">
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-label">初审人员</div>
            </div>
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-label">复核人员</div>
            </div>
            <div class="signature-block">
                <div class="signature-line"></div>
                <div class="signature-label">合规经理</div>
            </div>
        </div>

        <div class="report-footer">
            <p>本报告由 KYB 合规系统自动生成，仅供内部合规审查使用</p>
            <p style="margin-top:4px;">报告生成时间：{{ now()->format('Y-m-d H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
