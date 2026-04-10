@extends('layouts.super-admin')
@section('title', 'Create Pricing Plan')
@section('page-title', 'Create Pricing Plan')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('super-admin.pricing-plans.index') }}">Pricing Plans</a></li>
        <li class="breadcrumb-item active">Create</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-plus-circle me-2 text-primary"></i>New Pricing Plan</h6>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('super-admin.pricing-plans.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Plan Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Starter, Professional, Enterprise…" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Price (KES) <span class="text-danger">*</span></label>
                            <input type="number" name="price" value="{{ old('price', 0) }}" class="form-control @error('price') is-invalid @enderror" min="0" step="0.01" required>
                            @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Billing Cycle <span class="text-danger">*</span></label>
                            <select name="billing_cycle" class="form-select @error('billing_cycle') is-invalid @enderror" required>
                                <option value="monthly" @selected(old('billing_cycle') === 'monthly')>Monthly</option>
                                <option value="quarterly" @selected(old('billing_cycle') === 'quarterly')>Quarterly</option>
                                <option value="yearly" @selected(old('billing_cycle') === 'yearly')>Yearly</option>
                            </select>
                            @error('billing_cycle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Max Customers</label>
                            <input type="number" name="max_customers" value="{{ old('max_customers') }}" class="form-control" min="0" placeholder="Leave blank for unlimited">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Max Routers</label>
                            <input type="number" name="max_routers" value="{{ old('max_routers') }}" class="form-control" min="0" placeholder="Leave blank for unlimited">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" @checked(old('is_active', true))>
                                <label class="form-check-label" for="isActive">Plan is Active</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="2" class="form-control" placeholder="Short plan description…">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    {{-- Feature Flags --}}
                    <hr class="my-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 fw-semibold"><i class="bi bi-toggles me-2 text-primary"></i>Feature Flags</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addFeatureBtn">
                            <i class="bi bi-plus me-1"></i>Add Feature
                        </button>
                    </div>
                    <div class="table-responsive mb-2">
                        <table class="table table-sm align-middle" id="featuresTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Feature Key <small class="text-muted fw-normal">(slug)</small></th>
                                    <th>Display Label</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="featuresBody">
                            @if(old('feature_flags'))
                                @foreach(old('feature_flags') as $key => $name)
                                <tr class="feature-row">
                                    <td>
                                        <input type="text" class="form-control form-control-sm feature-key" value="{{ $key }}" placeholder="e.g. reports" data-key="{{ $key }}">
                                    </td>
                                    <td>
                                        <input type="text" name="feature_flags[{{ $key }}]" value="{{ $name }}" class="form-control form-control-sm feature-name" placeholder="e.g. Reports Module" required>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-feature"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted">Feature keys control sidebar visibility per plan. Examples: <code>reports</code>, <code>payments</code>, <code>mikrotik</code>, <code>support_tickets</code></small>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('super-admin.pricing-plans.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Create Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let featureIndex = {{ old('feature_flags') ? count(old('feature_flags')) : 0 }};

document.getElementById('addFeatureBtn').addEventListener('click', function () {
    const tbody = document.getElementById('featuresBody');
    const row = document.createElement('tr');
    row.className = 'feature-row';
    const idx = featureIndex++;
    row.innerHTML = `
        <td><input type="text" class="form-control form-control-sm feature-key" placeholder="e.g. reports" data-idx="${idx}"></td>
        <td><input type="text" name="feature_flags[feature_${idx}]" class="form-control form-control-sm feature-name" placeholder="e.g. Reports Module" required></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger remove-feature"><i class="bi bi-trash"></i></button></td>
    `;
    tbody.appendChild(row);
    bindRowEvents(row);
    bindRemoveButtons();
});

function bindRowEvents(row) {
    const keyInput = row.querySelector('.feature-key');
    const nameInput = row.querySelector('.feature-name');
    if (!keyInput || !nameInput) return;
    keyInput.addEventListener('input', function () {
        const slug = keyInput.value.trim().replace(/[^a-z0-9_]/gi, '_').toLowerCase() || ('feature_' + (keyInput.dataset.idx || '0'));
        nameInput.name = `feature_flags[${slug}]`;
    });
}

function bindRemoveButtons() {
    document.querySelectorAll('.remove-feature').forEach(btn => {
        btn.onclick = () => btn.closest('tr').remove();
    });
}

// Init existing rows (old input on validation failure)
document.querySelectorAll('.feature-row').forEach(bindRowEvents);
bindRemoveButtons();
</script>
@endpush
