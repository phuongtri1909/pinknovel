@extends('layouts.app')
@section('content')
    @include('components.banner_home')
    <section class="container-xl">
        @include('components.list_story_home', ['list_story' => $hotStories])
        @include('components.list_story_new_and_rating', [
            'latestUpdatedStories' => $latestUpdatedStories,
            'ratingStories' => $ratingStories,
        ])

        @if ($newStories->count() > 0)
            @include('components.list_story_new_slide', ['newStories' => $newStories])
        @endif


        @include('components.list_story_view_fl', [
            'topViewedStories' => $topViewedStories,
            'topFollowedStories' => $topFollowedStories,
        ])

        @if ($completedStories->count() > 0)
            @include('components.list_story_full', ['completedStories' => $completedStories])
        @endif

        @include('components.show_categories')



    </section>
@endsection
