<?php

namespace Tests\Feature;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class InvoiceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_all_invoices(): void
    {
        $this->logInWithAbilities(['none']);

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
        $this->logInWithAbilities(['none']);

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
        $this->logInWithAbilities(['none']);

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
        $this->logInWithAbilities(['none']);

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

    public function test_user_can_bulk_insert_many_invoices(): void
    {
        $this->logInWithAbilities(['create']);

        $invoices = Invoice::factory(3)->raw();
        $invoices = collect($invoices)->map(function ($arr, $key) {
            $arr = Arr::add($arr, 'customerId', $arr['customer_id']);
            $arr = Arr::add($arr, 'billedDate', $arr['billed_date']->format('Y-m-d H:i:s'));
            $arr = Arr::add($arr, 'paidDate', $arr['paid_date']?->format('Y-m-d H:i:s'));

            return Arr::except($arr, ['customer_id', 'billed_date', 'paid_date']);
        });

        $response = $this->postJson('/api/v1/invoices/bulk', $invoices->toArray());

        $response->assertStatus(200);
        $this->assertDatabaseHas('invoices', [
            'customer_id' => $invoices[0]['customerId'],
            'amount'      => $invoices[0]['amount'],
            'status'      => $invoices[0]['status'],
            'billed_date' => $invoices[0]['billedDate'],
        ]);
    }

    public function test_user_can_not_bulk_insert_many_invoices_without_providing_full_attributes(): void
    {
        $this->logInWithAbilities(['create']);

        $invoices = Invoice::factory(2)->raw();

        $response = $this->postJson('/api/v1/invoices/bulk', $invoices);

        $response
            ->assertStatus(422)
            ->assertJsonPath('error.message', fn (string $message) =>
                str_contains($message, 'The 0.customerId field')
            );
    }

    public function test_user_can_delete_existing_invoice(): void
    {
        $this->logInWithAbilities(['delete']);

        $invoice = Invoice::factory()->create();

        $response = $this->deleteJson('/api/v1/invoices/' . $invoice->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('invoices', [
            'id' => $invoice->id
        ]);
    }

    public function test_user_can_not_delete_existing_invoice_without_correct_token_ability(): void
    {
        $this->logInWithAbilities(['none']);

        $invoice = Invoice::factory()->create();

        $response = $this->deleteJson('/api/v1/invoices/' . $invoice->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id
        ]);
    }
}
