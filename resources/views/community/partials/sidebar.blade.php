@if(isset($popularCategories) && $popularCategories->count())
<div class="card sidebar-widget mb-4">
    <div class="card-header fw-semibold"><i class="bi bi-grid me-2 text-primary"></i>Popular Categories</div>
    <div class="card-body p-2">
        @foreach($popularCategories as $cat)
        <a href="{{ route('community.categories.show', $cat->slug) }}" class="d-flex justify-content-between align-items-center p-2 rounded text-decoration-none hover-bg mb-1" style="color:inherit;">
            <span>
                @if($cat->icon)<i class="bi bi-{{ $cat->icon }} me-2"></i>@endif
                {{ $cat->name }}
            </span>
            <span class="badge bg-secondary rounded-pill">{{ $cat->posts_count }}</span>
        </a>
        @endforeach
    </div>
</div>
@endif

@if(isset($trendingTags) && $trendingTags->count())
<div class="card sidebar-widget mb-4">
    <div class="card-header fw-semibold"><i class="bi bi-tags me-2 text-primary"></i>Trending Tags</div>
    <div class="card-body">
        @foreach($trendingTags as $tag)
        <a href="{{ route('community.tags.show', $tag->slug) }}" class="tag-badge badge bg-secondary-subtle text-secondary-emphasis me-1 mb-1 text-decoration-none">
            #{{ $tag->name }}
        </a>
        @endforeach
    </div>
</div>
@endif

@guest('community')
<div class="card sidebar-widget border-primary">
    <div class="card-body text-center">
        <i class="bi bi-people-fill text-primary fs-2 mb-2"></i>
        <h6 class="fw-bold">Join the Community</h6>
        <p class="text-muted small mb-3">Share knowledge, ask questions and connect with ISP professionals.</p>
        <a href="{{ route('community.register') }}" class="btn btn-primary btn-sm w-100 mb-2">Sign Up Free</a>
        <a href="{{ route('community.login') }}" class="btn btn-outline-secondary btn-sm w-100">Login</a>
    </div>
</div>
@endguest
