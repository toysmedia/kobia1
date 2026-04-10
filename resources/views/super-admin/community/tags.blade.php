@extends('layouts.super-admin')
@section('title', 'Community - Tags')
@section('page-title', 'Community Tags')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-tags me-2 text-primary"></i>Manage Tags</h5>
    <a href="{{ route('super-admin.community.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Tag</th><th>Slug</th><th>Posts</th><th>Usage Count</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse($tags as $tag)
                <tr>
                    <td><a href="{{ route('community.tags.show', $tag->slug) }}" target="_blank" class="fw-semibold text-decoration-none">#{{ $tag->name }}</a></td>
                    <td><code>{{ $tag->slug }}</code></td>
                    <td><span class="badge bg-secondary-subtle text-secondary-emphasis">{{ $tag->posts_count }}</span></td>
                    <td>{{ $tag->usage_count }}</td>
                    <td>
                        <form method="POST" action="{{ route('super-admin.community.tags.destroy', $tag->id) }}" class="d-inline" onsubmit="return confirm('Delete tag #{{ $tag->name }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" style="font-size:.75rem;"><i class="bi bi-trash"></i> Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No tags found</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $tags->links() }}</div>
</div>
@endsection
