<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseProductsHsCodeTest extends TestCase
{
    use RefreshDatabase;

    private function basePayload(array $products): array
    {
        return [
            'business' => [
                'name' => '测试科技有限公司',
                'country' => 'CN',
                'uscc' => '91440300MA5F2Q8812',
                'legal_rep' => '林若曦',
            ],
            'ubos' => [
                [
                    'name' => '林若曦',
                    'id_type' => 'id_card',
                    'id_number' => '370102199003078823',
                    'ownership_percent' => 100,
                    'is_pep' => false,
                ],
            ],
            'products' => $products,
            'action' => 'draft',
        ];
    }

    public function test_invalid_hs_code_format_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'analyst']);

        $response = $this->actingAs($user)->post('/cases', $this->basePayload([
            ['name' => '高精度传感器', 'hs_code' => 'invalid-code'],
        ]));

        $response->assertSessionHasErrors('products.0.hs_code');
    }

    public function test_missing_hs_code_is_required(): void
    {
        $user = User::factory()->create(['role' => 'analyst']);

        $response = $this->actingAs($user)->post('/cases', $this->basePayload([
            ['name' => '高精度传感器', 'hs_code' => ''],
        ]));

        $response->assertSessionHasErrors('products.0.hs_code');
    }

    public function test_at_least_one_product_is_required(): void
    {
        $user = User::factory()->create(['role' => 'analyst']);

        $payload = $this->basePayload([]);
        unset($payload['products']);

        $response = $this->actingAs($user)->post('/cases', $payload);

        $response->assertSessionHasErrors('products');
    }

    public function test_valid_us_six_digit_hs_code_is_accepted(): void
    {
        $user = User::factory()->create(['role' => 'analyst']);

        $response = $this->actingAs($user)->post('/cases', $this->basePayload([
            ['name' => '高精度传感器', 'hs_code' => '1234.56'],
        ]));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', ['name' => '高精度传感器', 'hs_code' => '1234.56']);
    }

    public function test_valid_brazil_ncm_eight_digit_hs_code_is_accepted(): void
    {
        $user = User::factory()->create(['role' => 'analyst']);

        $response = $this->actingAs($user)->post('/cases', $this->basePayload([
            ['name' => '集成电路', 'hs_code' => '8542.32.90'],
        ]));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', ['name' => '集成电路', 'hs_code' => '8542.32.90']);
    }
}
