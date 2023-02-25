<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
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
        $method = request()->method();

        if ($method === 'PUT') {
            return [
                'name'       => ['required'],
                'type'       => ['required', Rule::in('I', 'B', 'i', 'b')],
                'email'      => ['required', 'email'],
                'address'    => ['required'],
                'city'       => ['required'],
                'state'      => ['required'],
                'postalCode' => ['required']
            ];
        } else { // PATCH request
            return [
                'name'       => ['sometimes', 'required'],
                'type'       => ['sometimes', 'required', Rule::in('I', 'B', 'i', 'b')],
                'email'      => ['sometimes', 'required', 'email'],
                'address'    => ['sometimes', 'required'],
                'city'       => ['sometimes', 'required'],
                'state'      => ['sometimes', 'required'],
                'postalCode' => ['sometimes', 'required']
            ];
        }
    }

    protected function prepareForValidation()
    {
        // In case PATCH request does not include postal code
        if ($this->postalCode) {
            $this->merge([
                'postal_code' => $this->postalCode
            ]);
        }
    }
}
