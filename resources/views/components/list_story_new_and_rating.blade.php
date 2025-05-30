<section class="mt-5 bg-list rounded px-0 px-md-4 pb-4">
    <div class="row">
        <div class="col-12 col-md-6">

            <div class="d-flex justify-content-between align-items-center p-3 rounded-top-custom">
                <h2 class="fs-5 m-0 text-dark fw-bold"><i class="fa-solid fa-book-open" style="color: #22c55e;"></i> Truyện
                    Mới</h2>
                <div>
                    <a class="color-3 text-decoration-none" href="{{ route('story.new.chapter') }}">Xem tất cả <i
                            class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>

            <!-- Stories Grid -->
            <div class="bg-white rounded-3 p-3">
                @foreach ($latestUpdatedStories as $story)
                    @include('components.story_new', ['story' => $story])
                @endforeach
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="d-flex justify-content-between align-items-center p-3 rounded-top-custom">
                <h2 class="fs-5 m-0 text-dark fw-bold"><i class="fa-solid fa-star fa-xl"
                        style="color: #f59e0b;"></i>Đánh giá cao</h2>
                <div>
                    <a class="color-3 text-decoration-none" href="{{ route('story.rating') }}">Xem tất cả <i
                            class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>

            <div class="bg-white rounded-3 p-3">
                @foreach ($ratingStories as $story)
                    @include('components.story_rating', ['story' => $story])
                @endforeach
            </div>
        </div>
    </div>
</section>
