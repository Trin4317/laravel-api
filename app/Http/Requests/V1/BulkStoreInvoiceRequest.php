<?php

namespace App\Http\Requests\V1;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkStoreInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: implement authorization
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            '*.customerId' => ['required', 'integer'],
            '*.amount'     => ['required', 'numeric'],
            '*.status'     => ['required', Rule::in('B', 'P', 'V', 'b', 'p', 'v')],
            '*.billedDate' => ['required', 'date_format:Y-m-d H:i:s'],
            '*.paidDate'   => ['nullable', 'date_format:Y-m-d H:i:s'],
        ];
    }

    protected function prepareForValidation()
    {
        // Assuming the request payload is as following
        // [{"customerId":1,"amount":1111,"status":"B","billedDate":"2014-11-06 13:46:54"}]
        $data = [];

        foreach ($this->toArray() as $obj) {
            $obj['customer_id'] = $obj['customerId'] ?? null;
            $obj['billed_date'] = $obj['billedDate'] ?? null;
            $obj['paid_date']   = $obj['paidDate'] ?? null;
            $obj['created_at']  = Carbon::now();
            $obj['updated_at']  = Carbon::now();

            $data[] = $obj;
        }

        // The modified payload before validation will become
        // [{"customerId":1,"amount":1111,"status":"B","billedDate":"2014-11-06 13:46:54",
        //   "customer_id":1,"billed_date":"2014-11-06 13:46:54","paid_date":null,
        //   "created_at":"2014-11-06 13:46:54","updated_at":"2014-11-06 13:46:54"}]
        $this->merge($data);
    }
}
