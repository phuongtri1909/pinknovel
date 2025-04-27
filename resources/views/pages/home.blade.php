@extends('layouts.app')
@section('content')
    @include('components.banner_home')
    <section class="container-xl">
        @include('components.list_story_home', ['list_story' => $hotStories])
        @include('components.list_story_new', ['newStories' => $newStories])
        @include('components.list_story_full', ['completedStories' => $completedStories])
    </section>

@endsection

@push('styles')
@endpush

@push('scripts')
@endpush
