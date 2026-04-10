@extends('admin.layouts.app')
@section('title', 'Maps')
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
#map { height: calc(100vh - 220px); min-height: 450px; z-index: 1; }
.map-sidebar { height: calc(100vh - 220px); overflow-y: auto; }
.legend-item { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; cursor: pointer; }
.legend-dot { width: 14px; height: 14px; border-radius: 50%; flex-shrink: 0; }
</style>
@endpush
@section('content')
<div class="row">
    <div class="col-sm-12 mb-3">
        <h5 class="mb-0">Network Map</h5>
    </div>

    <div class="col-md-3">
        <div class="card map-sidebar">
            <div class="card-header"><h6 class="mb-0">Map Controls</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <input type="text" id="mapSearch" class="form-control form-control-sm" placeholder="Search locations...">
                </div>
                <h6 class="small text-muted mb-2">FILTER LAYERS</h6>
                <div class="legend-item" onclick="toggleLayer('router')"><div class="legend-dot" style="background:#28a745"></div> <span>Routers</span></div>
                <div class="legend-item" onclick="toggleLayer('subscriber_pppoe')"><div class="legend-dot" style="background:#007bff"></div> <span>PPPoE Subscribers</span></div>
                <div class="legend-item" onclick="toggleLayer('subscriber_hotspot')"><div class="legend-dot" style="background:#fd7e14"></div> <span>Hotspot Subscribers</span></div>
                <div class="legend-item" onclick="toggleLayer('tower')"><div class="legend-dot" style="background:#dc3545"></div> <span>Towers</span></div>
                <div class="legend-item" onclick="toggleLayer('other')"><div class="legend-dot" style="background:#6c757d"></div> <span>Other</span></div>

                <hr>
                <button class="btn btn-sm btn-primary w-100 mb-2" id="addLocationBtn">
                    <i class="bx bx-map-pin me-1"></i> Add Location
                </button>
                <p class="small text-muted" id="addLocationHint" style="display:none">Click on the map to place a marker</p>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card">
            <div class="card-body p-0">
                <div id="map"></div>
            </div>
        </div>
    </div>
</div>

{{-- Add Location Modal --}}
<div class="modal fade" id="addLocModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title">Add Location</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Name</label><input type="text" id="locName" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Type</label>
                    <select id="locType" class="form-select">
                        <option value="tower">Tower</option>
                        <option value="cabinet">Cabinet</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Description</label><textarea id="locDesc" class="form-control" rows="2"></textarea></div>
                <input type="hidden" id="locLat"> <input type="hidden" id="locLng">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveLocBtn">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var map = L.map('map').setView([-1.286389, 36.817223], 7);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19
}).addTo(map);

var layerGroups = {};
var markerColors = {
    router: '#28a745', subscriber_pppoe: '#007bff', subscriber_hotspot: '#fd7e14',
    tower: '#dc3545', cabinet: '#6c757d', other: '#6c757d'
};

function makeIcon(color) {
    return L.divIcon({
        html: `<div style="background:${color};width:14px;height:14px;border-radius:50%;border:2px solid #fff;box-shadow:0 1px 3px rgba(0,0,0,.4)"></div>`,
        iconSize: [14, 14], iconAnchor: [7, 7], popupAnchor: [0, -7], className: ''
    });
}

fetch('{{ route("admin.isp.maps.data") }}')
    .then(r => r.json())
    .then(data => {
        data.forEach(item => {
            if (!layerGroups[item.type]) {
                layerGroups[item.type] = L.layerGroup().addTo(map);
            }
            var color = markerColors[item.type] || '#6c757d';
            var marker = L.marker([item.latitude, item.longitude], { icon: makeIcon(color), draggable: true })
                .bindPopup(`<strong>${item.name}</strong><br><small>${item.type}</small><br>${item.description || ''}`)
                .addTo(layerGroups[item.type]);

            if (item.id && item.id.startsWith('loc_')) {
                var locId = item.id.replace('loc_', '');
                marker.on('dragend', function(e) {
                    var ll = e.target.getLatLng();
                    fetch('{{ route("admin.isp.maps.locations.update", ":id") }}'.replace(':id', locId), {
                        method: 'PUT',
                        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                        body: JSON.stringify({ latitude: ll.lat, longitude: ll.lng })
                    });
                });
            }
        });
    });

function toggleLayer(type) {
    if (layerGroups[type]) {
        if (map.hasLayer(layerGroups[type])) map.removeLayer(layerGroups[type]);
        else map.addLayer(layerGroups[type]);
    }
}

var addMode = false;
document.getElementById('addLocationBtn').addEventListener('click', function() {
    addMode = !addMode;
    document.getElementById('addLocationHint').style.display = addMode ? '' : 'none';
    this.textContent = addMode ? 'Cancel Add Mode' : '+ Add Location';
    this.classList.toggle('btn-danger', addMode);
    this.classList.toggle('btn-primary', !addMode);
});

map.on('click', function(e) {
    if (!addMode) return;
    document.getElementById('locLat').value = e.latlng.lat;
    document.getElementById('locLng').value = e.latlng.lng;
    new bootstrap.Modal(document.getElementById('addLocModal')).show();
});

document.getElementById('saveLocBtn').addEventListener('click', function() {
    var data = {
        name: document.getElementById('locName').value,
        type: document.getElementById('locType').value,
        description: document.getElementById('locDesc').value,
        latitude: parseFloat(document.getElementById('locLat').value),
        longitude: parseFloat(document.getElementById('locLng').value),
    };
    fetch('{{ route("admin.isp.maps.locations.store") }}', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body: JSON.stringify(data)
    }).then(r => r.json()).then(res => {
        if (res.success) { location.reload(); }
    });
});

// Context menu
map.on('contextmenu', function(e) {
    document.getElementById('locLat').value = e.latlng.lat;
    document.getElementById('locLng').value = e.latlng.lng;
    new bootstrap.Modal(document.getElementById('addLocModal')).show();
});
</script>
@endpush
