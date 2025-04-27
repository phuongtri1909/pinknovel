<section class="new-stories-section mt-4">
    <div class="row">
        <!-- Main Content - New Stories -->
        <div class="col-12 col-md-7 col-lg-8">
            <div class="content-wrapper">
                <div class="section-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="fs-5 m-0 text-dark">
                            <i class="fas fa-clock text-primary me-2 fa-xl cl-00b894"></i>Mới Cập Nhật
                        </h2>
                        <div class="category-filter">
                            <select class="form-select custom-select rounded-4" id="newStoryCategoryFilter">
                                <option value="">Tất cả thể loại</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="stories-container">
                    <div class="list-stories">
                        @include('components.story-list-items', ['newStories' => $newStories])
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-12 col-md-5 col-lg-4 mt-3 mt-sm-0">

            {{-- hot stories --}}
            @include('components.hot_stories')

            {{-- full stories --}}

            <!-- Recently Read Stories -->
            @include('components.recent-reads')

            <!-- Categories Widget -->
            <x-categories-widget :categories="$categories" :is-search="true" />
        </div>
    </div>
</section>

@once
    @push('styles')
        <style>
            .story-badges {
                display: flex;
                gap: 8px;
                margin-top: 8px;
            }

            .badge-new,
            .badge-hot,
            .badge-full {
                display: inline-block;
                padding: 2px 8px;
                border-radius: 4px;
                font-size: 0.75rem;
                font-weight: 600;
                text-transform: uppercase;
            }

            .badge-new {
                background: linear-gradient(135deg, #00b894, #00cec9);
                color: white;
            }

            .badge-hot {
                background: linear-gradient(135deg, #ff7675, #d63031);
                color: white;
            }

            .badge-full {
                background: linear-gradient(135deg, #6c5ce7, #a29bfe);
                color: white;
            }

            /* Main Content Styles */
            .section-title {
                font-size: 1.25rem;
                font-weight: 600;
                color: #333;
            }

            .story-thumb {
                width: 90px;
                height: 130px;
                object-fit: cover;
                border-radius: 4px;
            }

            .story-title {
                font-size: 0.9rem;
                margin: 0;
            }

            .story-title a {
                color: #333;
                text-decoration: none;
                transition: color 0.3s;
            }

            .story-title a:hover {
                color: #007bff;
            }

            .category-tag {
                display: inline-block;
                padding: 2px 8px;
                margin-right: 4px;
                background: #f0f2f5;
                color: #666;
                border-radius: 12px;
                font-size: 0.75rem;
            }

            /* Sidebar Styles */
            .recent-story-thumb {
                width: 50px;
                height: 65px;
                object-fit: cover;
                border-radius: 4px;
            }

            .recent-story-title {
                font-size: 0.95rem;
                margin: 0;
                line-height: 1.4;
            }

            .recent-story-title a {
                color: #333;
                text-decoration: none;
                transition: color 0.3s;
            }

            .recent-story-title a:hover {
                color: #007bff;
            }

           
            /* Responsive adjustments */
            @media (max-width: 768px) {
                .story-title {
                    font-size: 1rem;
                }

                .story-meta {
                    font-size: 0.85rem;
                }
            }

            
        </style>
    @endpush

    @push('scripts')
        <script>
            $(document).ready(function() {
                $('#newStoryCategoryFilter').change(function() {
                    const categoryId = $(this).val();
                    const storiesContainer = $('.list-stories');
                    
                    storiesContainer.addClass('loading');
                    
                    $.ajax({
                        url: '{{ route("home") }}',
                        method: 'GET',
                        data: {
                            category_id: categoryId,
                            type: 'new'
                        },
                        success: function(response) {
                            storiesContainer.html(response.html);
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
@endonce
