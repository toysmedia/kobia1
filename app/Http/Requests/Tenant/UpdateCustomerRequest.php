<?php

namespace App\Http\Requests\Tenant;

/**
 * Validates customer (PPPoE subscriber) update.
 * Inherits test-data blocking from SecureFormRequest.
 */
class UpdateCustomerRequest extends SecureFormRequest
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
            'package_id' => ['sometimes', 'integer'],
        ];
    }
}
