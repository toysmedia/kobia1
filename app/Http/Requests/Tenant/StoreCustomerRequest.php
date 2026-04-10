<?php

namespace App\Http\Requests\Tenant;

/**
 * Validates new customer (PPPoE subscriber) creation.
 * Inherits test-data blocking from SecureFormRequest.
 */
class StoreCustomerRequest extends SecureFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function secureRules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['nullable', 'email', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:30'],
            'mobile'     => ['nullable', 'string', 'max:30'],
            'username'   => ['required', 'string', 'max:255'],
            'package_id' => ['required', 'integer'],
        ];
    }
}
