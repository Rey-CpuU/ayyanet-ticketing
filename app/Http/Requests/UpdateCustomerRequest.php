<?php

namespace App\Http\Requests;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $customer = $this->route('customer');

        return $customer instanceof Customer && ($this->user()?->can('update', $customer) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return (new StoreCustomerRequest)->rules();
    }
}
