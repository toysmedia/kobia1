<?php

namespace App\Http\Requests\Tenant;

/**
 * Validates PPPoE subscriber creation.
 * Inherits test-data blocking from SecureFormRequest.
 */
class StoreSubscriberRequest extends SecureFormRequest
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
            'mobile'     => ['nullable', 'string', 'max:30'],
            'username'   => ['required', 'string', 'max:255'],
            'password'   => ['required', 'string', 'min:4'],
            'package_id' => ['required', 'integer'],
            'seller_id'  => ['nullable', 'integer'],
        ];
    }
}
