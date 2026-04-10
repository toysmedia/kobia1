@extends('community.layouts.app')
@section('title', 'Create Post')
@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header fw-bold"><i class="bi bi-plus-circle me-2 text-primary"></i>Create New Post</div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('community.posts.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title') }}" placeholder="Enter a descriptive title (min 10 chars)" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                        <option value="">Select category...</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="discussion" {{ old('type') === 'discussion' ? 'selected' : '' }}>Discussion</option>
                        <option value="question" {{ old('type') === 'question' ? 'selected' : '' }}>Question</option>
                        <option value="article" {{ old('type') === 'article' ? 'selected' : '' }}>Article</option>
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Content <span class="text-danger">*</span></label>
                    <textarea name="body" class="form-control @error('body') is-invalid @enderror" rows="12"
                              placeholder="Write your post content (min 20 chars)..." required>{{ old('body') }}</textarea>
                    @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Tags <small class="text-muted">(optional, max 5, press Enter to add)</small></label>
                    <div class="border rounded p-2 d-flex flex-wrap gap-1 align-items-center" id="tagsContainer" style="min-height:40px;">
                        <input type="text" id="tagInput" class="border-0 outline-0 flex-grow-1" placeholder="Add tags..." style="outline:none; min-width:100px;">
                    </div>
                    <div id="tagSuggestions" class="list-group mt-1 position-absolute d-none" style="z-index:1000; max-width:300px;"></div>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-send me-2"></i>Publish Post</button>
                    <a href="{{ route('community.posts.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const tagsContainer = document.getElementById('tagsContainer');
const tagInput = document.getElementById('tagInput');
const tags = [];

tagInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        const val = this.value.trim();
        if (val && tags.length < 5 && !tags.includes(val)) {
            tags.push(val);
            const badge = document.createElement('span');
            badge.className = 'badge bg-primary-subtle text-primary-emphasis d-flex align-items-center gap-1';
            badge.innerHTML = `#${val} <button type="button" class="btn-close btn-close-sm" style="font-size:.5rem;"></button><input type="hidden" name="tags[]" value="${val}">`;
            badge.querySelector('.btn-close').addEventListener('click', () => {
                tags.splice(tags.indexOf(val), 1);
                badge.remove();
            });
            tagsContainer.insertBefore(badge, this);
        }
        this.value = '';
    }
});
</script>
@endpush
