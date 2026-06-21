<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function makeCase(User $creator, array $attributes = []): BusinessCase
    {
        $business = Business::create([
            'name' => '测试公司 '.uniqid(),
            'country' => 'CN',
            'uscc' => substr(uniqid('91440300', true), 0, 32),
            'legal_rep' => '张三',
        ]);

        return BusinessCase::create(array_merge([
            'case_no' => 'KYB-T-'.uniqid(),
            'business_id' => $business->id,
            'status' => BusinessCase::STATUS_PENDING_REVIEW,
            'risk_level' => BusinessCase::RISK_LOW,
            'risk_score' => 10,
            'created_by' => $creator->id,
            'assigned_to' => $creator->id,
        ], $attributes));
    }

    public function test_manager_sees_global_overview_stats(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $analyst = User::factory()->create(['role' => 'analyst']);

        $this->makeCase($analyst, [
            'status' => BusinessCase::STATUS_PENDING_REVIEW,
            'risk_level' => BusinessCase::RISK_HIGH,
        ]);
        $this->makeCase($analyst, [
            'status' => BusinessCase::STATUS_APPROVED,
            'risk_level' => BusinessCase::RISK_LOW,
            'decided_at' => now(),
        ]);

        $response = $this->actingAs($manager)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('stats.total', 2)
            ->where('stats.pending', 1)
            ->where('stats.highRisk', 1)
            ->where('stats.approvedMonth', 1)
        );
    }

    public function test_analyst_only_sees_own_cases(): void
    {
        $me = User::factory()->create(['role' => 'analyst']);
        $other = User::factory()->create(['role' => 'analyst']);

        $this->makeCase($me, ['status' => BusinessCase::STATUS_PENDING_REVIEW]);
        $this->makeCase($other, ['status' => BusinessCase::STATUS_PENDING_REVIEW]);

        $response = $this->actingAs($me)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('stats.total', 1)
            ->where('stats.pending', 1)
            ->has('todos', 1)
        );
    }

    public function test_approved_month_ignores_same_month_in_other_years(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $analyst = User::factory()->create(['role' => 'analyst']);

        $this->makeCase($analyst, [
            'status' => BusinessCase::STATUS_APPROVED,
            'decided_at' => now()->subYear(),
        ]);
        $this->makeCase($analyst, [
            'status' => BusinessCase::STATUS_APPROVED,
            'decided_at' => now(),
        ]);

        $response = $this->actingAs($manager)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('stats.total', 2)
            ->where('stats.approvedMonth', 1)
        );
    }

    public function test_trend_returns_six_months_skeleton(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $response = $this->actingAs($manager)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (AssertableInertia $page) => $page->has('trend', 6));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }
}
