<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_all_customers(): void
    {
        $this->logInWithAbilities(['none']);

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

    public function test_user_can_filter_customers_with_one_condition(): void
    {
        $this->logInWithAbilities(['none']);

        Customer::factory(20)->create([
            'postal_code' => '10000'
        ]);

        $customerWithBigPostalCode = Customer::factory()->create([
            'postal_code' => '50000'
        ]);

        $response = $this->getJson('/api/v1/customers?postalCode[gt]=30000');

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('meta')
                     ->has('links')
                     ->has('data', 1)
                     ->has('data.0', fn (AssertableJson $json) =>
                        $json->where('id', $customerWithBigPostalCode->id)
                             ->where('name', $customerWithBigPostalCode->name)
                             ->where('postalCode', $customerWithBigPostalCode->postal_code)
                             ->etc()
                )
            );
    }

    public function test_user_can_filter_customers_with_many_conditions(): void
    {
        $this->logInWithAbilities(['none']);

        Customer::factory(20)->create([
            'postal_code' => '10000',
            'type' => 'I'
        ]);

        Customer::factory(2)->create([
            'postal_code' => '50000',
            'type' => 'B'
        ]);

        $response = $this->getJson('/api/v1/customers?postalCode[gt]=30000&type[eq]=B');

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('meta')
                     ->has('links')
                     ->has('data', 2)
            );
    }

    public function test_user_can_get_all_customers_with_invoces_included(): void
    {
        $this->logInWithAbilities(['none']);

        Customer::factory()
            ->count(20)
            ->hasInvoices(10)
            ->create();

        $response = $this->getJson('/api/v1/customers?includeInvoices=true');

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('meta')
                     ->has('links')
                     ->has('data', 10)
                     ->has('data.0.invoices', 10)
            );
    }

    public function test_user_can_get_specific_customer(): void
    {
        $this->logInWithAbilities(['none']);

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

    public function test_user_can_get_specific_customer_with_invoces_included(): void
    {
        $this->logInWithAbilities(['none']);

        $customer = Customer::factory()
                            ->hasInvoices(10)
                            ->create();

        $response = $this->getJson('/api/v1/customers/' . $customer->id . '?includeInvoices=true');

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('data', fn (AssertableJson $json) =>
                    $json->where('id', $customer->id)
                         ->where('name', $customer->name)
                         ->where('postalCode', $customer->postal_code)
                         ->etc()
                    )->has('data.invoices', 10)
            );
    }

    public function test_user_can_create_new_customer(): void
    {
        $this->logInWithAbilities(['create']);

        $customer = Customer::factory()->raw();
        $customer = Arr::add($customer, 'postalCode', $customer['postal_code']);
        Arr::forget($customer, 'postal_code');

        $response = $this->postJson('/api/v1/customers/', $customer);

        $response
            ->assertStatus(201)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('data', fn (AssertableJson $json) =>
                    $json->where('name', $customer['name'])
                    ->where('type', $customer['type'])
                    ->where('postalCode', $customer['postalCode'])
                    ->etc()
                )
            );
    }

    public function test_user_can_not_create_new_customer_when_failing_validation(): void
    {
        $this->logInWithAbilities(['create']);

        $customer = Customer::factory()->raw();
        $customer = Arr::add($customer, 'postalCode', $customer['postal_code']);
        Arr::forget($customer, 'postal_code');
        Arr::forget($customer, 'name');

        $response = $this->postJson('/api/v1/customers/', $customer);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('name', 'errors');
    }

    public function test_user_can_update_existing_customer(): void
    {
        $this->logInWithAbilities(['update']);

        $customer = Customer::factory()
                             ->create()
                             ->makeHidden(['postal_code', 'updated_at', 'created_at'])
                             ->toArray();

        Arr::set($customer, 'name', 'John Doe');
        Arr::set($customer, 'type', 'I');
        Arr::set($customer, 'email', 'john@example.com');
        Arr::set($customer, 'city', 'New York');
        $customer = Arr::add($customer, 'postalCode', '70000');

        $response = $this->putJson('/api/v1/customers/' . $customer['id'], $customer);

        $response->assertStatus(200);
        $this->assertDatabaseHas('customers', [
            'name'        => 'John Doe',
            'type'        => 'I',
            'email'       => 'john@example.com',
            'city'        => 'New York',
            'postal_code' => '70000'
        ]);
    }

    public function test_user_can_not_update_existing_customer_without_providing_full_attributes(): void
    {
        $this->logInWithAbilities(['update']);

        $customer = Customer::factory()
                             ->create()
                             ->makeHidden(['name', 'updated_at', 'created_at'])
                             ->toArray();

        $response = $this->putJson('/api/v1/customers/' . $customer['id'], $customer);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('name', 'errors');
    }

    public function test_user_can_patch_existing_customer(): void
    {
        $this->logInWithAbilities(['update']);

        $customer = Customer::factory()
                             ->create()
                             ->makeHidden(['type', 'address', 'state', 'postal_code', 'updated_at', 'created_at'])
                             ->toArray();

        Arr::set($customer, 'name', 'John Doe');
        Arr::set($customer, 'email', 'john@example.com');
        Arr::set($customer, 'city', 'New York');

        $response = $this->patchJson('/api/v1/customers/' . $customer['id'], $customer);

        $response->assertStatus(200);
        $this->assertDatabaseHas('customers', [
            'name'        => 'John Doe',
            'email'       => 'john@example.com',
            'city'        => 'New York'
        ]);
    }
}
