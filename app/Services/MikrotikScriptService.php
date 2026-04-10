<?php

namespace App\Services;

use App\Models\Router;

class MikrotikScriptService
{
    // ── Public API ─────────────────────────────────────────────────────────────

    /**
     * Generate the full provisioning script for the given router.
     *
     * Section 1 (Foundation) is always included.
     * Subsequent sections depend on the router's service_mode:
     *   pppoe         → Section 1 + Section 2
     *   hotspot       → Section 1 + Section 3
     *   pppoe_hotspot → Section 1 + Section 2 + Section 3
     *   combined      → Section 1 + Section 4
     */
    public function generate(Router $router): string
    {
        $sections = [];

        $sections[] = $this->generateSection1($router);

        $mode = $router->service_mode ?? 'pppoe_hotspot';

        switch ($mode) {
            case 'pppoe':
                $sections[] = $this->generateSection2($router);
                break;

            case 'hotspot':
                $sections[] = $this->generateSection3($router);
                break;

            case 'combined':
                $sections[] = $this->generateSection4($router);
                break;

            case 'pppoe_hotspot':
            default:
                $sections[] = $this->generateSection2($router);
                $sections[] = $this->generateSection3($router);
                break;
        }

        return implode("\n\n", $sections);
    }

    /**
     * Generate a .rsc file string safe for /import on MikroTik.
     *
     * Root cause of "expected end of command" errors:
     *   RouterOS /import does NOT support \" escaping inside on-event="..." strings.
     *   The parser closes the string at the first bare " it encounters, so any ""
     *   or "string" inside the on-event value causes "expected end of command".
     *   Fix: use on-event={...} brace delimiters. RouterOS brace blocks:
     *     - Are depth-balanced (nested {} are fine as long as they balance)
     *     - Do NOT require any escaping of " inside them
     *     - Are exactly what /system export produces natively
     *
     * Usage on router:
     *   /import file-name=RTR-011.rsc
     */
    public function generateRsc(Router $router): string
    {
        $script   = $this->generate($router);
        $name     = $router->name     ?? 'MikroTik';
        $refCode  = $router->ref_code ?? 'provision';
        $mode     = $router->service_mode ?? 'pppoe_hotspot';
        $date     = now()->toDateTimeString();

        $header  = "# =====================================================\n";
        $header .= "# Provisioning script for : {$name}\n";
        $header .= "# Ref code                : {$refCode}\n";
        $header .= "# Mode                    : {$mode}\n";
        $header .= "# Generated               : {$date}\n";
        $header .= "# =====================================================\n";
        $header .= "# Upload via WinBox > Files, then in terminal run:\n";
        $header .= "#   /import file-name={$refCode}.rsc\n";
        $header .= "# =====================================================\n\n";

        return $header . $script;
    }

    /**
     * Get Section 1 (Foundation) script only.
     */
    public function generateFoundation(Router $router): string
    {
        return $this->generateSection1($router);
    }

    /**
     * Get Section 2/3/4 (Services) script only.
     * Depends on router's service_mode.
     */
    public function generateServices(Router $router): string
    {
        $sections = [];
        $mode = $router->service_mode ?? 'pppoe_hotspot';

        switch ($mode) {
            case 'pppoe':
                $sections[] = $this->generateSection2($router);
                break;
            case 'hotspot':
                $sections[] = $this->generateSection3($router);
                break;
            case 'combined':
                $sections[] = $this->generateSection4($router);
                break;
            default:
                $sections[] = $this->generateSection2($router);
                $sections[] = $this->generateSection3($router);
                break;
        }

        return implode("\n\n", $sections);
    }

    // -------------------------------------------------------------------------
    // Section 1 — Foundation (always included)
    // -------------------------------------------------------------------------

    private function generateSection1(Router $router): string
    {
        $radiusSecret        = $this->s($router->radius_secret ?? '');
        $radiusServerIp      = $this->s($this->billingVpnIp($router));
        $billingPublicIp     = $this->s($this->billingPublicIp($router));
        $billingSubnet       = $this->s(config('openvpn.billing_server_subnet', ''));
        $openvpnPort         = (int) ($router->openvpn_port ?? config('openvpn.port', 443));

        $caFilename          = $this->s($router->ca_cert_filename     ?? 'ca.crt');
        $routerCertFilename  = $this->s($router->router_cert_filename  ?? 'router.crt');
        $routerKeyFilename   = $this->s($this->deriveKeyFilename($router->router_cert_filename ?? 'router.crt'));

        $caCertName          = pathinfo($router->ca_cert_filename     ?? 'ca.crt',    PATHINFO_FILENAME);
        $routerCertName      = pathinfo($router->router_cert_filename  ?? 'router.crt', PATHINFO_FILENAME);

        $openvpnCipher       = $this->s(config('openvpn.cipher',      'aes256-cbc'));
        $openvpnAuth         = $this->s(config('openvpn.auth',        'sha1'));
        $openvpnTls          = $this->s(config('openvpn.tls_version', 'any'));

        // resolveBillingDomain() returns a raw domain (no protocol, no trailing slash).
        // $billingDomain  — used raw inside brace on-event blocks (no extra escaping)
        // $billingDomainS — s()-escaped for use inside double-quoted ROS strings
        $billingDomain       = $this->resolveBillingDomain($router);
        $billingDomainS      = $this->s($billingDomain);

        $refCode             = $this->s($router->ref_code ?? '');
        $mgmtUserName        = $this->s($router->ref_code ?? $router->name ?? 'mgmt-router');
        $timezone            = $this->s($router->timezone ?? 'Africa/Nairobi');
        $routerName          = $this->s($router->name ?? 'MikroTik');

        $callbackSecret      = config('app.router_callback_secret', '');
        $routerNameUrl       = urlencode($router->name ?? 'MikroTik');
        $callbackSecretUrl   = urlencode($callbackSecret);

        $lines = [];

        // --- Router identity ---
        $lines[] = "/system identity set name=\"{$routerName}\"";
        $lines[] = "";

        // 1. DNS
        $lines[] = "/ip dns set servers=8.8.8.8,8.8.4.4 allow-remote-requests=no";
        $lines[] = "";
        $lines[] = "/interface detect-internet set detect-interface-list=all internet-interface-list=all";
        $lines[] = "";

        // 2. Certificate download
        $lines[] = ":execute script=\"/tool fetch url=\\\"https://{$billingDomainS}/api/router-certs/{$refCode}/ca.crt\\\" dst-path={$caFilename}\"";
        $lines[] = ":execute script=\"/tool fetch url=\\\"https://{$billingDomainS}/api/router-certs/{$refCode}/router.crt\\\" dst-path={$routerCertFilename}\"";
        $lines[] = ":execute script=\"/tool fetch url=\\\"https://{$billingDomainS}/api/router-certs/{$refCode}/router.key\\\" dst-path={$routerKeyFilename}\"";

        $certDownloadTimeout = (int) config('mikrotik.cert_download_timeout', 120);
        $lines[] = ":local certReady false; :local certWait 0; :while (\$certReady != true && \$certWait < {$certDownloadTimeout}) do={ :if ([:len [/file find name={$caFilename}]] > 0 && [:len [/file find name={$routerCertFilename}]] > 0 && [:len [/file find name={$routerKeyFilename}]] > 0) do={ :set certReady true } else={ :delay 2s; :set certWait (\$certWait + 2) } }";
        $lines[] = ":if (\$certReady != true) do={ :log error \"Provisioning: cert files not downloaded after {$certDownloadTimeout}s\" }";
        $lines[] = ":delay 2s";
        $lines[] = "";

        $lines[] = ":execute script=\"/certificate import file-name={$caFilename} passphrase=\\\"\\\"\"";
        $lines[] = ":delay 3s";
        $lines[] = ":do { /certificate set [find name={$caCertName}] trusted=yes } on-error={}";
        $lines[] = ":execute script=\"/certificate import file-name={$routerCertFilename} passphrase=\\\"\\\"\"";
        $lines[] = ":delay 3s";
        $lines[] = ":execute script=\"/certificate import file-name={$routerKeyFilename} passphrase=\\\"\\\"\"";
        $lines[] = ":delay 3s";
        $lines[] = ":do { /certificate settings set crl-download=yes crl-use=yes } on-error={}";
        $lines[] = "";

        // 3. RADIUS Client
        $lines[] = ":do { /radius remove [find address={$radiusServerIp}] } on-error={}";
        $lines[] = ":execute script=\"/radius add address={$radiusServerIp} secret=\\\"{$radiusSecret}\\\" service=hotspot,ppp timeout=3s\"";
        $lines[] = ":delay 2s";
        $lines[] = "/radius incoming set accept=yes port=3799";
        $lines[] = "";

        // 4. Management User
        $lines[] = ":do { /user remove [find name=\"{$mgmtUserName}\"] } on-error={}";
        $lines[] = ":execute script=\"/user add name=\\\"{$mgmtUserName}\\\" password=\\\"{$radiusSecret}\\\" group=full\"";
        $lines[] = ":delay 2s";
        $lines[] = "";

        // 5. Timezone
        $lines[] = "/system clock set time-zone-name={$timezone} time-zone-autodetect=no";
        $lines[] = "";

        // 6. NTP Client
        $ntpServer  = '216.239.35.8';
        $rosVersion = $router->routeros_version ?? '';
        $rosMajor   = $rosVersion !== '' ? (int) explode('.', $rosVersion)[0] : 0;
        if ($rosMajor === 6) {
            $lines[] = "/system ntp client set enabled=yes primary-ntp={$ntpServer}";
        } else {
            $lines[] = "/system ntp client set enabled=yes";
            $lines[] = ":do { /system ntp client servers remove [find address={$ntpServer}] } on-error={}";
            $lines[] = "/system ntp client servers add address={$ntpServer}";
        }
        $lines[] = "";

        // 7. Firewall Baseline
        if ($billingSubnet !== '') {
            $lines[] = ":do { /ip firewall filter remove [find comment=\"accept billing mgmt\"] } on-error={}";
            $lines[] = "/ip firewall filter add chain=input src-address={$billingSubnet} action=accept comment=\"accept billing mgmt\" place-before=*0";
        }
        $lines[] = "/ip firewall filter disable [find where comment=\"defconf: fasttrack\"]";
        $lines[] = ":do { /ip firewall filter remove [find comment=\"drop expired\"] } on-error={}";
        $lines[] = "/ip firewall filter add chain=forward src-address-list=expired action=drop comment=\"drop expired\" place-before=*0";
        $lines[] = "";

        // 8. NAT Masquerade
        $lines[] = ":do { /ip firewall nat remove [find comment=\"billing masquerade\"] } on-error={}";
        $lines[] = "/ip firewall nat add chain=srcnat action=masquerade comment=\"billing masquerade\" place-before=*0";
        $lines[] = "";

        // 9. OpenVPN Tunnel
        $lines[] = ":do { /ppp profile remove [find name=ovpn-mgmt] } on-error={}";
        $lines[] = "/ppp profile add name=ovpn-mgmt change-tcp-mss=yes use-encryption=yes";
        $lines[] = ":do { /interface ovpn-client remove [find name=ovpn-mgmt] } on-error={}";
        $lines[] = ":local a \"name=ovpn-mgmt connect-to={$billingPublicIp} port={$openvpnPort} protocol=tcp\"";
        $lines[] = ":local b \" user=\\\"{$mgmtUserName}\\\" password=\\\"{$radiusSecret}\\\"\"";
        $lines[] = ":local c \" certificate={$routerCertName} ca-certificate={$caCertName}\"";
        $lines[] = ":local d \" auth={$openvpnAuth} cipher={$openvpnCipher} tls-version={$openvpnTls} verify-server-certificate=yes\"";
        $lines[] = ":local e \" mode=ip use-peer-dns=no profile=ovpn-mgmt disabled=no\"";
        $lines[] = ":local full (\"/interface ovpn-client add \" . \$a . \$b . \$c . \$d . \$e)";
        $lines[] = ":execute script=\$full";
        $lines[] = "";

        // Enable REST API
        $lines[] = "/ip service set www port=80 disabled=no";
        $lines[] = "/ip service set api port=8728 disabled=no";
        $lines[] = "";

        // Wait for VPN tunnel
        $vpnWaitTimeout = (int) config('openvpn.vpn_wait_timeout', 60);
        $lines[] = ":local waited 0; :local vpnUp false; :while (\$vpnUp != true && \$waited < {$vpnWaitTimeout}) do={ :do { :set vpnUp [/interface ovpn-client get [find name=ovpn-mgmt] running] } on-error={}; :if (\$vpnUp != true) do={ :delay 1s; :set waited (\$waited + 1) } }";
        $lines[] = "";

        // 10. Host route to billing VPN IP
        $lines[] = ":do { /ip route remove [find comment=\"billing vpn route\"] } on-error={}";
        $lines[] = ":do { /ip route add dst-address={$radiusServerIp}/32 gateway=ovpn-mgmt routing-table=main comment=\"billing vpn route\" } on-error={}";
        $lines[] = "";

        // Phase 1 callback
        $lines[] = ":local vpnIp \"\"; :do { :set vpnIp [/ip address get [find interface=ovpn-mgmt] address]; :set vpnIp [:pick \$vpnIp 0 [:find \$vpnIp \"/\"]] } on-error={}";
        $lines[] = ":local cbUrl \"https://{$billingDomainS}/api/router-callback\"";
        $lines[] = ":local cbData \"router_name={$routerNameUrl}&phase=1&vpn_ip=\$vpnIp&secret={$callbackSecretUrl}\"";
        $lines[] = ":do { /tool fetch url=\$cbUrl http-method=post http-header-field=\"Content-Type: application/x-www-form-urlencoded\" http-data=\$cbData keep-result=no } on-error={}";
        $lines[] = "";

        // -----------------------------------------------------------------------
        // Heartbeat scheduler
        //
        // KEY FIX: on-event={...} uses BRACES not quotes.
        //
        // RouterOS /import does NOT support \" inside on-event="..." strings.
        // It closes the string at the first bare " — so :local v "" causes
        // "expected end of command" at exactly that " character.
        //
        // With on-event={...}:
        //   - " characters inside need NO escaping whatsoever
        //   - {} inside must be balanced (they are — verified)
        //   - This is the format /system export itself produces
        // -----------------------------------------------------------------------
        $heartbeatInterval = config('openvpn.heartbeat_interval', '00:00:01');

        $hb  = ':local v ""; ';
        $hb .= ':do { :set v [/ip address get [find interface=ovpn-mgmt] address]; ';
        $hb .= ':set v [:pick $v 0 [:find $v "/"]] } on-error={}; ';
        $hb .= ':do { /tool fetch url="https://' . $billingDomain . '/api/router-heartbeat" ';
        $hb .= 'http-method=post ';
        $hb .= 'http-header-field="Content-Type: application/x-www-form-urlencoded" ';
        $hb .= 'http-data="router_name=' . $routerNameUrl . '&vpn_ip=$v&secret=' . $callbackSecretUrl . '" ';
        $hb .= 'keep-result=no } on-error={}';

        $lines[] = ":do { /system scheduler remove [find name=billing-heartbeat] } on-error={}";
        $lines[] = '/system scheduler add name=billing-heartbeat'
            . ' interval=' . $heartbeatInterval
            . ' start-time=startup'
            . ' policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon'
            . ' on-event={' . $hb . '}';
        $lines[] = "";

        // -----------------------------------------------------------------------
        // Session sync scheduler — same brace-delimiter approach
        // -----------------------------------------------------------------------
        $syncInterval = config('openvpn.sync_interval', '00:00:01');

        $ss  = ':local pppSess ""; ';
        $ss .= ':do { :foreach s in=[/ppp active find] do={ ';
        $ss .= ':local u [/ppp active get $s name]; ';
        $ss .= ':local i [/ppp active get $s address]; ';
        $ss .= ':local t [/ppp active get $s uptime]; ';
        $ss .= ':local c [/ppp active get $s caller-id]; ';
        $ss .= ':set pppSess ($pppSess . $u . "," . $i . "," . $t . "," . $c . ";") ';
        $ss .= '} } on-error={}; ';
        $ss .= ':local hsSess ""; ';
        $ss .= ':do { :foreach s in=[/ip hotspot active find] do={ ';
        $ss .= ':local u [/ip hotspot active get $s user]; ';
        $ss .= ':local i [/ip hotspot active get $s address]; ';
        $ss .= ':local t [/ip hotspot active get $s uptime]; ';
        $ss .= ':local m [/ip hotspot active get $s mac-address]; ';
        $ss .= ':set hsSess ($hsSess . $u . "," . $i . "," . $t . "," . $m . ";") ';
        $ss .= '} } on-error={}; ';
        $ss .= ':local cpu ""; ';
        $ss .= ':do { :set cpu [/system resource get cpu-load] } on-error={}; ';
        $ss .= ':local syncData ("router_name=' . $routerNameUrl . '&secret=' . $callbackSecretUrl . '&ppp_sessions=" . $pppSess . "&hs_sessions=" . $hsSess . "&cpu=" . $cpu); ';
        $ss .= ':do { /tool fetch url="https://' . $billingDomain . '/api/router-sync" ';
        $ss .= 'http-method=post ';
        $ss .= 'http-header-field="Content-Type: application/x-www-form-urlencoded" ';
        $ss .= 'http-data=$syncData keep-result=no } on-error={}';

        $lines[] = ":do { /system scheduler remove [find name=billing-sync] } on-error={}";
        $lines[] = '/system scheduler add name=billing-sync'
            . ' interval=' . $syncInterval
            . ' start-time=startup'
            . ' policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon'
            . ' on-event={' . $ss . '}';

        return $this->buildScript($lines);
    }

    // -------------------------------------------------------------------------
    // Section 2 — PPPoE Service
    // -------------------------------------------------------------------------

    private function generateSection2(Router $router): string
    {
        $bridgeName        = $this->s($router->pppoe_bridge_name ?? 'pppoe_bridge');
        $poolRange         = $this->s($router->pppoe_pool_range  ?? '19.225.0.1-19.225.255.254');
        $gatewayIp         = $this->s($router->pppoe_gateway_ip  ?? '19.225.0.1');
        $billingDomainS    = $this->s($this->resolveBillingDomain($router));
        $callbackSecret    = config('app.router_callback_secret', '');
        $routerNameUrl     = urlencode($router->name ?? 'MikroTik');
        $callbackSecretUrl = urlencode($callbackSecret);

        $lines = [];

        $lines[] = ":do { /interface bridge remove [find name={$bridgeName}] } on-error={}";
        $lines[] = "/interface bridge add name={$bridgeName}";
        $lines[] = "";

        $lines[] = ":do { /ip pool remove [find name=pppoe_pool] } on-error={}";
        $lines[] = "/ip pool add name=pppoe_pool ranges={$poolRange}";
        $lines[] = "";

        $lines[] = ":do { /ppp profile remove [find name=pppoe-profile] } on-error={}";
        $lines[] = "/ppp profile add name=pppoe-profile dns-server=8.8.8.8,8.8.4.4 local-address={$gatewayIp} remote-address=pppoe_pool use-encryption=yes";
        $lines[] = "";

        $lines[] = ":do { /interface pppoe-server server remove [find service-name=pppoe] } on-error={}";
        $lines[] = "/interface pppoe-server server add service-name=pppoe interface={$bridgeName} authentication=pap one-session-per-host=yes keepalive-timeout=10 default-profile=pppoe-profile disabled=no";
        $lines[] = "";

        $lines[] = "/ppp aaa set use-radius=yes interim-update=00:05:50 accounting=yes";
        $lines[] = "";

        $lines[] = ":local cbUrl \"https://{$billingDomainS}/api/router-phase-complete\"";
        $lines[] = ":local cbData \"router_name={$routerNameUrl}&phase=2&secret={$callbackSecretUrl}\"";
        $lines[] = ":do { /tool fetch url=\$cbUrl http-method=post http-header-field=\"Content-Type: application/x-www-form-urlencoded\" http-data=\$cbData keep-result=no } on-error={}";

        return $this->buildScript($lines);
    }

    // -------------------------------------------------------------------------
    // Section 3 — Hotspot Service
    // -------------------------------------------------------------------------

    private function generateSection3(Router $router): string
    {
        $bridgeName        = $this->s($router->hotspot_bridge_name ?? 'hotspot_bridge');
        $poolRange         = $this->s($router->hotspot_pool_range  ?? '11.220.0.1-11.220.255.254');
        $gatewayIp         = $this->s($router->hotspot_gateway_ip  ?? '11.220.0.1');
        $prefix            = (int) ($router->hotspot_prefix        ?? 16);
        $networkAddr       = $this->networkAddress($router->hotspot_gateway_ip ?? '11.220.0.1', $prefix);
        $billingDomain     = $this->resolveBillingDomain($router);
        $billingDomainS    = $this->s($billingDomain);
        $refCode           = $this->s($router->ref_code ?? '');
        $billingVpnIp      = $this->s($this->billingVpnIp($router));
        $callbackSecret    = config('app.router_callback_secret', '');
        $routerNameUrl     = urlencode($router->name ?? 'MikroTik');
        $callbackSecretUrl = urlencode($callbackSecret);

        $lines = [];

        $lines[] = ":do { /interface bridge remove [find name={$bridgeName}] } on-error={}";
        $lines[] = "/interface bridge add name={$bridgeName}";
        $lines[] = "";

        $lines[] = ":do { /ip address remove [find address=\"{$gatewayIp}/{$prefix}\"] } on-error={}";
        $lines[] = "/ip address add address={$gatewayIp}/{$prefix} interface={$bridgeName}";
        $lines[] = "";

        $lines[] = ":do { /ip pool remove [find name=hs_pool] } on-error={}";
        $lines[] = "/ip pool add name=hs_pool ranges={$poolRange}";
        $lines[] = "";

        $lines[] = ":do { /ip dhcp-server remove [find name=hs-dhcp] } on-error={}";
        $lines[] = "/ip dhcp-server add name=hs-dhcp interface={$bridgeName} address-pool=hs_pool lease-time=1d-00:10:00 disabled=no";
        $lines[] = ":do { /ip dhcp-server network remove [find address=\"{$networkAddr}/{$prefix}\"] } on-error={}";
        $lines[] = "/ip dhcp-server network add address={$networkAddr}/{$prefix} gateway={$gatewayIp} dns-server=8.8.8.8,8.8.4.4";
        $lines[] = "";

        $lines[] = ":do { /ip hotspot profile remove [find name=hs-profile] } on-error={}";
        $lines[] = "/ip hotspot profile add name=hs-profile dns-name={$billingDomainS} hotspot-address={$gatewayIp} login-by=cookie,https,http-pap,mac-cookie use-radius=yes radius-interim-update=00:06:30 http-cookie-lifetime=3d";
        $lines[] = "";

        $lines[] = ":do { /ip hotspot remove [find name=hs-server] } on-error={}";
        $lines[] = "/ip hotspot add name=hs-server interface={$bridgeName} address-pool=hs_pool profile=hs-profile addresses-per-mac=3 idle-timeout=1m disabled=no";
        $lines[] = "";

        $lines[] = "/ip hotspot user profile set [find name=default] keepalive-timeout=00:02:00 status-autorefresh=1m shared-users=1 add-mac-cookie=yes mac-cookie-timeout=3d transparent-proxy=yes open-status-page=always";
        $lines[] = "";

        $lines[] = ":delay 5s";
        foreach (['login','alogin','status','logout','redirect','error','flogin','fstatus','flogout','rlogin','rstatus','radvert'] as $page) {
            $lines[] = ":execute script=\"/tool fetch url=\\\"https://{$billingDomainS}/api/router-hotspot/{$refCode}/{$page}.html\\\" dst-path=hotspot/{$page}.html\"";
            $lines[] = ":delay 2s";
        }
        foreach (['md5.js','errors.txt','api.json'] as $asset) {
            $lines[] = ":execute script=\"/tool fetch url=\\\"https://{$billingDomainS}/api/router-hotspot/{$refCode}/{$asset}\\\" dst-path=hotspot/{$asset}\"";
            $lines[] = ":delay 2s";
        }
        $lines[] = "";

        $lines[] = ":do { /ip hotspot walled-garden ip remove [find dst-address={$billingVpnIp}] } on-error={}";
        $lines[] = "/ip hotspot walled-garden ip add dst-address={$billingVpnIp} action=accept";
        $lines[] = ":do { /ip hotspot walled-garden remove [find dst-host=\"{$billingDomainS}\"] } on-error={}";
        $lines[] = "/ip hotspot walled-garden add dst-host=\"{$billingDomainS}\" action=allow";
        $lines[] = "";

        $lines[] = "/ip dns set allow-remote-requests=yes";
        $lines[] = "";

        $lines[] = ":do { /ip firewall nat remove [find comment=\"masquerade hotspot network\"] } on-error={}";
        $lines[] = "/ip firewall nat add chain=srcnat action=masquerade src-address={$networkAddr}/{$prefix} comment=\"masquerade hotspot network\"";
        $lines[] = "";

        $lines[] = ":local cbUrl \"https://{$billingDomainS}/api/router-phase-complete\"";
        $lines[] = ":local cbData \"router_name={$routerNameUrl}&phase=3&secret={$callbackSecretUrl}\"";
        $lines[] = ":do { /tool fetch url=\$cbUrl http-method=post http-header-field=\"Content-Type: application/x-www-form-urlencoded\" http-data=\$cbData keep-result=no } on-error={}";

        return $this->buildScript($lines);
    }

    // -------------------------------------------------------------------------
    // Section 4 — Combined Mode (PPPoE + Hotspot on single bridge)
    // -------------------------------------------------------------------------

    private function generateSection4(Router $router): string
    {
        $bridgeName        = 'PPP_HOTSPOT';
        $pppoePoolRange    = $this->s($router->pppoe_pool_range   ?? '19.225.0.1-19.225.255.254');
        $pppoeGateway      = $this->s($router->pppoe_gateway_ip   ?? '19.225.0.1');
        $hsPoolRange       = $this->s($router->hotspot_pool_range ?? '11.220.0.1-11.220.255.254');
        $hsGateway         = $this->s($router->hotspot_gateway_ip ?? '11.220.0.1');
        $hsPrefix          = (int) ($router->hotspot_prefix       ?? 16);
        $networkAddr       = $this->networkAddress($router->hotspot_gateway_ip ?? '11.220.0.1', $hsPrefix);
        $billingDomain     = $this->resolveBillingDomain($router);
        $billingDomainS    = $this->s($billingDomain);
        $refCode           = $this->s($router->ref_code ?? '');
        $billingVpnIp      = $this->s($this->billingVpnIp($router));
        $callbackSecret    = config('app.router_callback_secret', '');
        $routerNameUrl     = urlencode($router->name ?? 'MikroTik');
        $callbackSecretUrl = urlencode($callbackSecret);

        $lines = [];

        $lines[] = ":do { /interface bridge remove [find name={$bridgeName}] } on-error={}";
        $lines[] = "/interface bridge add name={$bridgeName}";
        $lines[] = "";

        $lines[] = ":do { /ip address remove [find address=\"{$pppoeGateway}/16\"] } on-error={}";
        $lines[] = "/ip address add address={$pppoeGateway}/16 interface={$bridgeName}";
        $lines[] = ":do { /ip address remove [find address=\"{$hsGateway}/{$hsPrefix}\"] } on-error={}";
        $lines[] = "/ip address add address={$hsGateway}/{$hsPrefix} interface={$bridgeName}";
        $lines[] = "";

        $lines[] = ":do { /ip pool remove [find name=pppoe_pool] } on-error={}";
        $lines[] = "/ip pool add name=pppoe_pool ranges={$pppoePoolRange}";
        $lines[] = "";

        $lines[] = ":do { /ppp profile remove [find name=pppoe-profile] } on-error={}";
        $lines[] = "/ppp profile add name=pppoe-profile dns-server=8.8.8.8,8.8.4.4 local-address={$pppoeGateway} remote-address=pppoe_pool use-encryption=yes";
        $lines[] = "";

        $lines[] = ":do { /interface pppoe-server server remove [find service-name=pppoe] } on-error={}";
        $lines[] = "/interface pppoe-server server add service-name=pppoe interface={$bridgeName} authentication=pap one-session-per-host=yes keepalive-timeout=10 default-profile=pppoe-profile disabled=no";
        $lines[] = "";

        $lines[] = "/ppp aaa set use-radius=yes interim-update=00:05:50 accounting=yes";
        $lines[] = "";

        $lines[] = ":do { /ip pool remove [find name=hs_pool] } on-error={}";
        $lines[] = "/ip pool add name=hs_pool ranges={$hsPoolRange}";
        $lines[] = "";

        $lines[] = ":do { /ip dhcp-server remove [find name=hs-dhcp] } on-error={}";
        $lines[] = "/ip dhcp-server add name=hs-dhcp interface={$bridgeName} address-pool=hs_pool lease-time=1d-00:10:00 disabled=no";
        $lines[] = ":do { /ip dhcp-server network remove [find address=\"{$networkAddr}/{$hsPrefix}\"] } on-error={}";
        $lines[] = "/ip dhcp-server network add address={$networkAddr}/{$hsPrefix} gateway={$hsGateway} dns-server=8.8.8.8,8.8.4.4";
        $lines[] = "";

        $lines[] = ":do { /ip hotspot profile remove [find name=hs-profile] } on-error={}";
        $lines[] = "/ip hotspot profile add name=hs-profile dns-name={$billingDomainS} hotspot-address={$hsGateway} login-by=cookie,https,http-pap,mac-cookie use-radius=yes radius-interim-update=00:06:30 http-cookie-lifetime=3d";
        $lines[] = "";

        $lines[] = ":do { /ip hotspot remove [find name=hs-server] } on-error={}";
        $lines[] = "/ip hotspot add name=hs-server interface={$bridgeName} address-pool=hs_pool profile=hs-profile addresses-per-mac=3 idle-timeout=1m disabled=no";
        $lines[] = "";

        $lines[] = "/ip hotspot user profile set [find name=default] keepalive-timeout=00:02:00 status-autorefresh=1m shared-users=1 add-mac-cookie=yes mac-cookie-timeout=3d transparent-proxy=yes open-status-page=always";
        $lines[] = "";

        $lines[] = ":delay 5s";
        foreach (['login','alogin','status','logout','redirect','error','flogin','fstatus','flogout','rlogin','rstatus','radvert'] as $page) {
            $lines[] = ":execute script=\"/tool fetch url=\\\"https://{$billingDomainS}/api/router-hotspot/{$refCode}/{$page}.html\\\" dst-path=hotspot/{$page}.html\"";
            $lines[] = ":delay 2s";
        }
        foreach (['md5.js','errors.txt','api.json'] as $asset) {
            $lines[] = ":execute script=\"/tool fetch url=\\\"https://{$billingDomainS}/api/router-hotspot/{$refCode}/{$asset}\\\" dst-path=hotspot/{$asset}\"";
            $lines[] = ":delay 2s";
        }
        $lines[] = "";

        $lines[] = ":do { /ip hotspot walled-garden ip remove [find dst-address={$billingVpnIp}] } on-error={}";
        $lines[] = "/ip hotspot walled-garden ip add dst-address={$billingVpnIp} action=accept";
        $lines[] = ":do { /ip hotspot walled-garden remove [find dst-host=\"{$billingDomainS}\"] } on-error={}";
        $lines[] = "/ip hotspot walled-garden add dst-host=\"{$billingDomainS}\" action=allow";
        $lines[] = "";

        $lines[] = "/ip dns set allow-remote-requests=yes";
        $lines[] = "";

        $lines[] = ":do { /ip firewall nat remove [find comment=\"masquerade hotspot network\"] } on-error={}";
        $lines[] = "/ip firewall nat add chain=srcnat action=masquerade src-address={$networkAddr}/{$hsPrefix} comment=\"masquerade hotspot network\"";
        $lines[] = "";

        $lines[] = ":local cbUrl \"https://{$billingDomainS}/api/router-phase-complete\"";
        $lines[] = ":local cbData \"router_name={$routerNameUrl}&phase=3&secret={$callbackSecretUrl}\"";
        $lines[] = ":do { /tool fetch url=\$cbUrl http-method=post http-header-field=\"Content-Type: application/x-www-form-urlencoded\" http-data=\$cbData keep-result=no } on-error={}";

        return $this->buildScript($lines);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Sanitize a value for safe embedding inside a RouterOS double-quoted string.
     * Strips newlines and escapes backslashes and double-quotes.
     *
     * Do NOT use this for values placed inside brace on-event={...} blocks —
     * those do not require any quote escaping.
     */
    private function s(string $value): string
    {
        $value = str_replace(["\r\n", "\r", "\n"], '', $value);
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace('"', '\\"', $value);
        return $value;
    }

    /**
     * Resolve the billing domain from the router model or app config.
     * Always returns a bare domain with no protocol prefix and no trailing slash.
     * Example: "https://billing.example.com/" → "billing.example.com"
     *
     * Used raw (without s()) inside brace on-event={} blocks.
     * Pass through s() when embedding in a double-quoted ROS string.
     */
    private function resolveBillingDomain(Router $router): string
    {
        $domain = $router->billing_domain ?? $this->stripProtocol(config('app.url', ''));
        return $this->stripProtocol($domain);
    }

    /**
     * Resolve billing server VPN IP.
     */
    private function billingVpnIp(Router $router): string
    {
        return $router->billing_server_vpn_ip
            ?: config('openvpn.billing_server_vpn_ip', '10.8.0.1');
    }

    /**
     * Resolve billing server public IP.
     */
    private function billingPublicIp(Router $router): string
    {
        return $router->billing_server_public_ip
            ?: config('openvpn.billing_server_public_ip', '89.117.52.63');
    }

    /**
     * Resolve billing server VPN subnet.
     */
    private function billingSubnet(Router $router): string
    {
        return $router->billing_server_subnet
            ?: config('openvpn.billing_server_subnet', '10.8.0.0/24');
    }

    /**
     * Derive key filename from cert filename.
     * "router.crt" → "router.key"
     */
    private function deriveKeyFilename(string $certFilename): string
    {
        $base = pathinfo($certFilename, PATHINFO_FILENAME);
        return $base . '.key';
    }

    /**
     * Calculate network address for a given IP and prefix.
     * ('11.220.0.1', 16) → '11.220.0.0'
     */
    private function networkAddress(string $ip, int $prefix): string
    {
        $ipLong   = ip2long($ip);
        $maskLong = ~((1 << (32 - $prefix)) - 1) & 0xFFFFFFFF;
        return long2ip($ipLong & $maskLong);
    }

    /**
     * Strip protocol prefix and trailing slashes.
     * "https://billing.example.com/" → "billing.example.com"
     */
    private function stripProtocol(string $url): string
    {
        $url = preg_replace('#^https?://#i', '', $url);
        return rtrim($url, '/');
    }

    /**
     * Join lines, collapse excess blank lines, trim.
     */
    private function buildScript(array $lines): string
    {
        $script = implode("\n", $lines);
        $script = preg_replace('/\n{3,}/', "\n\n", $script);
        return trim($script);
    }
}