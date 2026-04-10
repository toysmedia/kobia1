@extends('layouts.super-admin')
@section('title', 'Tenant Map')
@section('page-title', 'Tenant Map')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" crossorigin="anonymous">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" crossorigin="anonymous">
<style>
    #tenantMap { height: calc(100vh - 220px); min-height: 480px; border-radius: 0.5rem; }
    .leaflet-popup-content { font-size: 13px; min-width: 220px; }
    .tenant-popup-status { display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 600; color: #fff; }
    .tenant-popup-stat { display: flex; justify-content: space-between; margin-top: 4px; font-size: 12px; }
    .tenant-popup-stat span:first-child { color: #6c757d; }
    /* Dark mode popup */
    [data-bs-theme="dark"] .leaflet-popup-content-wrapper,
    [data-bs-theme="dark"] .leaflet-popup-tip { background: #232639; color: #e4e6f0; }
    [data-bs-theme="dark"] .tenant-popup-stat span:first-child { color: #8892a4; }
    [data-bs-theme="dark"] .leaflet-control-zoom a { background: #232639; color: #e4e6f0; border-color: #2e3248; }
    [data-bs-theme="dark"] .leaflet-control-zoom a:hover { background: #2f3248; }
    /* Map layer control */
    .map-layer-control { position: absolute; top: 10px; right: 10px; z-index: 1000; }
    .map-layer-btn { padding: 4px 12px; font-size: 12px; }
    @media (max-width: 575.98px) {
        #tenantMap { height: 60vw !important; min-height: 300px !important; }
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="mb-0 fw-bold">Tenant Map</h5>
        <small class="text-muted">{{ $tenants->count() }} tenant(s) plotted with coordinates</small>
    </div>
    <div class="d-flex gap-2">
        <div class="btn-group btn-group-sm" id="layerSwitcher">
            <button class="btn btn-outline-secondary active" data-layer="streets">Streets</button>
            <button class="btn btn-outline-secondary" data-layer="satellite">Satellite</button>
            <button class="btn btn-outline-secondary" data-layer="dark">Dark</button>
        </div>
        <button class="btn btn-outline-secondary btn-sm" id="fullscreenBtn" title="Toggle fullscreen">
            <i class="bi bi-fullscreen"></i>
        </button>
        <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-list-ul me-1"></i>View List
        </a>
    </div>
</div>

@if($tenants->isEmpty())
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-geo-alt fs-1 d-block mb-3 opacity-25"></i>
        <p class="mb-1 fw-semibold">No tenants with coordinates yet.</p>
        <p class="small mb-0">Set <code>lat</code> and <code>lng</code> when editing a tenant to display them on the map.</p>
        <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-primary btn-sm mt-3">Manage Tenants</a>
    </div>
</div>
@else
<div class="card border-0 shadow-sm position-relative">
    <div class="card-body p-2">
        <div id="tenantMap"></div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/WPeE=" crossorigin="anonymous"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js" crossorigin="anonymous"></script>
<script>
(function () {
    const tenants = @json($tenants);
    const tenantBaseUrl = '{{ route('super-admin.tenants.index') }}'.replace(/\/+$/, '');

    if (!tenants.length) return;

    // Detect current theme
    function isDarkMode() {
        return document.documentElement.getAttribute('data-bs-theme') === 'dark';
    }

    // Tile layer definitions
    const tileLayers = {
        streets: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 18
        }),
        satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: '© Esri — Source: Esri, USGS, NOAA',
            maxZoom: 18
        }),
        dark: L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> © <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 19
        }),
    };

    const map = L.map('tenantMap', { zoomControl: true });

    // Apply initial tile layer based on theme
    let currentLayer = isDarkMode() ? 'dark' : 'streets';
    let userPickedLayer = false; // tracks whether user manually selected a layer
    tileLayers[currentLayer].addTo(map);

    // Marker cluster group
    const clusterGroup = L.markerClusterGroup({
        maxClusterRadius: 50,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true,
    });

    const statusColors = {
        active:    '#198754',
        suspended: '#ffc107',
        expired:   '#dc3545',
        trial:     '#0dcaf0',
    };

    tenants.forEach(function (tenant) {
        const lat = parseFloat(tenant.lat);
        const lng = parseFloat(tenant.lng);
        if (isNaN(lat) || isNaN(lng)) return;

        const statusColor = statusColors[tenant.status] || '#6c757d';

        const icon = L.divIcon({
            className: '',
            html: `<div style="width:16px;height:16px;background:${statusColor};border-radius:50%;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.4);"></div>`,
            iconSize: [16, 16],
            iconAnchor: [8, 8],
            popupAnchor: [0, -10]
        });

        const subscribers = tenant.subscribers_count ?? '—';
        const plan        = tenant.plan ?? '—';
        const revenue     = tenant.total_revenue ? 'KES ' + Number(tenant.total_revenue).toLocaleString() : '—';

        const popupHtml = `
            <strong style="font-size:14px;">${tenant.name}</strong><br>
            <code style="font-size:11px;">${tenant.subdomain}</code>
            <div style="margin:6px 0;">
                <span class="tenant-popup-status" style="background:${statusColor};">${tenant.status}</span>
            </div>
            <div class="tenant-popup-stat"><span>Subscribers</span><strong>${subscribers}</strong></div>
            <div class="tenant-popup-stat"><span>Plan</span><strong>${plan}</strong></div>
            <div class="tenant-popup-stat"><span>Revenue</span><strong>${revenue}</strong></div>
            <div style="margin-top:8px;">
                <a href="${tenantBaseUrl}/${tenant.id}" target="_blank" style="font-size:12px;">View Details →</a>
            </div>
        `;

        const marker = L.marker([lat, lng], { icon }).bindPopup(popupHtml, { maxWidth: 280 });
        clusterGroup.addLayer(marker);
    });

    map.addLayer(clusterGroup);

    // Fit bounds
    const markers = clusterGroup.getLayers();
    if (markers.length) {
        const group = L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.2));
        if (markers.length === 1) map.setZoom(10);
    }

    // Legend
    const legend = L.control({ position: 'bottomright' });
    legend.onAdd = function () {
        const div = L.DomUtil.create('div', '');
        div.style.cssText = 'padding:8px 12px;border-radius:8px;font-size:12px;line-height:1.8;';
        div.id = 'map-legend';
        updateLegendStyle(div);
        div.innerHTML = `
            <div style="font-weight:600;margin-bottom:4px;">Status</div>
            ${Object.entries(statusColors).map(([status, color]) => `
                <div><span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:${color};margin-right:6px;vertical-align:middle;"></span>${status.charAt(0).toUpperCase() + status.slice(1)}</div>
            `).join('')}
        `;
        return div;
    };
    legend.addTo(map);

    function updateLegendStyle(div) {
        const dark = isDarkMode();
        div.style.background  = dark ? '#232639' : '#fff';
        div.style.color       = dark ? '#e4e6f0' : '#212529';
        div.style.boxShadow   = dark ? '0 1px 6px rgba(0,0,0,.5)' : '0 1px 6px rgba(0,0,0,.2)';
        div.style.border      = dark ? '1px solid #2e3248' : '1px solid #dee2e6';
    }

    // Layer switcher
    document.querySelectorAll('#layerSwitcher [data-layer]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const layer = this.dataset.layer;
            if (layer === currentLayer) return;
            map.removeLayer(tileLayers[currentLayer]);
            tileLayers[layer].addTo(map);
            currentLayer = layer;
            userPickedLayer = true; // user manually selected
            document.querySelectorAll('#layerSwitcher [data-layer]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Fullscreen toggle
    document.getElementById('fullscreenBtn')?.addEventListener('click', function () {
        const card = document.querySelector('.card.position-relative');
        if (!document.fullscreenElement) {
            card?.requestFullscreen();
            this.innerHTML = '<i class="bi bi-fullscreen-exit"></i>';
        } else {
            document.exitFullscreen();
            this.innerHTML = '<i class="bi bi-fullscreen"></i>';
        }
        setTimeout(() => map.invalidateSize(), 200);
    });

    // Auto-switch tile layer when theme changes (only if user hasn't manually picked a layer)
    const observer = new MutationObserver(function () {
        const legendDiv = document.getElementById('map-legend');
        if (legendDiv) updateLegendStyle(legendDiv);

        if (!userPickedLayer) {
            const themePref = isDarkMode() ? 'dark' : 'streets';
            if (themePref !== currentLayer) {
                map.removeLayer(tileLayers[currentLayer]);
                tileLayers[themePref].addTo(map);
                currentLayer = themePref;
                document.querySelectorAll('#layerSwitcher [data-layer]').forEach(b => {
                    b.classList.toggle('active', b.dataset.layer === themePref);
                });
            }
        }
    });

    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
})();
</script>
@endpush
