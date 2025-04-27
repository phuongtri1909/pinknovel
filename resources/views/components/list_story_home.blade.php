<section>
    <div class="mt-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center bg-8ed7ff px-3 pb-3 pt-1 rounded-top-custom">
            <h2 class="fs-5 m-0 text-dark"><i class="fa-solid fa-fire fa-xl" style="color: #ffe371;"></i> Truyện Hot</h2>
            <div>
                <select class="form-select shadow-sm rounded-4" id="categoryFilter" style="min-width: 200px;">
                    <option selected value="">Tất cả thể loại</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Stories Grid -->
        <div id="storiesContainer" class="bg-white rounded-bottom-custom">
            @include('components.stories-grid', ['hotStories' => $hotStories])
        </div>
    </div>
</section>

@once
    @push('scripts')
        <script>
            $(document).ready(function() {
                $('#categoryFilter').change(function() {
                    const categoryId = $(this).val();
                    const storiesContainer = $('#storiesContainer');

                    // Show loading state
                    storiesContainer.addClass('loading');

                    $.ajax({
                        url: '{{ route('home') }}',
                        method: 'GET',
                        data: {
                            category_id: categoryId,
                            type: 'hot'
                        },
                        success: function(response) {
                            storiesContainer.html(response.html);
                            // Reinitialize any needed plugins or behaviors
                        },
                        error: function() {
                            alert('Có lỗi xảy ra khi lọc truyện');
                        },
                        complete: function() {
                            storiesContainer.removeClass('loading');
                        }
                    });
                });
            });
        </script>
    @endpush

    @push('styles')
        <style>

            .rounded-bottom-custom {
                border-bottom-left-radius: 1rem !important;
                border-bottom-right-radius: 1rem !important;
            }

            .rounded-top-custom {
                border-top-left-radius: 1rem !important;
                border-top-right-radius: 1rem !important;
            }

            /* Remaining styles that can't be replaced by Bootstrap */
            #storiesContainer.loading {
                opacity: 0.6;
                pointer-events: none;
            }

            /* Story Card Styles */
            .story-item {
                opacity: 0;
                transform: translateY(20px);
                animation: fadeInUp 0.6s ease forwards;
            }

            .story-card {
                overflow: hidden;
                transition: all 0.3s ease;
                height: 100%;
                position: relative;
            }

            .story-card:hover {
                transform: translateY(-5px);
            }

            /* Thumbnail Styles */
            .story-thumbnail {
                position: relative;
                padding-top: 133%;
                background: #f8f9fa;
            }

            .story-thumbnail img {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                object-fit: scale-down;
                transition: transform 0.3s ease;
            }

            .story-card:hover .story-thumbnail img {
                transform: translateY(-5px);
            }

            /* Hover Effect Styles */
            .story-hover {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.8);
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1rem;
                backdrop-filter: blur(2px);
            }

            .story-card:hover .story-hover {
                opacity: 1;
                visibility: visible;
            }

            .hover-content {
                color: white;
                text-align: center;
                transform: translateY(10px);
                transition: transform 0.3s ease;
            }

            .story-card:hover .hover-content {
                transform: translateY(0);
            }

            /* Story title height limiting */
            .story-title {
                height: 2.5em;
                overflow: hidden;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
            }

            /* Category Badge Styles - special styling */
                        /* Category Badge Styles */
                        .story-categories {
                display: flex;
                flex-wrap: wrap;
                gap: 4px;
                justify-content: center;
                margin-top: 8px;
            }

            .category-badge {
                background: rgba(255, 255, 255, 0.2);
                color: white;
                padding: 3px 10px;
                border-radius: 12px;
                font-size: 0.75rem;
                backdrop-filter: blur(4px);
                transition: all 0.3s ease;
            }

            .category-badge:hover {
                background: rgba(255, 255, 255, 0.3);
                transform: translateY(-1px);
            }

            /* Animation */
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Animation Delays for Grid Items */
            .story-item:nth-child(1) {
                animation-delay: 0.1s;
            }

            .story-item:nth-child(2) {
                animation-delay: 0.2s;
            }

            .story-item:nth-child(3) {
                animation-delay: 0.3s;
            }

            .story-item:nth-child(4) {
                animation-delay: 0.4s;
            }

            .story-item:nth-child(5) {
                animation-delay: 0.5s;
            }

            .story-item:nth-child(6) {
                animation-delay: 0.6s;
            }
        </style>
    @endpush
@endonce



           
