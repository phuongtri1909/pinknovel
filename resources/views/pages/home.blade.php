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

        <!-- Facebook Page Plugin -->
        <div class="mt-4">
            <div class="w-100">
                <div class="fb-page" data-href="https://www.facebook.com/pinknovel" data-small-header="false"
                    data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true">
                </div>
            </div>
        </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v17.0"
        nonce="random_nonce"></script>
@endpush
