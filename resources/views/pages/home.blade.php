@extends('layouts.app')
@section('content')
    @include('components.banner_home')
    <section class="container-xl">
        @include('components.list_story_home', ['list_story' => $hotStories])
        @include('components.list_story_new_and_rating', ['newStories' => $newStories])
        @include('components.list_story_new_slide', ['newStories' => $newStories])
        @include('components.list_story_view_fl', ['newStories' => $newStories])
        @include('components.list_story_full', ['completedStories' => $completedStories])
        @include('components.show_categories')

        
      
    </section>
@endsection

