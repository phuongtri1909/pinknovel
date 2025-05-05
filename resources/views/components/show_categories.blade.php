<!-- filepath: d:\truyen\pinknovel\resources\views\components\show_categories.blade.php -->
<section>
    <div class="bg-4 mt-5 p-3 border-bottom-custom-1">
        <div class="border-bottom-custom d-flex justify-content-between align-items-center">
            <span class="color-3 fw-semibold py-3 fs-5">THỂ LOẠI</span>
            <a href="" class="text-decoration-none text-gray-600 hover-color-3">xem thêm</a>
        </div>
        <div>
            <div class="row mt-3">
                @foreach ($categories as $category)
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                        <a href="" class="link-category text-decoration-none text-dark hover-color-3">
                            <i class="fas fa-chevron-right category-icon"></i>
                            <span class="fw-semibold">{{ $category->name }}</span> <span
                                class="text-gray-600">({{ $category->stories_count }})</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

@once
    @push('styles')
        <style>
            .border-bottom-custom {
                border-bottom: 1px solid #a7a7a7;
            }

            .border-bottom-custom-1 {
                border-bottom: 4px solid var(--primary-color-3);
            }

            .category-icon {
                font-size: 12px;
                margin-right: 8px;
                color: #666666;
                transition: all 200ms ease;
            }
            
            .link-category {
                display: inline-flex;
                align-items: center;
            }
            
            .link-category:hover .category-icon {
                color: var(--primary-color-3);
                transform: translateX(2px);
            }
        </style>
    @endpush
@endonce