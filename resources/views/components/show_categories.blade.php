<!-- filepath: /d:/full_truyen/resources/views/components/categories-widget.blade.php -->
<div class="sidebar-widget categories-widget rounded-4 shadow-sm mt-4">
    <div class="widget-header mb-3">
        <h3 class="fs-5 m-0 text-dark">
            <i class="fas fa-tags text-primary me-2"></i>

            Thể Loại

        </h3>
    </div>

    <div class="widget-content">
        <div class="category-grid">
            @foreach ($categories as $category)
                <a href="{{ route('categories.story.show', $category->slug) }}"
                    class="category-item rounded-4 {{ isset($currentCategory) && $currentCategory->id == $category->id ? 'active' : '' }}">
                    <span class="category-name">{{ $category->name }}</span>

                </a>
            @endforeach
        </div>
    </div>
</div>

@once
    @push('styles')
        <style>
            /* Categories Widget Styles */
            .category-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }

            .category-item {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 8px;
                background: #f8f9fa;
                color: #333;
                text-decoration: none;
                border-radius: 4px;
                text-align: center;
                transition: all 0.3s;
                border: 1px solid rgba(0, 0, 0, 0.05);
            }

            .category-item:hover {
                background: #007bff;
                color: white;
                transform: translateY(-2px);
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

            .category-item.active {
                background: #007bff;
                color: white;
                font-weight: 500;
            }

            .category-item.active .story-count {
                background-color: white;
                color: #007bff;
            }


            /* Responsive adjustments */
            @media (max-width: 575px) {
                .category-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
            }

            @media (min-width: 576px) and (max-width: 767px) {
                .category-grid {
                    grid-template-columns: repeat(3, 1fr);
                }
            }

            @media (min-width: 768px) and (max-width: 991px) {
                .category-grid {
                    grid-template-columns: repeat(4, 1fr);
                }
            }

            @media (min-width: 992px) {
                .category-grid {
                    grid-template-columns: repeat(5, 1fr);
                }
            }
        </style>
    @endpush
@endonce
