@extends('community.layouts.app')
@section('title', 'Tags')
@section('content')

<h5 class="fw-bold mb-4"><i class="bi bi-tags me-2 text-primary"></i>All Tags</h5>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        @forelse($tags as $tag)
        <a href="{{ route('community.tags.show', $tag->slug) }}"
           class="tag-badge badge me-2 mb-2 text-decoration-none"
           style="background: rgba(var(--bs-primary-rgb),.1); color: var(--bs-primary); font-size: {{ min(1.1, 0.7 + $tag->usage_count/50) }}rem; padding: .4rem .9rem;">
            #{{ $tag->name }}
            <span class="ms-1 opacity-75">({{ $tag->usage_count }})</span>
        </a>
        @empty
        <p class="text-muted">No tags yet.</p>
        @endforelse
    </div>
</div>
@endsection
