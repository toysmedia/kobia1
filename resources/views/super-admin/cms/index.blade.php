@extends('layouts.super-admin')
@section('title', 'CMS Editor')
@section('page-title', 'CMS Content Editor')

@section('content')
{{-- Flash Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-0 fw-bold">CMS Editor</h5>
        <small class="text-muted">Manage guest/public home page content</small>
    </div>
</div>

<form method="POST" action="{{ route('super-admin.cms.update') }}">
    @csrf
    @method('PUT')

    {{-- Tab Navigation --}}
    <ul class="nav nav-tabs mb-4" id="cmsTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#heroTab" type="button"><i class="bi bi-image me-1"></i>Hero</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#featuresTab" type="button"><i class="bi bi-stars me-1"></i>Features</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#pricingTab" type="button"><i class="bi bi-tags me-1"></i>Pricing</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#contactTab" type="button"><i class="bi bi-envelope me-1"></i>Contact</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#footerTab" type="button"><i class="bi bi-layout-text-window me-1"></i>Footer</button></li>
    </ul>

    <div class="tab-content">

        {{-- Hero Section --}}
        <div class="tab-pane fade show active" id="heroTab">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-image me-2 text-primary"></i>Hero Section</h6>
                </div>
                <div class="card-body">
                    @php $hero = $sections['hero'] ?? []; @endphp
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Headline</label>
                            <input type="text" name="sections[hero][title]" value="{{ $hero['title'] ?? '' }}" class="form-control" placeholder="Kenya's #1 ISP Management Platform">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sub-headline</label>
                            <input type="text" name="sections[hero][subtitle]" value="{{ $hero['subtitle'] ?? '' }}" class="form-control" placeholder="Manage your ISP business with ease…">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">CTA Button Text</label>
                            <input type="text" name="sections[hero][cta_text]" value="{{ $hero['cta_text'] ?? '' }}" class="form-control" placeholder="Start Free Trial">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">CTA Button URL</label>
                            <input type="text" name="sections[hero][cta_url]" value="{{ $hero['cta_url'] ?? '' }}" class="form-control" placeholder="/register">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Background Image URL</label>
                            <input type="text" name="sections[hero][bg_image]" value="{{ $hero['bg_image'] ?? '' }}" class="form-control" placeholder="https://…">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">WhatsApp Number</label>
                            <input type="text" name="sections[hero][whatsapp_number]" value="{{ $hero['whatsapp_number'] ?? '' }}" class="form-control" placeholder="+254712345678">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Schedule Meeting URL</label>
                            <input type="text" name="sections[hero][meeting_url]" value="{{ $hero['meeting_url'] ?? '' }}" class="form-control" placeholder="https://calendly.com/…">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Trial Offer Days</label>
                            <input type="number" name="sections[hero][trial_days]" value="{{ $hero['trial_days'] ?? 14 }}" class="form-control" min="0">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Features Section --}}
        <div class="tab-pane fade" id="featuresTab">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-stars me-2 text-primary"></i>Features Section</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addFeatureItem">
                        <i class="bi bi-plus me-1"></i>Add Feature
                    </button>
                </div>
                <div class="card-body">
                    @php $features = $sections['features'] ?? []; @endphp
                    <div id="featuresContainer">
                        @foreach((array)$features as $i => $feature)
                        @if(is_array($feature))
                        <div class="feature-item border rounded p-3 mb-3">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-2">
                                    <label class="form-label small text-muted">Icon Class</label>
                                    <input type="text" name="sections[features][{{ $i }}][icon]" value="{{ $feature['icon'] ?? '' }}" class="form-control form-control-sm" placeholder="bi bi-wifi">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">Title</label>
                                    <input type="text" name="sections[features][{{ $i }}][title]" value="{{ $feature['title'] ?? '' }}" class="form-control form-control-sm" placeholder="Feature title">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Description</label>
                                    <input type="text" name="sections[features][{{ $i }}][description]" value="{{ $feature['description'] ?? '' }}" class="form-control form-control-sm" placeholder="Short description…">
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-item w-100"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Pricing Section --}}
        <div class="tab-pane fade" id="pricingTab">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-tags me-2 text-primary"></i>Pricing Section</h6>
                </div>
                <div class="card-body">
                    @php $pricing = $sections['pricing'] ?? []; @endphp
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="sections[pricing][title]" value="{{ $pricing['title'] ?? 'Choose Your Plan' }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Section Subtitle</label>
                            <input type="text" name="sections[pricing][subtitle]" value="{{ $pricing['subtitle'] ?? '' }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="sections[pricing][show]" value="1" id="showPricing" @checked(($pricing['show'] ?? true) == '1' || ($pricing['show'] ?? true) === true)>
                                <label class="form-check-label" for="showPricing">Show Pricing Section on Homepage</label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 p-3 bg-light rounded">
                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Pricing cards are pulled automatically from active pricing plans. Manage plans under <a href="{{ route('super-admin.pricing-plans.index') }}">Pricing Plans</a>.</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact Section --}}
        <div class="tab-pane fade" id="contactTab">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-envelope me-2 text-primary"></i>Contact Section</h6>
                </div>
                <div class="card-body">
                    @php $contact = $sections['contact'] ?? []; @endphp
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Contact Email</label>
                            <input type="email" name="sections[contact][email]" value="{{ $contact['email'] ?? '' }}" class="form-control" placeholder="hello@oxnet.co.ke">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Phone</label>
                            <input type="text" name="sections[contact][phone]" value="{{ $contact['phone'] ?? '' }}" class="form-control" placeholder="+254712345678">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Physical Address</label>
                            <input type="text" name="sections[contact][address]" value="{{ $contact['address'] ?? '' }}" class="form-control" placeholder="Nairobi, Kenya">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Support Hours</label>
                            <input type="text" name="sections[contact][hours]" value="{{ $contact['hours'] ?? '' }}" class="form-control" placeholder="Mon–Fri 8am–6pm">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer Section --}}
        <div class="tab-pane fade" id="footerTab">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-layout-text-window me-2 text-primary"></i>Footer & Theme</h6>
                </div>
                <div class="card-body">
                    @php $footer = $sections['footer'] ?? []; @endphp
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Copyright Text</label>
                            <input type="text" name="sections[footer][copyright]" value="{{ $footer['copyright'] ?? '© ' . date('Y') . ' OxNet. All rights reserved.' }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Social — Twitter URL</label>
                            <input type="text" name="sections[footer][twitter]" value="{{ $footer['twitter'] ?? '' }}" class="form-control" placeholder="https://twitter.com/…">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Social — Facebook URL</label>
                            <input type="text" name="sections[footer][facebook]" value="{{ $footer['facebook'] ?? '' }}" class="form-control" placeholder="https://facebook.com/…">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Social — LinkedIn URL</label>
                            <input type="text" name="sections[footer][linkedin]" value="{{ $footer['linkedin'] ?? '' }}" class="form-control" placeholder="https://linkedin.com/…">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Theme Primary Color</label>
                            <div class="input-group">
                                <input type="color" name="sections[footer][color_primary]" value="{{ $footer['color_primary'] ?? '#0d6efd' }}" class="form-control form-control-color">
                                <input type="text" value="{{ $footer['color_primary'] ?? '#0d6efd' }}" class="form-control form-control-sm" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Theme Secondary Color</label>
                            <div class="input-group">
                                <input type="color" name="sections[footer][color_secondary]" value="{{ $footer['color_secondary'] ?? '#6c757d' }}" class="form-control form-control-color">
                                <input type="text" value="{{ $footer['color_secondary'] ?? '#6c757d' }}" class="form-control form-control-sm" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Theme Accent Color</label>
                            <div class="input-group">
                                <input type="color" name="sections[footer][color_accent]" value="{{ $footer['color_accent'] ?? '#198754' }}" class="form-control form-control-color">
                                <input type="text" value="{{ $footer['color_accent'] ?? '#198754' }}" class="form-control form-control-sm" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Save --}}
    <div class="d-flex justify-content-end mt-4">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check-lg me-1"></i>Save All Changes
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
let featureItemIndex = {{ count((array)($sections['features'] ?? [])) }};

document.getElementById('addFeatureItem').addEventListener('click', function () {
    const container = document.getElementById('featuresContainer');
    const div = document.createElement('div');
    div.className = 'feature-item border rounded p-3 mb-3';
    div.innerHTML = `
        <div class="row g-2 align-items-center">
            <div class="col-md-2">
                <label class="form-label small text-muted">Icon Class</label>
                <input type="text" name="sections[features][${featureItemIndex}][icon]" class="form-control form-control-sm" placeholder="bi bi-wifi">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Title</label>
                <input type="text" name="sections[features][${featureItemIndex}][title]" class="form-control form-control-sm" placeholder="Feature title">
            </div>
            <div class="col-md-6">
                <label class="form-label small text-muted">Description</label>
                <input type="text" name="sections[features][${featureItemIndex}][description]" class="form-control form-control-sm" placeholder="Short description…">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-sm btn-outline-danger remove-item w-100"><i class="bi bi-trash"></i></button>
            </div>
        </div>
    `;
    container.appendChild(div);
    featureItemIndex++;
    bindRemove();
});

function bindRemove() {
    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.onclick = () => btn.closest('.feature-item').remove();
    });
}

// Sync color inputs
document.querySelectorAll('input[type="color"]').forEach(colorInput => {
    colorInput.addEventListener('input', function () {
        this.nextElementSibling.value = this.value;
    });
});

bindRemove();
</script>
@endpush
