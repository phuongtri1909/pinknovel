@extends('admin.layouts.app')

@section('content-auth')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-0 mx-md-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">Chi tiết lịch sử chuyển nhượng #{{ $history->id }}</h5>
                            <p class="text-sm mb-0">Thông tin chi tiết về giao dịch chuyển nhượng truyện</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.story-transfer.history') }}" class="btn bg-gradient-secondary btn-sm">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Transfer Summary -->
                        <div class="col-12">
                            <div class="card mb-4">
                                <div class="card-header bg-gradient-info">
                                    <h6 class="text-white mb-0">
                                        <i class="fas fa-info-circle me-2"></i>Thông tin giao dịch
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td class="text-sm font-weight-bold">ID giao dịch:</td>
                                                    <td class="text-sm">#{{ $history->id }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-sm font-weight-bold">Loại chuyển nhượng:</td>
                                                    <td>
                                                        <span class="badge {{ $history->transfer_type === 'bulk' ? 'bg-gradient-warning' : 'bg-gradient-info' }}">
                                                            {{ $history->transfer_type_text }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-sm font-weight-bold">Trạng thái:</td>
                                                    <td>
                                                        <span class="badge {{ $history->status_badge }}">
                                                            {{ $history->status_text }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-sm font-weight-bold">Thời gian:</td>
                                                    <td class="text-sm">{{ $history->transferred_at_formatted }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td class="text-sm font-weight-bold">Thực hiện bởi:</td>
                                                    <td class="text-sm">
                                                        {{ $history->transferred_by_name }}
                                                        @if($history->transferredBy)
                                                            <br><small class="text-secondary">{{ $history->transferredBy->email }}</small>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-sm font-weight-bold">IP Address:</td>
                                                    <td class="text-sm">{{ $history->ip_address ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-sm font-weight-bold">Thời gian tương đối:</td>
                                                    <td class="text-sm">{{ $history->time_ago }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Story Information -->
                        <div class="col-lg-6">
                            <div class="card mb-4">
                                <div class="card-header bg-gradient-primary">
                                    <h6 class="text-white mb-0">
                                        <i class="fas fa-book me-2"></i>Thông tin truyện
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if($history->story)
                                        <div class="d-flex">
                                            <div class="me-3">
                                                <img src="{{ $history->story->cover ? asset('storage/' . $history->story->cover) : asset('assets/img/default-story.png') }}" 
                                                     class="img-fluid border-radius-lg" style="width: 80px; height: 120px; object-fit: cover;" alt="Story Cover">
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-2">{{ $history->story_title }}</h6>
                                                <p class="text-sm mb-1">
                                                    <strong>ID:</strong> {{ $history->story_id }}
                                                </p>
                                                <p class="text-sm mb-1">
                                                    <strong>Slug:</strong> {{ $history->story_slug ?? 'N/A' }}
                                                </p>
                                                <p class="text-sm mb-1">
                                                    <strong>Trạng thái hiện tại:</strong> 
                                                    <span class="badge {{ $history->story->status_badge }}">
                                                        {{ $history->story->status_text }}
                                                    </span>
                                                </p>
                                                <a href="{{ route('stories.show', $history->story) }}" 
                                                   class="btn btn-sm bg-gradient-info mt-2">
                                                    <i class="fas fa-eye me-1"></i>Xem truyện
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Truyện đã bị xóa</strong>
                                            <br>
                                            <small>
                                                <strong>Tiêu đề:</strong> {{ $history->story_title }}<br>
                                                <strong>ID:</strong> {{ $history->story_id }}
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Transfer Reason -->
                            <div class="card">
                                <div class="card-header bg-gradient-warning">
                                    <h6 class="text-white mb-0">
                                        <i class="fas fa-comment me-2"></i>Lý do chuyển nhượng
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-sm mb-0">{{ $history->reason }}</p>
                                    
                                    @if($history->notes)
                                        <hr>
                                        <h6 class="text-sm font-weight-bold">Ghi chú:</h6>
                                        <p class="text-sm text-danger mb-0">{{ $history->notes }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Authors Information -->
                        <div class="col-lg-6">
                            <!-- Old Author -->
                            <div class="card mb-4">
                                <div class="card-header bg-gradient-danger">
                                    <h6 class="text-white mb-0">
                                        <i class="fas fa-user-minus me-2"></i>Tác giả cũ (Chuyển từ)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex">
                                        @if($history->oldAuthor)
                                            <div class="me-3">
                                                <img src="{{ $history->oldAuthor->avatar ? asset('storage/' . $history->oldAuthor->avatar) : asset('assets/img/default-avatar.png') }}" 
                                                     class="avatar avatar-lg" alt="Old Author Avatar">
                                            </div>
                                            <div>
                                                <h6 class="mb-1">{{ $history->old_author_name }}</h6>
                                                <p class="text-sm mb-1">{{ $history->old_author_email }}</p>
                                                <span class="badge bg-gradient-info">{{ $history->oldAuthor->role }}</span>
                                                <p class="text-xs mt-2 mb-0">
                                                    <strong>Tổng truyện hiện tại:</strong> {{ $history->oldAuthor->stories()->count() }}
                                                </p>
                                                <a href="{{ route('users.show', $history->oldAuthor) }}" 
                                                   class="btn btn-sm bg-gradient-secondary mt-2">
                                                    <i class="fas fa-user me-1"></i>Xem profile
                                                </a>
                                            </div>
                                        @else
                                            <div class="alert alert-warning w-100">
                                                <i class="fas fa-user-times me-2"></i>
                                                <strong>Tác giả không còn tồn tại</strong>
                                                <br>
                                                <small>
                                                    <strong>Tên:</strong> {{ $history->old_author_name }}<br>
                                                    <strong>Email:</strong> {{ $history->old_author_email }}
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- New Author -->
                            <div class="card">
                                <div class="card-header bg-gradient-success">
                                    <h6 class="text-white mb-0">
                                        <i class="fas fa-user-plus me-2"></i>Tác giả mới (Chuyển đến)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex">
                                        @if($history->newAuthor)
                                            <div class="me-3">
                                                <img src="{{ $history->newAuthor->avatar ? asset('storage/' . $history->newAuthor->avatar) : asset('assets/img/default-avatar.png') }}" 
                                                     class="avatar avatar-lg" alt="New Author Avatar">
                                            </div>
                                            <div>
                                                <h6 class="mb-1">{{ $history->new_author_name }}</h6>
                                                <p class="text-sm mb-1">{{ $history->new_author_email }}</p>
                                                <span class="badge bg-gradient-info">{{ $history->newAuthor->role }}</span>
                                                <p class="text-xs mt-2 mb-0">
                                                    <strong>Tổng truyện hiện tại:</strong> {{ $history->newAuthor->stories()->count() }}
                                                </p>
                                                <a href="{{ route('users.show', $history->newAuthor) }}" 
                                                   class="btn btn-sm bg-gradient-secondary mt-2">
                                                    <i class="fas fa-user me-1"></i>Xem profile
                                                </a>
                                            </div>
                                        @else
                                            <div class="alert alert-warning w-100">
                                                <i class="fas fa-user-times me-2"></i>
                                                <strong>Tác giả không còn tồn tại</strong>
                                                <br>
                                                <small>
                                                    <strong>Tên:</strong> {{ $history->new_author_name }}<br>
                                                    <strong>Email:</strong> {{ $history->new_author_email }}
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Transfer Metadata -->
                        @if($history->transfer_metadata)
                            <div class="col-12 mt-4">
                                <div class="card">
                                    <div class="card-header bg-gradient-dark">
                                        <h6 class="text-white mb-0">
                                            <i class="fas fa-database me-2"></i>Metadata tại thời điểm chuyển nhượng
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            $metadata = $history->transfer_metadata;
                                        @endphp
                                        
                                        <!-- Basic Statistics -->
                                        <div class="row">
                                            @if(isset($metadata['chapters_count']))
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="border-radius-md bg-gradient-primary p-3 text-center mb-3">
                                                        <h4 class="text-white mb-0">{{ $metadata['chapters_count'] }}</h4>
                                                        <p class="text-white text-xs mb-0">Tổng chương</p>
                                                    </div>
                                                </div>
                                            @endif

                                            @if(isset($metadata['published_chapters']))
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="border-radius-md bg-gradient-success p-3 text-center mb-3">
                                                        <h4 class="text-white mb-0">{{ $metadata['published_chapters'] }}</h4>
                                                        <p class="text-white text-xs mb-0">Đã xuất bản</p>
                                                    </div>
                                                </div>
                                            @endif

                                            @if(isset($metadata['total_views']))
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="border-radius-md bg-gradient-info p-3 text-center mb-3">
                                                        <h4 class="text-white mb-0">{{ number_format($metadata['total_views']) }}</h4>
                                                        <p class="text-white text-xs mb-0">Lượt xem</p>
                                                    </div>
                                                </div>
                                            @endif

                                            @if(isset($metadata['bookmarks_count']))
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="border-radius-md bg-gradient-warning p-3 text-center mb-3">
                                                        <h4 class="text-white mb-0">{{ $metadata['bookmarks_count'] }}</h4>
                                                        <p class="text-white text-xs mb-0">Bookmark</p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Revenue Statistics - NEW SECTION -->
                                        @if(isset($metadata['revenue']))
                                            <div class="row">
                                                <div class="col-12">
                                                    <h6 class="text-dark mb-3">
                                                        <i class="fas fa-dollar-sign me-2"></i>Thông tin doanh thu
                                                    </h6>
                                                </div>
                                                
                                                <div class="col-md-4">
                                                    <div class="card bg-gradient-success">
                                                        <div class="card-body text-center text-white">
                                                            <h4 class="mb-1">{{ number_format($metadata['revenue']['total_revenue']) }}</h4>
                                                            <p class="text-xs mb-0">Tổng doanh thu (xu)</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-4">
                                                    <div class="card bg-gradient-info">
                                                        <div class="card-body text-center text-white">
                                                            <h4 class="mb-1">{{ number_format($metadata['revenue']['story_purchases']['total_amount']) }}</h4>
                                                            <p class="text-xs mb-0">Doanh thu mua truyện</p>
                                                            <small class="opacity-8">{{ $metadata['revenue']['story_purchases']['count'] }} lượt mua</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-4">
                                                    <div class="card bg-gradient-warning">
                                                        <div class="card-body text-center text-white">
                                                            <h4 class="mb-1">{{ number_format($metadata['revenue']['chapter_purchases']['total_amount']) }}</h4>
                                                            <p class="text-xs mb-0">Doanh thu mua chương</p>
                                                            <small class="opacity-8">{{ $metadata['revenue']['chapter_purchases']['count'] }} lượt mua</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Performance Metrics - NEW SECTION -->
                                        @if(isset($metadata['metrics']))
                                            <div class="row mt-4">
                                                <div class="col-12">
                                                    <h6 class="text-dark mb-3">
                                                        <i class="fas fa-chart-line me-2"></i>Chỉ số hiệu suất
                                                    </h6>
                                                </div>
                                                
                                                <div class="col-md-4">
                                                    <div class="card">
                                                        <div class="card-body text-center">
                                                            <h6 class="mb-2">Tỷ lệ bookmark</h6>
                                                            <div class="progress">
                                                                <div class="progress-bar bg-info" style="width: {{ min($metadata['metrics']['bookmark_rate'], 100) }}%"></div>
                                                            </div>
                                                            <small class="text-muted">{{ $metadata['metrics']['bookmark_rate'] }}%</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-4">
                                                    <div class="card">
                                                        <div class="card-body text-center">
                                                            <h6 class="mb-2">Tỷ lệ bình luận</h6>
                                                            <div class="progress">
                                                                <div class="progress-bar bg-warning" style="width: {{ min($metadata['metrics']['comment_rate'], 100) }}%"></div>
                                                            </div>
                                                            <small class="text-muted">{{ $metadata['metrics']['comment_rate'] }}%</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-4">
                                                    <div class="card">
                                                        <div class="card-body text-center">
                                                            <h6 class="mb-2">Tỷ lệ mua hàng</h6>
                                                            <div class="progress">
                                                                <div class="progress-bar bg-success" style="width: {{ min($metadata['metrics']['purchase_rate'], 100) }}%"></div>
                                                            </div>
                                                            <small class="text-muted">{{ $metadata['metrics']['purchase_rate'] }}%</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Other metadata sections (existing) -->
                                        <div class="row mt-4">
                                            <!-- Story Status at transfer time -->
                                            @if(isset($metadata['story_status']))
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="card h-100">
                                                        <div class="card-body text-center">
                                                            <h6 class="mb-2">Trạng thái truyện</h6>
                                                            <span class="badge bg-gradient-info">{{ $metadata['story_status'] }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            @if(isset($metadata['story_type']))
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="card h-100">
                                                        <div class="card-body text-center">
                                                            <h6 class="mb-2">Loại truyện</h6>
                                                            <span class="badge bg-gradient-secondary">{{ $metadata['story_type'] }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            @if(isset($metadata['is_18_plus']))
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="card h-100">
                                                        <div class="card-body text-center">
                                                            <h6 class="mb-2">Độ tuổi</h6>
                                                            @if($metadata['is_18_plus'])
                                                                <span class="badge bg-gradient-danger">18+</span>
                                                            @else
                                                                <span class="badge bg-gradient-success">Tất cả</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Bulk transfer info -->
                                            @if(isset($metadata['bulk_transfer_batch']) && $metadata['bulk_transfer_batch'])
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="card h-100">
                                                        <div class="card-body text-center">
                                                            <h6 class="mb-2">Batch Size</h6>
                                                            <span class="badge bg-gradient-warning">{{ $metadata['batch_size'] ?? 'N/A' }} truyện</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Timestamp -->
                                        @if(isset($metadata['captured_at']))
                                            <div class="row mt-3">
                                                <div class="col-12">
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-clock me-2"></i>
                                                        <strong>Thời gian capture metadata:</strong> {{ \Carbon\Carbon::parse($metadata['captured_at'])->format('d/m/Y H:i:s') }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Raw metadata for debugging -->
                                        <div class="mt-4">
                                            <button class="btn btn-sm bg-gradient-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#rawMetadata">
                                                <i class="fas fa-code me-1"></i>Xem Raw Metadata
                                            </button>
                                            <div class="collapse mt-3" id="rawMetadata">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <pre class="text-xs"><code>{{ json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Technical Details -->
                        <div class="col-12 mt-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-cogs me-2"></i>Thông tin kỹ thuật
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                {{-- <tr>
                                                    <td class="text-sm font-weight-bold" style="width: 150px;">User Agent:</td>
                                                    <td class="text-xs">{{ $history->user_agent ?? 'N/A' }}</td>
                                                </tr> --}}
                                                <tr>
                                                    <td class="text-sm font-weight-bold">Created At:</td>
                                                    <td class="text-sm">{{ $history->created_at->format('d/m/Y H:i:s') }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-sm font-weight-bold">Updated At:</td>
                                                    <td class="text-sm">{{ $history->updated_at->format('d/m/Y H:i:s') }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <!-- Related transfers for same story -->
                                            @php
                                                $relatedTransfers = \App\Models\StoryTransferHistory::where('story_id', $history->story_id)
                                                    ->where('id', '!=', $history->id)
                                                    ->orderBy('transferred_at', 'desc')
                                                    ->limit(5)
                                                    ->get();
                                            @endphp
                                            
                                            @if($relatedTransfers->count() > 0)
                                                <h6 class="text-sm font-weight-bold">Các lần chuyển nhượng khác của truyện này:</h6>
                                                <div class="list-group">
                                                    @foreach($relatedTransfers as $related)
                                                        <a href="{{ route('admin.story-transfer.history.show', $related) }}" 
                                                           class="list-group-item list-group-item-action p-2">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <small>
                                                                    #{{ $related->id }} - {{ $related->old_author_name }} → {{ $related->new_author_name }}
                                                                </small>
                                                                <small class="text-muted">{{ $related->transferred_at->format('d/m/Y') }}</small>
                                                            </div>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-12 mt-4">
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="{{ route('admin.story-transfer.history') }}" class="btn bg-gradient-secondary">
                                    <i class="fas fa-list me-2"></i>Danh sách lịch sử
                                </a>
                                
                                @if($history->story)
                                    <a href="{{ route('admin.story-transfer.show', $history->story) }}" class="btn bg-gradient-warning">
                                        <i class="fas fa-exchange-alt me-2"></i>Chuyển nhượng lại
                                    </a>
                                @endif

                                {{-- <button class="btn bg-gradient-info" onclick="window.print()">
                                    <i class="fas fa-print me-2"></i>In báo cáo
                                </button> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles-admin')
    <style>
        @media print {
            .btn, .card-header, .navbar, .sidebar {
                display: none !important;
            }
            .card {
                border: 1px solid #dee2e6 !important;
                box-shadow: none !important;
            }
        }
        
        .avatar {
            object-fit: cover;
        }
        
        .list-group-item:hover {
            background-color: #f8f9fa;
        }
    </style>
@endpush

@push('scripts-admin')
    <script>
        // Auto-refresh if transfer is in progress (for failed status)
        @if($history->status === 'failed')
            setTimeout(function() {
                location.reload();
            }, 30000); // Refresh every 30 seconds for failed transfers
        @endif
    </script>
@endpush