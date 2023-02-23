<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_all_customers(): void
    {
        $customers = Customer::factory(20)->create();

        $response = $this->getJson('/api/v1/customers');

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('meta')
                     ->has('links')
                     ->has('data', 10)
                     ->has('data.0', fn (AssertableJson $json) =>
                        $json->where('id', $customers[0]->id)
                             ->where('name', $customers[0]->name)
                             ->where('postalCode', $customers[0]->postal_code)
                             ->etc()
                )
            );
    }

    public function test_user_can_get_specific_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->getJson('/api/v1/customers/' . $customer->id);

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('data', fn (AssertableJson $json) =>
                    $json->where('id', $customer->id)
                         ->where('name', $customer->name)
                         ->where('postalCode', $customer->postal_code)
                         ->etc()
                )
            );
    }
}
