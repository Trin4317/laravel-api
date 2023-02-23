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
