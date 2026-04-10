@extends('community.layouts.app')
@section('title', 'Edit Post')
@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header fw-bold"><i class="bi bi-pencil me-2 text-primary"></i>Edit Post</div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('community.posts.update', $post->slug) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title', $post->title) }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $post->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Content <span class="text-danger">*</span></label>
                    <textarea name="body" class="form-control @error('body') is-invalid @enderror" rows="12" required>{{ old('body', $post->body) }}</textarea>
                    @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Tags <small class="text-muted">(current tags will be replaced)</small></label>
                    <div class="border rounded p-2 d-flex flex-wrap gap-1 align-items-center" id="tagsContainer" style="min-height:40px;">
                        @foreach($post->tags as $tag)
                        <span class="badge bg-primary-subtle text-primary-emphasis d-flex align-items-center gap-1">
                            #{{ $tag->name }}
                            <button type="button" class="btn-close" style="font-size:.5rem;"></button>
                            <input type="hidden" name="tags[]" value="{{ $tag->name }}">
                        </span>
                        @endforeach
                        <input type="text" id="tagInput" class="border-0 flex-grow-1" placeholder="Add tag..." style="outline:none; min-width:100px;">
                    </div>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-2"></i>Update Post</button>
                    <a href="{{ route('community.posts.show', $post->slug) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
