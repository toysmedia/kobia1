<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TenantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Handles ISP admin self-registration.
 * Creates the tenant record, provisions the database, and redirects to the new subdomain.
 */
class TenantAdminRegisterController extends Controller
{
    public function __construct(
        private readonly TenantService $tenantService,
    ) {}

    /**
     * Show the tenant registration form.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.tenant.register');
    }

    /**
     * Validate and register a new ISP tenant.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('tenants', 'email')],
            'phone'     => ['nullable', 'string', 'max:20'],
            'subdomain' => [
                'required',
                'string',
                'min:3',
                'max:63',
                'regex:/^[a-z0-9][a-z0-9\-]*[a-z0-9]$/',
                Rule::unique('tenants', 'subdomain'),
            ],
            'plan'      => ['nullable', 'string', 'exists:pricing_plans,slug'],
        ]);

        $result = $this->tenantService->create($validated);
        $tenant = $result['tenant'];

        $appDomain = env('APP_DOMAIN', 'oxnet.co.ke');
        $tenantUrl = url("https://{$tenant->subdomain}.{$appDomain}/login");

        return redirect($tenantUrl)
            ->with('success', "Welcome! Your ISP portal is ready at {$tenant->subdomain}.{$appDomain}");
    }
}
