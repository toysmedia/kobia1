@if ($errors->any())
<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">Category</label>
        <select name="category" class="form-select">
            <option value="">— Select —</option>
            @foreach(['billing','connectivity','navigation','mikrotik','general','community','security','support'] as $cat)
                <option value="{{ $cat }}" @selected(old('category', $entry->category ?? '') === $cat)>{{ ucfirst($cat) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">Language</label>
        <select name="language" class="form-select">
            <option value="en" @selected(old('language', $entry->language ?? 'en') === 'en')>English</option>
            <option value="sw" @selected(old('language', $entry->language ?? '') === 'sw')>Swahili</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">Active</label>
        <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1"
                   @checked(old('is_active', $entry->is_active ?? true))>
            <label class="form-check-label" for="isActive">Enabled</label>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Question <span class="text-danger">*</span></label>
        <textarea name="question" class="form-control" rows="2" required>{{ old('question', $entry->question ?? '') }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Answer <span class="text-danger">*</span></label>
        <textarea name="answer" class="form-control" rows="5" required>{{ old('answer', $entry->answer ?? '') }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Keywords <span class="text-muted small">(comma-separated)</span></label>
        <input type="text" name="keywords" class="form-control"
               value="{{ old('keywords', isset($entry) ? implode(', ', $entry->keywords ?? []) : '') }}"
               placeholder="mpesa, payment, billing">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold d-block">Portal Context</label>
        <div class="d-flex flex-wrap gap-3 mt-1">
            @foreach(['guest','admin','customer','community','login'] as $p)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="portal_context[]" value="{{ $p }}"
                           id="pc_{{ $p }}"
                           @checked(in_array($p, old('portal_context', $entry->portal_context ?? [])))>
                    <label class="form-check-label" for="pc_{{ $p }}">{{ ucfirst($p) }}</label>
                </div>
            @endforeach
        </div>
    </div>
</div>
