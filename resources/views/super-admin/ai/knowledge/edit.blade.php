@extends('layouts.super-admin')
@section('title', 'Edit Knowledge')
@section('page-title', 'Edit Knowledge Base Entry')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0"><i class="bi bi-pencil me-2 text-primary"></i>Edit Knowledge Base Entry</h4>
    <a href="{{ route('super-admin.ai.knowledge') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('super-admin.ai.knowledge.update', $entry->id) }}">
            @csrf @method('PUT')
            @include('super-admin.ai.knowledge._form', ['entry' => $entry])
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Update Entry</button>
                <a href="{{ route('super-admin.ai.knowledge') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
