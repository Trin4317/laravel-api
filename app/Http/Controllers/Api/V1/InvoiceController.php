<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\V1\StoreInvoiceRequest;
use App\Http\Requests\V1\BulkStoreInvoiceRequest;
use App\Http\Requests\V1\UpdateInvoiceRequest;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Http\Resources\V1\InvoiceResource;
use App\Http\Resources\V1\InvoiceCollection;
use App\Filters\V1\InvoicesFilter;
use Illuminate\Support\Arr;
use Illuminate\Auth\Access\AuthorizationException;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // TODO: use DI and don't pass request object directly
        $filter = new InvoicesFilter();
        $eloQueries = $filter->transform(request());

        if (count($eloQueries) == 0) {
            return new InvoiceCollection(Invoice::paginate(10));
        } else {
            return new InvoiceCollection(Invoice::where($eloQueries)->paginate(10)->withQueryString());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvoiceRequest $request)
    {
        //
    }

    /**
     * Store multiple resources in storage.
     */
    public function bulkStore(BulkStoreInvoiceRequest $request)
    {
        // Note: Eloquent does not provide createMany method, so we use insert method instead
        // Limitations: https://laraveldaily.com/post/eloquent-create-query-builder-insert
        $bulk = collect($request->all())->map(function ($arr, $key) {
            return Arr::except($arr, ['customerId', 'billedDate', 'paidDate']);
        });

        Invoice::insert($bulk->toArray());
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        return new InvoiceResource($invoice);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        // Note: DELETE request does not have body like GET request, so it does not make sense to create a Form Request class
        // Hence, we authorize the user's action here
        if (!request()->user()->tokenCan('delete')) {
            throw new AuthorizationException;
        }

        $invoice->delete();
    }
}
