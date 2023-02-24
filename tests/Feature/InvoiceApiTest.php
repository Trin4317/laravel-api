<?php

namespace Tests\Feature;

use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class InvoiceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_all_invoices(): void
    {
        $invoices = Invoice::factory(20)->create();

        $response = $this->getJson('/api/v1/invoices');

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('meta')
                     ->has('links')
                     ->has('data', 10)
                     ->has('data.0', fn (AssertableJson $json) =>
                        $json->where('id', $invoices[0]->id)
                             ->where('customerId', $invoices[0]->customer->id)
                             ->where('amount', $invoices[0]->amount)
                             ->where('status', $invoices[0]->status)
                             ->etc()
                )
            );
    }

    public function test_user_can_filter_invoices_with_one_condition(): void
    {
        Invoice::factory(20)->create([
            'amount' => 10000
        ]);

        $invoiceWithBigAmount = Invoice::factory()->create([
            'amount' => 50000
        ]);

        $response = $this->getJson('/api/v1/invoices?amount[gt]=30000');

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('meta')
                     ->has('links')
                     ->has('data', 1)
                     ->has('data.0', fn (AssertableJson $json) =>
                        $json->where('id', $invoiceWithBigAmount->id)
                             ->where('amount', $invoiceWithBigAmount->amount)
                             ->where('customerId', $invoiceWithBigAmount->customer_id)
                             ->etc()
                )
            );
    }

    public function test_user_can_filter_invoices_with_many_conditions(): void
    {
        Invoice::factory(20)->create([
            'amount' => 10000,
            'status' => 'B'
        ]);

        Invoice::factory(2)->create([
            'amount' => 50000,
            'status' => 'P'
        ]);

        $response = $this->getJson('/api/v1/invoices?amount[gt]=30000&status[eq]=P');

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('meta')
                     ->has('links')
                     ->has('data', 2)
            );
    }

    public function test_user_can_get_specific_invoice(): void
    {
        $invoice = Invoice::factory()->create();

        $response = $this->getJson('/api/v1/invoices/' . $invoice->id);

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('data', fn (AssertableJson $json) =>
                    $json->where('id', $invoice->id)
                         ->where('customerId', $invoice->customer->id)
                         ->where('amount', $invoice->amount)
                         ->where('status', $invoice->status)
                         ->etc()
                )
            );
    }
}
