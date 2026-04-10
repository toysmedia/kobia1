<?php

namespace App\Http\Requests\Tenant;

use App\Events\SuspiciousActivityDetected;
use App\Rules\NotTestEmail;
use App\Rules\NotTestName;
use App\Rules\NotTestPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

/**
 * Base form request for all tenant forms.
 * Automatically applies test-data validation rules and dispatches
 * the SuspiciousActivityDetected event on violation.
 */
abstract class SecureFormRequest extends FormRequest
{
    /**
     * Augment the rules from child classes with test-data rules.
     * Child classes must implement secureRules() instead of rules().
     */
    final public function rules(): array
    {
        return array_merge_recursive($this->secureRules(), $this->testDataRules());
    }

    /**
     * Define the domain-specific validation rules in subclasses.
     */
    abstract protected function secureRules(): array;

    /**
     * Build test-data validation rules for common fields when present.
     */
    protected function testDataRules(): array
    {
        $rules = [];

        foreach ($this->emailFields() as $field) {
            if ($this->has($field)) {
                $rules[$field][] = new NotTestEmail();
            }
        }

        foreach ($this->phoneFields() as $field) {
            if ($this->has($field)) {
                $rules[$field][] = new NotTestPhone();
            }
        }

        foreach ($this->nameFields() as $field) {
            if ($this->has($field)) {
                $rules[$field][] = new NotTestName();
            }
        }

        return $rules;
    }

    /**
     * List of email field names to validate.
     */
    protected function emailFields(): array
    {
        return ['email', 'email_address', 'admin_email'];
    }

    /**
     * List of phone field names to validate.
     */
    protected function phoneFields(): array
    {
        return ['phone', 'mobile', 'phone_number', 'mobile_number'];
    }

    /**
     * List of name field names to validate.
     */
    protected function nameFields(): array
    {
        return ['name', 'first_name', 'last_name', 'full_name'];
    }

    /**
     * On validation failure, dispatch the SuspiciousActivityDetected event
     * if any test-data rules were violated.
     */
    protected function failedValidation(Validator $validator): void
    {
        $errors    = $validator->errors()->toArray();
        $testRules = array_keys($this->testDataRules());
        $violations = array_intersect_key($errors, array_flip($testRules));

        if (! empty($violations)) {
            event(new SuspiciousActivityDetected(
                url: $this->fullUrl(),
                violations: $violations,
                inputs: $this->except(['password', 'password_confirmation']),
                userId: auth('admin')->id(),
                tenantId: optional(app()->bound('current_tenant') ? app('current_tenant') : null)?->id,
                ipAddress: $this->ip() ?? '',
            ));
        }

        parent::failedValidation($validator);
    }
}
