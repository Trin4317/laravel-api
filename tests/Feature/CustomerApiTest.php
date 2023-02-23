<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_api(): void
    {
        $response = $this->getJson('/api/v1/customers');

        $response->assertStatus(200);
    }

    public function test_user_can_get_specific_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->getJson('/api/v1/customers/' . $customer->id);

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('data.id', $customer->id)
                     ->where('data.name', $customer->name)
                     ->where('data.postalCode', $customer->postal_code)
                     ->etc()
            );
    }
}
