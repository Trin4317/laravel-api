<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Auth\Access\AuthorizationException;
use App\Models\Invoice;
use App\Filters\V1\InvoicesFilter;
use App\Http\Requests\V1\BulkStoreInvoiceRequest;

class InvoiceService
{
    private $invoiceFilter;

    public function __construct(InvoicesFilter $invoiceFilter)
    {
        $this->invoiceFilter = $invoiceFilter;
    }

    public function all(Request $request)
    {
        $eloQueries = $this->invoiceFilter->transform($request);
        $invoices = Invoice::where($eloQueries);

        return $invoices->paginate(10)->withQueryString();
    }

    public function createMany(BulkStoreInvoiceRequest $request)
    {
        // Note: Eloquent does not provide createMany method, so we use insert method instead
        // Limitations: https://laraveldaily.com/post/eloquent-create-query-builder-insert
        $bulk = collect($request->all())->map(function ($arr, $key) {
            return Arr::except($arr, ['customerId', 'billedDate', 'paidDate']);
        });

        Invoice::insert($bulk->toArray());
    }

    public function delete(Request $request, Invoice $invoice)
    {
        // Note: DELETE request does not have body like GET request, so it does not make sense to create a Form Request class
        // Hence, we authorize the user's action here
        if (! $request->user()->tokenCan('delete')) {
            throw new AuthorizationException;
        }

        $invoice->delete();
    }
}
