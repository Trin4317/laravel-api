<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreInvoiceRequest;
use App\Http\Requests\V1\BulkStoreInvoiceRequest;
use App\Http\Requests\V1\UpdateInvoiceRequest;
use App\Http\Resources\V1\InvoiceResource;
use App\Http\Resources\V1\InvoiceCollection;

class InvoiceController extends Controller
{
    private $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $invoiceList = $this->invoiceService->all($request);

        return new InvoiceCollection($invoiceList);
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
        $this->invoiceService->createMany($request);
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
    public function destroy(Request $request, Invoice $invoice)
    {
        $this->invoiceService->delete($request, $invoice);
    }
}
