@extends('layouts.super-admin')
@section('title', 'Knowledge Base')
@section('page-title', 'AI Knowledge Base')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0"><i class="bi bi-book me-2 text-primary"></i>Knowledge Base</h4>
    <a href="{{ route('super-admin.ai.knowledge.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Entry</a>
</div>

{{-- Filters --}}
<form method="GET" class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search question / answer…" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="category" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="language" class="form-select form-select-sm">
                    <option value="">All Languages</option>
                    <option value="en" @selected(request('language') === 'en')>English</option>
                    <option value="sw" @selected(request('language') === 'sw')>Swahili</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('super-admin.ai.knowledge') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
            </div>
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Category</th>
                    <th>Question</th>
                    <th>Portals</th>
                    <th>Lang</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                <tr>
                    <td>{{ $entry->id }}</td>
                    <td><span class="badge bg-secondary">{{ $entry->category ?? 'general' }}</span></td>
                    <td>{{ Str::limit($entry->question, 70) }}</td>
                    <td>
                        @if($entry->portal_context)
                            @foreach($entry->portal_context as $p)
                                <span class="badge bg-info text-dark">{{ $p }}</span>
                            @endforeach
                        @else
                            <span class="text-muted small">all</span>
                        @endif
                    </td>
                    <td>{{ strtoupper($entry->language) }}</td>
                    <td>
                        @if($entry->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('super-admin.ai.knowledge.edit', $entry->id) }}" class="btn btn-outline-primary btn-sm py-0 px-2"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('super-admin.ai.knowledge.delete', $entry->id) }}" class="d-inline" onsubmit="return confirm('Delete this entry?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm py-0 px-2"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No knowledge base entries yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($entries->hasPages())
    <div class="card-footer bg-transparent">{{ $entries->links() }}</div>
    @endif
</div>
@endsection
