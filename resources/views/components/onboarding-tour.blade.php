{{-- Onboarding Tour Component
     Include once in the admin layout; passes server-side tour_completed flag to JS.
--}}
@php
    $tourCompleted = auth('admin')->user()?->tour_completed ?? false;
@endphp

<script>
    window.__oxnetTourCompleted = {{ $tourCompleted ? 'true' : 'false' }};
</script>
