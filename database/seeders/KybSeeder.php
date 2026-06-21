<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\BeneficialOwner;
use App\Models\Business;
use App\Models\BusinessCase;
use App\Models\CaseDocument;
use App\Models\Review;
use App\Models\RiskAssessment;
use App\Models\User;
use App\Services\RiskService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class KybSeeder extends Seeder
{
    public function run(): void
    {
        $analyst = User::create([
            'name' => '陈思琪', 'email' => 'analyst@kyb.test',
            'password' => 'password', 'role' => 'analyst', 'title' => '合规专员',
            'email_verified_at' => now(),
        ]);
        $manager = User::create([
            'name' => '李文渊', 'email' => 'manager@kyb.test',
            'password' => 'password', 'role' => 'manager', 'title' => '合规经理',
            'email_verified_at' => now(),
        ]);
        User::create([
            'name' => '系统管理员', 'email' => 'admin@kyb.test',
            'password' => 'password', 'role' => 'admin', 'title' => '系统管理员',
            'email_verified_at' => now(),
        ]);

        $dataset = [
            [
                'business' => ['深圳云栖科技有限公司', '深圳', '软件和信息技术服务业', '91440300MA5F2Q8812', '林若曦', '人民币 5000 万元', '2018-06-12', '一般经营项目:软件开发、信息系统集成、数据处理与存储。', false],
                'ubos' => [['林若曦', '370102199003078823', 62.0, false], ['郑明远', '110108198505121234', 28.0, false]],
                'status' => 'approved', 'risk' => 'low', 'month' => 5,
            ],
            [
                'business' => ['北京中科智算股份有限公司', '北京', '研究和试验发展', '91110108MA01ABCD56', '宋怀瑾', '人民币 1 亿元', '2016-09-01', '人工智能算法研发、算力基础设施服务、行业解决方案。', false],
                'ubos' => [['宋怀瑾', '110108198207014567', 51.0, false], [' Horizon Capital', 'P12345678', 30.0, false]],
                'status' => 'approved', 'risk' => 'low', 'month' => 5,
            ],
            [
                'business' => ['上海瀚海供应链管理有限公司', '上海', '多式联运和运输代理', '91310115MA1K3P9099', '苏婉清', '人民币 3000 万元', '2019-03-20', '国际货物运输代理、仓储服务(除危险化学品)、供应链管理咨询。', false],
                'ubos' => [['苏婉清', '310104199112058811', 45.0, false], ['周慕白', '310115198811119922', 25.0, false]],
                'status' => 'pending_review', 'risk' => 'medium', 'month' => 6,
            ],
            [
                'business' => ['广州新橙文创科技有限公司', '广州', '广播、电视、电影和录音制作业', '91440101MA9Y2K3344', '韩星辰', '人民币 1000 万元', '2021-11-08', '数字内容创作、动漫游戏开发、文化创意设计。', false],
                'ubos' => [['韩星辰', '440106199507304411', 70.0, false]],
                'status' => 'reviewing', 'risk' => 'low', 'month' => 6,
            ],
            [
                'business' => ['离岸国际控股有限公司', 'KY', '投资与控股', 'KY-3210987-OFFSHORE', 'Marcus Lin', 'USD 5,000', '2014-02-14', '跨境投资控股、资产重组与离岸结构设计。', false],
                'ubos' => [['Marcus Lin', 'P88765432', 80.0, false]],
                'status' => 'reviewing', 'risk' => 'high', 'month' => 6,
            ],
            [
                'business' => ['盛远争议数字科技有限公司', '杭州', '虚拟资产/争议行业', '91330106MA2H8J7799', '钱景行', '人民币 800 万元', '2020-04-30', '虚拟资产交易撮合、数字藏品发行(存在争议业务)。', true],
                'ubos' => [['钱景行', '330106198904203345', 90.0, true]],
                'status' => 'reviewing', 'risk' => 'high', 'month' => 6,
            ],
            [
                'business' => ['某受限国际贸易有限公司', '深圳', '跨境贸易', '91440300MA9ZZ112233', '吴畏', '人民币 200 万元', '2017-07-19', '货物与技术进出口、跨境支付结算、国际物流。', true],
                'ubos' => [['吴畏', '440304198502091122', 55.0, true]],
                'status' => 'rejected', 'risk' => 'prohibited', 'month' => 4,
            ],
            [
                'business' => ['速达空壳商务咨询中心', '成都', '商务服务业', '91510104MA6X3D0045', '孙一鸣', '人民币 1 万元', '2022-10-11', '商务信息咨询(无实际经营场所与人员)。', false],
                'ubos' => [['孙一鸣', '510104199001011234', 100.0, false]],
                'status' => 'pending_review', 'risk' => 'high', 'month' => 6,
            ],
            [
                'business' => ['武汉光子跃动科技有限公司', '武汉', '软件和信息技术服务业', '91420100MA4K9Q5566', '冯予安', '人民币 8000 万元', '2015-12-28', '光电器件研发、半导体设备、精密仪器制造。', false],
                'ubos' => [['冯予安', '420102198710106677', 40.0, false], ['邓书宁', '420103198501028899', 35.0, false]],
                'status' => 'screening', 'risk' => null, 'month' => 6,
            ],
            [
                'business' => ['南京清川能源科技有限公司', '南京', '电力、热力生产和供应业', '91320100MA1WQ7T012', '蒋砚舟', '人民币 1.2 亿元', '2013-05-06', '光伏发电、储能系统、清洁能源工程总承包。', false],
                'ubos' => [['蒋砚舟', '320102198003128845', 58.0, false]],
                'status' => 'draft', 'risk' => null, 'month' => 6,
            ],
        ];

        $caseNo = 1;
        foreach ($dataset as $row) {
            [$name, $region, $industry, $uscc, $legalRep, $capital, $establish, $scope, $adverse] = $row['business'];

            $business = Business::create([
                'name' => $name, 'region' => $region, 'industry' => $industry,
                'uscc' => $uscc, 'legal_rep' => $legalRep, 'registered_capital' => $capital,
                'establish_date' => $establish, 'address' => $region.'市高新区科韵路'.rand(1, 999).'号',
                'scope' => $scope,
            ]);

            $createdAt = Carbon::now()->subMonths($row['month'])->endOfMonth();
            $case = BusinessCase::create([
                'case_no' => sprintf('KYB-2026-%04d', $caseNo++),
                'business_id' => $business->id,
                'status' => $row['status'],
                'risk_level' => $row['risk'],
                'risk_score' => 0,
                'assigned_to' => $row['status'] === 'reviewing' || $row['status'] === 'pending_review' ? $manager->id : $analyst->id,
                'created_by' => $analyst->id,
                'summary' => '企业身份核验与反洗钱合规审查。',
                'created_at' => $createdAt, 'updated_at' => $createdAt,
            ]);

            foreach ($row['ubos'] as $ubo) {
                BeneficialOwner::create([
                    'case_id' => $case->id, 'name' => $ubo[0], 'id_type' => str_starts_with($ubo[1], 'P') ? 'passport' : 'id_card',
                    'id_number' => $ubo[1], 'ownership_percent' => $ubo[2], 'is_pep' => $ubo[3],
                    'nationality' => str_starts_with($ubo[1], 'P') ? '境外' : '中国',
                    'verification_status' => 'verified',
                ]);
            }

            $docTypes = ['license', 'articles', 'id_card', 'bank_statement'];
            foreach (array_slice($docTypes, 0, rand(2, 4)) as $type) {
                CaseDocument::create([
                    'case_id' => $case->id, 'type' => $type,
                    'filename' => match ($type) {
                        'license' => '营业执照.pdf',
                        'articles' => '公司章程.pdf',
                        'id_card' => '法人身份证.jpg',
                        'bank_statement' => '银行开户证明.pdf',
                        default => '文件.pdf',
                    },
                    'path' => 'documents/sample.pdf', 'mime_type' => 'application/pdf',
                    'size' => rand(120000, 980000), 'ocr_status' => 'done',
                ]);
            }

            if ($row['status'] !== 'draft') {
                $riskService = app(RiskService::class);
                $assessment = $riskService->screen($case);
                $assessment->created_at = $createdAt; $assessment->updated_at = $createdAt; $assessment->save();
                $case->risk_score = $assessment->score; $case->risk_level = $assessment->level;
                $case->save();

                if (in_array($row['status'], ['reviewing', 'approved', 'rejected'], true)) {
                    Review::create([
                        'case_id' => $case->id, 'user_id' => $analyst->id, 'level' => 'first',
                        'decision' => 'approve', 'comment' => '主体信息与证照核验一致,受益人持股结构清晰,提交经理复核。',
                        'created_at' => $createdAt->copy()->addDay(), 'updated_at' => $createdAt->copy()->addDay(),
                    ]);
                }
                if (in_array($row['status'], ['approved', 'rejected'], true)) {
                    Review::create([
                        'case_id' => $case->id, 'user_id' => $manager->id, 'level' => 'final',
                        'decision' => $row['status'] === 'approved' ? 'approve' : 'reject',
                        'comment' => $row['status'] === 'approved'
                            ? '风险结论可接受,材料齐全,准予通过并归档。'
                            : '存在重大风险/合规缺陷,予以驳回,禁止准入。',
                        'created_at' => $createdAt->copy()->addDays(2), 'updated_at' => $createdAt->copy()->addDays(2),
                    ]);
                    $case->decided_at = $createdAt->copy()->addDays(2);
                    if ($row['status'] === 'approved' || $row['status'] === 'screening' || $row['status'] === 'draft') {
                        $case->submitted_at = $createdAt->copy()->addDay();
                    }
                    $case->save();
                }
                if ($row['status'] !== 'screening') {
                    $case->submitted_at = $createdAt->copy()->addDay();
                    $case->save();
                }
            }
        }

        $actions = [
            ['case.create', '创建核验案件', '案件已创建,进入草稿。'],
            ['case.submit', '提交核验', '案件提交,触发自动核验与风险筛查。'],
            ['case.screen', '风险筛查', '系统完成 PEP/制裁/负面新闻筛查。'],
            ['case.review', '审核操作', '完成一级审核并记录意见。'],
            ['case.approve', '终审通过', '合规经理终审通过,案件归档。'],
        ];
        $cases = BusinessCase::orderBy('id')->get();
        foreach ($cases as $idx => $case) {
            $count = $case->status === 'approved' ? 5 : ($case->status === 'rejected' ? 4 : ($case->status === 'draft' ? 1 : 3));
            for ($i = 0; $i < $count; $i++) {
                $a = $actions[$i];
                $time = $case->created_at->copy()->addHours($i * 5 + 1);
                AuditLog::create([
                    'user_id' => $i % 2 === 0 ? $analyst->id : $manager->id,
                    'action' => $a[0], 'entity_type' => 'case', 'entity_id' => $case->id,
                    'meta' => ['case_no' => $case->case_no, 'label' => $a[1], 'detail' => $a[2]],
                    'ip' => '10.0.'.($idx % 255).'.'.($i * 7 % 255),
                    'created_at' => $time, 'updated_at' => $time,
                ]);
            }
        }
    }
}
