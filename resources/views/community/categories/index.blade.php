@extends('community.layouts.app')
@section('title', 'Categories')
@section('content')

<h5 class="fw-bold mb-4"><i class="bi bi-grid me-2 text-primary"></i>Browse Categories</h5>

@forelse($categories as $category)
<div class="card post-card mb-3 border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex align-items-start gap-3">
            @if($category->color)
            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width:48px;height:48px;background:{{ $category->color }};flex-shrink:0;">
                @if($category->icon)<i class="bi bi-{{ $category->icon }}"></i>@else{{ substr($category->name, 0, 1) }}@endif
            </div>
            @else
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white" style="width:48px;height:48px;flex-shrink:0;">
                @if($category->icon)<i class="bi bi-{{ $category->icon }}"></i>@else<i class="bi bi-folder"></i>@endif
            </div>
            @endif
            <div class="flex-grow-1">
                <h6 class="fw-bold mb-1">
                    <a href="{{ route('community.categories.show', $category->slug) }}" class="text-decoration-none">{{ $category->name }}</a>
                    <span class="badge bg-secondary-subtle text-secondary-emphasis ms-2">{{ $category->posts_count }} posts</span>
                </h6>
                @if($category->description)
                <p class="text-muted small mb-2">{{ $category->description }}</p>
                @endif
                @if($category->children->count())
                <div class="d-flex flex-wrap gap-1">
                    @foreach($category->children as $child)
                    <a href="{{ route('community.categories.show', $child->slug) }}" class="badge bg-secondary-subtle text-secondary-emphasis text-decoration-none">{{ $child->name }}</a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@empty
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-grid fs-1 text-muted mb-3"></i>
        <h5 class="text-muted">No categories yet</h5>
    </div>
</div>
@endforelse
@endsection
