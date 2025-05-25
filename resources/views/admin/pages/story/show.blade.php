@extends('admin.layouts.app')

@section('content-auth')
    <div class="row">
        <div class="col-12">
            <div class="card mb-0 mx-0 mx-md-4 mb-md-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Chi tiết truyện</h5>
                        <div>
                            <a href="{{ route('stories.edit', $story) }}" class="btn btn-sm bg-gradient-info me-2">
                                <i class="fas fa-edit me-1"></i> Sửa truyện
                            </a>
                            <a href="{{ route('stories.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Quay lại
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <img src="{{ Storage::url($story->cover) }}" class="img-fluid rounded shadow" style="max-height: 300px;">
                                <div class="mt-3">
                                    <span class="badge bg-gradient-{{ $story->status === 'published' ? 'success' : 'secondary' }} mb-2">
                                        {{ $story->status === 'published' ? 'Đã xuất bản' : 'Bản nháp' }}
                                    </span>
                                    @if($story->completed)
                                        <span class="badge bg-gradient-info mb-2">Đã hoàn thành</span>
                                    @else
                                        <span class="badge bg-gradient-warning mb-2">Đang cập nhật</span>
                                    @endif
                                    @if($story->is_18_plus)
                                        <span class="badge bg-gradient-danger mb-2">18+</span>
                                    @endif
                                    @if($story->is_monopoly)
                                        <span class="badge bg-gradient-dark mb-2">Độc quyền</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <h3 class="mb-3">{{ $story->title }}</h3>
                            
                            <div class="mb-3">
                                <h6 class="text-uppercase text-xs font-weight-bolder opacity-6">Thể loại:</h6>
                                <div>
                                    @foreach($story->categories as $category)
                                        <span class="badge {{ $category->is_main ? 'bg-gradient-warning' : 'bg-gradient-light text-dark' }} me-1">
                                            @if($category->is_main)<i class="fas fa-star me-1 small"></i>@endif
                                            {{ $category->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-xs font-weight-bolder opacity-6">Tác giả:</h6>
                                    <p>{{ $story->author_name ?: 'Chưa cập nhật' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-xs font-weight-bolder opacity-6">Dịch giả:</h6>
                                    <p>{{ $story->translator_name ?: 'Chưa cập nhật' }}</p>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-xs font-weight-bolder opacity-6">Loại truyện:</h6>
                                    <p>{{ $story->story_type ? ucfirst($story->story_type) : 'Chưa phân loại' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-xs font-weight-bolder opacity-6">Người đăng:</h6>
                                    <a href="{{ route('users.show', $story->user->id) }}">{{ $story->user->name ?? 'Không xác định' }}</a>
                                </div>
                            </div>
                            
                            @if($story->link_aff)
                                <div class="mb-3">
                                    <h6 class="text-uppercase text-xs font-weight-bolder opacity-6">Link Affiliate:</h6>
                                    <a href="{{ $story->link_aff }}" target="_blank">{{ $story->link_aff }}</a>
                                </div>
                            @endif
                            
                            <div class="mb-3">
                                <h6 class="text-uppercase text-xs font-weight-bolder opacity-6">Thống kê:</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="card mini-stats-wid">
                                            <div class="card-body">
                                                <div class="d-flex">
                                                    <div class="flex-grow-1">
                                                        <p class="text-muted fw-medium mb-2">Số chương</p>
                                                        <h4 class="mb-0">{{ $story->chapters_count }}</h4>
                                                    </div>
                                                    <div class="avatar-sm align-self-center">
                                                        <span class="avatar-title bg-light text-primary rounded-circle">
                                                            <i class="fas fa-book-open fs-5"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="card mini-stats-wid">
                                            <div class="card-body">
                                                <div class="d-flex">
                                                    <div class="flex-grow-1">
                                                        <p class="text-muted fw-medium mb-2">Lượt xem</p>
                                                        <h4 class="mb-0">{{ number_format($story->total_views) }}</h4>
                                                    </div>
                                                    <div class="avatar-sm align-self-center">
                                                        <span class="avatar-title bg-light text-primary rounded-circle">
                                                            <i class="fas fa-eye fs-5"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="card mini-stats-wid">
                                            <div class="card-body">
                                                <div class="d-flex">
                                                    <div class="flex-grow-1">
                                                        <p class="text-muted fw-medium mb-2">Theo dõi</p>
                                                        <h4 class="mb-0">{{ $bookmarks_count }}</h4>
                                                    </div>
                                                    <div class="avatar-sm align-self-center">
                                                        <span class="avatar-title bg-light text-primary rounded-circle">
                                                            <i class="fas fa-bookmark fs-5"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="card mini-stats-wid">
                                            <div class="card-body">
                                                <div class="d-flex">
                                                    <div class="flex-grow-1">
                                                        <p class="text-muted fw-medium mb-2">Doanh thu</p>
                                                        <h4 class="mb-0">{{ number_format($total_revenue) }} xu</h4>
                                                    </div>
                                                    <div class="avatar-sm align-self-center">
                                                        <span class="avatar-title bg-light text-primary rounded-circle">
                                                            <i class="fas fa-coins fs-5"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            @if($story->has_combo)
                                <div class="mb-3">
                                    <h6 class="text-uppercase text-xs font-weight-bolder opacity-6">Thông tin combo:</h6>
                                    <div class="alert alert-info">
                                        <p class="mb-1">- Giá combo: <strong>{{ number_format($story->combo_price) }}</strong> xu</p>
                                        <p class="mb-1">- Tổng giá nếu mua lẻ: <strong>{{ number_format($story->total_chapter_price) }}</strong> xu</p>
                                        <p class="mb-0">- Tiết kiệm: <strong>{{ number_format($story->total_chapter_price - $story->combo_price) }}</strong> xu (<strong>{{ $story->discount_percentage }}%</strong>)</p>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="mb-3">
                                <h6 class="text-uppercase text-xs font-weight-bolder opacity-6">Mô tả:</h6>
                                <div class="p-3 border rounded">
                                    {!! $story->description !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs mt-4" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#story-purchases" role="tab">
                                <i class="fas fa-shopping-cart me-1"></i> Mua truyện
                                <span class="badge bg-primary rounded-pill">{{ $story_purchases_count }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#chapter-purchases" role="tab">
                                <i class="fas fa-file-invoice-dollar me-1"></i> Mua chương
                                <span class="badge bg-primary rounded-pill">{{ $chapter_purchases_count }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#bookmarks" role="tab">
                                <i class="fas fa-bookmark me-1"></i> Theo dõi
                                <span class="badge bg-primary rounded-pill">{{ $bookmarks_count }}</span>
                            </a>
                        </li>
                    </ul>
                    
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <!-- Story Purchases Tab -->
                        <div class="tab-pane active" id="story-purchases" role="tabpanel">
                            <div class="table-responsive mt-3">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Người dùng</th>
                                            <th>Số tiền</th>
                                            <th>Ngày mua</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($story_purchases as $purchase)
                                            <tr>
                                                <td>{{ $purchase->id }}</td>
                                                <td>{{ $purchase->user->name ?? 'Không xác định' }}</td>
                                                <td>{{ number_format($purchase->amount_paid) }} xu</td>
                                                <td>{{ $purchase->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">Chưa có giao dịch mua truyện</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @if($story_purchases_count > 10)
                                    <div class="d-flex justify-content-center mt-3">
                                        {{ $story_purchases->fragment('story-purchases')->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Chapter Purchases Tab -->
                        <div class="tab-pane" id="chapter-purchases" role="tabpanel">
                            <div class="table-responsive mt-3">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Người dùng</th>
                                            <th>Chương</th>
                                            <th>Số tiền</th>
                                            <th>Ngày mua</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($chapter_purchases as $purchase)
                                            <tr>
                                                <td>{{ $purchase->id }}</td>
                                                <td>{{ $purchase->user->name ?? 'Không xác định' }}</td>
                                                <td>Chương {{ $purchase->chapter->number }}: {{ $purchase->chapter->title }}</td>
                                                <td>{{ number_format($purchase->amount_paid) }} xu</td>
                                                <td>{{ $purchase->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">Chưa có giao dịch mua chương</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @if($chapter_purchases_count > 10)
                                    <div class="d-flex justify-content-center mt-3">
                                        {{ $chapter_purchases->fragment('chapter-purchases')->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Bookmarks Tab -->
                        <div class="tab-pane" id="bookmarks" role="tabpanel">
                            <div class="table-responsive mt-3">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Người dùng</th>
                                            <th>Chương đã đọc</th>
                                            <th>Thông báo</th>
                                            <th>Ngày theo dõi</th>
                                            <th>Đọc gần nhất</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($bookmarks as $bookmark)
                                            <tr>
                                                <td>{{ $bookmark->id }}</td>
                                                <td>{{ $bookmark->user->name ?? 'Không xác định' }}</td>
                                                <td>
                                                    @if($bookmark->lastChapter)
                                                        Chương {{ $bookmark->lastChapter->number }}: {{ Str::limit($bookmark->lastChapter->title, 30) }}
                                                    @else
                                                        Chưa đọc
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($bookmark->notification_enabled)
                                                        <span class="badge bg-success">Bật</span>
                                                    @else
                                                        <span class="badge bg-secondary">Tắt</span>
                                                    @endif
                                                </td>
                                                <td>{{ $bookmark->created_at->format('d/m/Y H:i') }}</td>
                                                <td>{{ $bookmark->last_read_at ? $bookmark->last_read_at->format('d/m/Y H:i') : 'Chưa đọc' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">Chưa có người theo dõi</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @if($bookmarks_count > 10)
                                    <div class="d-flex justify-content-center mt-3">
                                        {{ $bookmarks->fragment('bookmarks')->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts-admin')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle tab navigation with URL hash
        const hash = window.location.hash;
        if (hash) {
            const triggerEl = document.querySelector(`a[href="${hash}"]`);
            if (triggerEl) {
                triggerEl.click();
            }
        }
        
        // Update URL hash when tab changes
        const tabLinks = document.querySelectorAll('.nav-link');
        tabLinks.forEach(link => {
            link.addEventListener('click', function() {
                window.location.hash = this.getAttribute('href');
            });
        });
    });
</script>
@endpush 