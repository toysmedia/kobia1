<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IspSetting;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class IspSettingController extends Controller
{
    protected array $settingKeys = [
        // Company
        'company_name', 'billing_domain', 'address', 'phone', 'email',
        // RADIUS
        'radius_server_ip', 'radius_secret', 'radius_port', 'radius_acct_port', 'interim_update_interval',
        // M-Pesa
        'mpesa_environment', 'mpesa_consumer_key', 'mpesa_consumer_secret',
        'mpesa_shortcode', 'mpesa_passkey', 'mpesa_stk_callback_url',
        'mpesa_c2b_shortcode', 'mpesa_c2b_validation_url', 'mpesa_c2b_confirmation_url',
        // Payment Gateways
        'pg_mpesa_paybill_enabled', 'pg_mpesa_paybill_number', 'pg_mpesa_paybill_account', 'pg_mpesa_paybill_display',
        'pg_mpesa_till_enabled', 'pg_mpesa_till_number', 'pg_mpesa_till_display',
        'pg_kopokopo_enabled', 'pg_kopokopo_client_id', 'pg_kopokopo_client_secret', 'pg_kopokopo_till', 'pg_kopokopo_webhook_url', 'pg_kopokopo_env',
        'pg_equity_enabled', 'pg_equity_merchant_id', 'pg_equity_api_key', 'pg_equity_account', 'pg_equity_callback_url',
        'pg_kcb_enabled', 'pg_kcb_merchant_code', 'pg_kcb_api_key', 'pg_kcb_account', 'pg_kcb_callback_url',
        'pg_coop_enabled', 'pg_coop_consumer_key', 'pg_coop_consumer_secret', 'pg_coop_account', 'pg_coop_callback_url',
        // SMS
        'sms_gateway', 'sms_sender_id', 'sms_enabled',
        'at_username', 'at_api_key', 'at_sender_id',
        'blessedafrica_api_key', 'blessedafrica_api_url',
        'advanta_api_key', 'advanta_partner_id',
        // WhatsApp
        'whatsapp_api_url', 'whatsapp_instance_id', 'whatsapp_api_key', 'whatsapp_sender_number', 'whatsapp_enabled',
        // Email (SMTP)
        'mail_host', 'mail_port', 'mail_encryption', 'mail_username', 'mail_password', 'mail_from_name', 'mail_from_address',
        // Billing
        'default_pppoe_expiry_days', 'default_hotspot_expiry_hours', 'grace_period_hours',
        'auto_disconnect', 'currency', 'paybill_display_text',
    ];

    public function index()
    {
        $settings = [];
        foreach ($this->settingKeys as $key) {
            $settings[$key] = IspSetting::getValue($key, '');
        }
        return view('admin.isp.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $tab = $request->get('tab', 'company');

        $rules = [];
        switch ($tab) {
            case 'company':
                $rules = [
                    'company_name'   => 'nullable|string|max:100',
                    'billing_domain' => 'nullable|string|max:255',
                    'address'        => 'nullable|string|max:500',
                    'phone'          => 'nullable|string|max:30',
                    'email'          => 'nullable|email|max:100',
                ];
                break;
            case 'radius':
                $rules = [
                    'radius_server_ip'         => 'nullable|ip',
                    'radius_secret'            => 'nullable|string|max:100',
                    'radius_port'              => 'nullable|integer|min:1|max:65535',
                    'radius_acct_port'         => 'nullable|integer|min:1|max:65535',
                    'interim_update_interval'  => 'nullable|integer|min:60',
                ];
                break;
            case 'mpesa':
                $rules = [
                    'mpesa_environment'          => 'nullable|in:sandbox,production',
                    'mpesa_consumer_key'         => 'nullable|string|max:255',
                    'mpesa_consumer_secret'      => 'nullable|string|max:255',
                    'mpesa_shortcode'            => 'nullable|string|max:20',
                    'mpesa_passkey'              => 'nullable|string|max:255',
                    'mpesa_stk_callback_url'     => 'nullable|url|max:500',
                    'mpesa_c2b_shortcode'        => 'nullable|string|max:20',
                    'mpesa_c2b_validation_url'   => 'nullable|url|max:500',
                    'mpesa_c2b_confirmation_url' => 'nullable|url|max:500',
                ];
                break;
            case 'payment_gateways':
                $rules = [
                    'pg_mpesa_paybill_enabled'  => 'boolean',
                    'pg_mpesa_paybill_number'   => 'nullable|string|max:20',
                    'pg_mpesa_paybill_account'  => 'nullable|string|max:50',
                    'pg_mpesa_paybill_display'  => 'nullable|string|max:100',
                    'pg_mpesa_till_enabled'     => 'boolean',
                    'pg_mpesa_till_number'      => 'nullable|string|max:20',
                    'pg_mpesa_till_display'     => 'nullable|string|max:100',
                    'pg_kopokopo_enabled'       => 'boolean',
                    'pg_kopokopo_client_id'     => 'nullable|string|max:255',
                    'pg_kopokopo_client_secret' => 'nullable|string|max:255',
                    'pg_kopokopo_till'          => 'nullable|string|max:20',
                    'pg_kopokopo_webhook_url'   => 'nullable|url|max:500',
                    'pg_kopokopo_env'           => 'nullable|in:sandbox,production',
                    'pg_equity_enabled'         => 'boolean',
                    'pg_equity_merchant_id'     => 'nullable|string|max:100',
                    'pg_equity_api_key'         => 'nullable|string|max:255',
                    'pg_equity_account'         => 'nullable|string|max:50',
                    'pg_equity_callback_url'    => 'nullable|url|max:500',
                    'pg_kcb_enabled'            => 'boolean',
                    'pg_kcb_merchant_code'      => 'nullable|string|max:100',
                    'pg_kcb_api_key'            => 'nullable|string|max:255',
                    'pg_kcb_account'            => 'nullable|string|max:50',
                    'pg_kcb_callback_url'       => 'nullable|url|max:500',
                    'pg_coop_enabled'           => 'boolean',
                    'pg_coop_consumer_key'      => 'nullable|string|max:255',
                    'pg_coop_consumer_secret'   => 'nullable|string|max:255',
                    'pg_coop_account'           => 'nullable|string|max:50',
                    'pg_coop_callback_url'      => 'nullable|url|max:500',
                ];
                break;
            case 'sms':
                $rules = [
                    'sms_gateway'          => 'nullable|in:africastalking,blessedafrica,advanta',
                    'sms_sender_id'        => 'nullable|string|max:50',
                    'sms_enabled'          => 'boolean',
                    'at_username'          => 'nullable|string|max:100',
                    'at_api_key'           => 'nullable|string|max:255',
                    'blessedafrica_api_key' => 'nullable|string|max:255',
                    'blessedafrica_api_url' => 'nullable|url|max:500',
                    'advanta_api_key'      => 'nullable|string|max:255',
                    'advanta_partner_id'   => 'nullable|string|max:100',
                ];
                break;
            case 'whatsapp':
                $rules = [
                    'whatsapp_api_url'       => 'nullable|url|max:500',
                    'whatsapp_instance_id'   => 'nullable|string|max:100',
                    'whatsapp_api_key'       => 'nullable|string|max:255',
                    'whatsapp_sender_number' => 'nullable|string|max:20',
                    'whatsapp_enabled'       => 'boolean',
                ];
                break;
            case 'email':
                $rules = [
                    'mail_host'         => 'nullable|string|max:255',
                    'mail_port'         => 'nullable|integer|min:1|max:65535',
                    'mail_encryption'   => 'nullable|in:tls,ssl',
                    'mail_username'     => 'nullable|string|max:255',
                    'mail_password'     => 'nullable|string|max:255',
                    'mail_from_name'    => 'nullable|string|max:100',
                    'mail_from_address' => 'nullable|email|max:100',
                ];
                break;
            case 'billing':
                $rules = [
                    'default_pppoe_expiry_days'    => 'nullable|integer|min:1',
                    'default_hotspot_expiry_hours' => 'nullable|integer|min:1',
                    'grace_period_hours'           => 'nullable|integer|min:0',
                    'auto_disconnect'              => 'nullable|in:yes,no',
                    'currency'                     => 'nullable|string|max:10',
                    'paybill_display_text'         => 'nullable|string|max:255',
                ];
                break;
        }

        $data = $request->validate($rules);

        // Normalise boolean/checkbox fields to '1'/'0'
        $booleanFields = [
            'sms_enabled', 'whatsapp_enabled',
            'pg_mpesa_paybill_enabled', 'pg_mpesa_till_enabled',
            'pg_kopokopo_enabled', 'pg_equity_enabled', 'pg_kcb_enabled', 'pg_coop_enabled',
        ];
        foreach ($booleanFields as $field) {
            if (array_key_exists($field, $data) || $request->has($field)) {
                $data[$field] = $request->boolean($field) ? '1' : '0';
            }
        }

        foreach ($data as $key => $value) {
            IspSetting::setValue($key, $value ?? '');
        }

        AuditLog::record('settings.updated', IspSetting::class, null, [], array_merge(['tab' => $tab], $data));

        $tabNames = [
            'company'          => 'Company',
            'radius'           => 'RADIUS',
            'mpesa'            => 'M-Pesa',
            'payment_gateways' => 'Payment Gateways',
            'sms'              => 'SMS',
            'whatsapp'         => 'WhatsApp',
            'email'            => 'Email',
            'billing'          => 'Billing',
        ];
        $tabLabel = $tabNames[$tab] ?? ucfirst($tab);

        return back()->with('success', $tabLabel . ' settings saved successfully.')->with('active_tab', $tab);
    }
}