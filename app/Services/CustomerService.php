<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use App\Models\Customer;
use App\Filters\V1\CustomersFilter;
use App\Http\Requests\V1\StoreCustomerRequest;
use App\Http\Requests\V1\UpdateCustomerRequest;

class CustomerService
{
    private $customerFilter;

    public function __construct(CustomersFilter $customerFilter)
    {
        $this->customerFilter = $customerFilter;
    }

    public function all(Request $request)
    {
        $eloQueries = $this->customerFilter->transform($request);
        $customers = Customer::where($eloQueries);

        $includeInvoices = $request->query('includeInvoices');
        if ($includeInvoices) {
            $customers = $customers->with('invoices');
        }

        return $customers->paginate(10)->withQueryString();
    }

    public function create(StoreCustomerRequest $request)
    {
        return Customer::create($request->all());
    }

    public function get(Request $request, Customer $customer)
    {
        $includeInvoices = $request->query('includeInvoices');
        if ($includeInvoices) {
            return $customer->loadMissing('invoices');
        }

        return $customer;
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->all());
    }

    public function delete(Request $request, Customer $customer)
    {
        // Note: DELETE request does not have body like GET request, so it does not make sense to create a Form Request class
        // Hence, we authorize the user's action here
        if (! $request->user()->tokenCan('delete')) {
            throw new AuthorizationException;
        }

        $customer->delete();
    }
}
