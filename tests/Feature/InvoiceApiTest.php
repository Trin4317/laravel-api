<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InvoiceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_api(): void
    {
        $response = $this->getJson('/api/v1/invoices');

        $response->assertStatus(200);
    }
}
