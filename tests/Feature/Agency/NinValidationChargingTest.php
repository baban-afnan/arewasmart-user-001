<?php

namespace Tests\Feature\Agency;

use App\Models\AgentService;
use App\Models\Service;
use App\Models\ServiceField;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NinValidationChargingTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $wallet;
    protected $serviceField;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['status' => 'active', 'role' => 'user']);
        $this->wallet = Wallet::create([
            'user_id' => $this->user->id,
            'balance' => 500.00,
            'status' => 'active',
            'wallet_number' => '1234567890'
        ]);

        $service = Service::create(['name' => 'Validation', 'is_active' => true]);
        $this->serviceField = ServiceField::create([
            'service_id' => $service->id,
            'field_name' => 'NIN Validation',
            'field_code' => '123',
            'base_price' => 100.00
        ]);
    }

    /** @test */
    public function it_charges_before_api_call_and_refunds_on_failure()
    {
        Http::fake([
            '*' => Http::response(['status' => 'failed', 'message' => 'API Error'], 400),
        ]);

        $response = $this->actingAs($this->user)->postJson(route('nin-validation.store'), [
            'service_field' => $this->serviceField->id,
            'nin' => '12345678901',
            'service_type' => 'validation'
        ]);

        $response->assertStatus(302); // Should still redirect back with success/error session

        $this->wallet->refresh();
        $this->assertEquals(500.00, $this->wallet->balance); // Refunded
        
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'status' => 'failed',
            'amount' => 100.00
        ]);

        $this->assertDatabaseHas('agent_services', [
            'user_id' => $this->user->id,
            'status' => 'failed',
            'comment' => 'API Error'
        ]);
    }

    /** @test */
    public function it_successfully_charges_on_api_success()
    {
        Http::fake([
            '*' => Http::response(['status' => 'success', 'message' => 'Verified'], 200),
        ]);

        $response = $this->actingAs($this->user)->postJson(route('nin-validation.store'), [
            'service_field' => $this->serviceField->id,
            'nin' => '12345678901',
            'service_type' => 'validation'
        ]);

        $response->assertStatus(302); // Should still redirect back with success/error session

        $this->wallet->refresh();
        $this->assertEquals(400.00, $this->wallet->balance); // Charged
        
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'status' => 'completed',
            'amount' => 100.00
        ]);

        $this->assertDatabaseHas('agent_services', [
            'user_id' => $this->user->id,
            'status' => 'successful'
        ]);
    }

    /** @test */
    public function it_prevents_low_balance_submissions()
    {
        $this->wallet->update(['balance' => 0.00]);

        $response = $this->actingAs($this->user)->post(route('nin.validation.store'), [
            'service_field' => $this->serviceField->id,
            'nin' => '12345678901',
            'service_type' => 'validation'
        ]);

        $response->assertSessionHas('error', 'Insufficient wallet balance.');
        $this->assertEquals(0.00, $this->wallet->fresh()->balance);
    }
}
