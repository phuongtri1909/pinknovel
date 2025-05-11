@extends('layouts.information')

@section('info_title', 'Khu vực tác giả')
@section('info_description', 'Khu vực tác giả của bạn trên ' . request()->getHost())
@section('info_keyword', 'quản lý truyện, đăng truyện, tác giả, ' . request()->getHost())
@section('info_section_title', 'Khu vực tác giả')
@section('info_section_desc', 'Quản lý truyện và theo dõi doanh thu')

@push('styles')
<style>
    .author-dashboard-card {
        border-radius: 16px;
        padding: 20px;
        height: 120px;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        cursor: pointer;
    }
    
    .author-dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    
    .card-revenue {
        background: linear-gradient(135deg, #20b2aa, #57c5b6);
    }
    
    .card-stories {
        background: linear-gradient(135deg, #7bc5ae, #9ed2be);
    }
    
    .card-pending {
        background: linear-gradient(135deg, #e9b384, #f3deba);
    }
    
    .card-support {
        background: linear-gradient(135deg, #78c1f3, #9be8ff);
    }
    
    .author-card-title {
        color: rgba(255, 255, 255, 0.8);
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .author-card-value {
        color: white;
        font-size: 28px;
        font-weight: 600;
        display: flex;
        align-items: center;
    }
    
    .author-card-value i {
        margin-right: 10px;
        font-size: 24px;
    }
    
    .author-button {
        border-radius: 30px;
        padding: 12px 24px;
        font-weight: 500;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background-color: white;
        border: none;
        color: #333;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    
    .author-button:hover {
        box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .author-button i {
        font-size: 18px;
    }
    
    .author-button.primary {
        background: linear-gradient(135deg, #20b2aa, #57c5b6);
        color: white;
    }
    
    .author-button.secondary {
        background: white;
        color: #333;
    }
    
    .revenue-chart {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        margin-top: 30px;
    }
    
    .revenue-chart:hover {
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    }
    
    .revenue-chart-title {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .revenue-chart-title i {
        font-size: 20px;
        margin-right: 10px;
        color: #20b2aa;
    }
    
    .loading-animation {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 200px;
        color: #999;
    }
    
    .action-buttons {
        animation: fadeIn 0.5s ease-in-out;
    }
    
    @keyframes fadeIn {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes pulseGlow {
        0% { box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        50% { box-shadow: 0 4px 20px rgba(32, 178, 170, 0.3); }
        100% { box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    }

    .stats-widget {
        margin-bottom: 30px;
        animation: fadeIn 0.6s ease-in-out;
    }

    .recent-activities {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        margin-top: 30px;
    }

    .activity-item {
        padding: 12px 0;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        color: white;
    }

    .bg-view {
        background: linear-gradient(135deg, #78c1f3, #9be8ff);
    }

    .bg-comment {
        background: linear-gradient(135deg, #e9b384, #f3deba);
    }

    .bg-update {
        background: linear-gradient(135deg, #7bc5ae, #9ed2be);
    }
</style>
@endpush

@section('info_content')
    <div class="row g-4 mb-4">
        <!-- Doanh thu tháng -->
        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="#" class="text-decoration-none">
                <div class="author-dashboard-card card-revenue">
                    <div class="author-card-title">
                        Doanh thu tháng
                        <i class="fas fa-chevron-right"></i>
                    </div>
                    <div class="author-card-value">
                        <i class="fas fa-chart-line"></i>
                        <span>+0</span>
                    </div>
                </div>
            </a>
        </div>
        
        <!-- Truyện của tôi -->
        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="{{ route('user.author.stories') }}" class="text-decoration-none">
                <div class="author-dashboard-card card-stories">
                    <div class="author-card-title">
                        Truyện của tôi
                        <i class="fas fa-chevron-right"></i>
                    </div>
                    <div class="author-card-value">
                        <i class="fas fa-book"></i>
                        <span>{{ $stories->total() ?? 0 }}</span>
                    </div>
                </div>
            </a>
        </div>
        
        <!-- Đang quản lý -->
        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="#" class="text-decoration-none">
                <div class="author-dashboard-card card-pending">
                    <div class="author-card-title">
                        Đang quản lý
                        <i class="fas fa-chevron-right"></i>
                    </div>
                    <div class="author-card-value">
                        <i class="fas fa-tasks"></i>
                        <span>{{ $pendingCount ?? 1 }}</span>
                    </div>
                </div>
            </a>
        </div>
        
        <!-- Yêu cầu hỗ trợ -->
        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="#" class="text-decoration-none">
                <div class="author-dashboard-card card-support">
                    <div class="author-card-title">
                        Yêu cầu hỗ trợ
                        <i class="fas fa-chevron-right"></i>
                    </div>
                    <div class="author-card-value">
                        <i class="fas fa-headset"></i>
                        <span>0</span>
                    </div>
                </div>
            </a>
        </div>
    </div>
    
    <div class="action-buttons d-flex justify-content-between my-4">
        <a href="{{ route('user.author.stories.create') }}" class="author-button primary text-decoration-none">
            <i class="fas fa-plus"></i>
            <span>ĐĂNG TRUYỆN</span>
        </a>
        
        <a href="#" class="author-button secondary text-decoration-none">
            <i class="fas fa-calendar-alt"></i>
            <span>SỰ KIỆN</span>
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="revenue-chart">
                <div class="revenue-chart-title">
                    <i class="fas fa-chart-bar"></i>
                    <h5 class="mb-0">Biểu đồ hiệu suất doanh thu tháng</h5>
                </div>
                <div class="loading-animation">
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="fas fa-chart-line fa-3x"></i>
                        </div>
                        <p>Chưa có dữ liệu</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="recent-activities">
                <h5 class="mb-3">Hoạt động gần đây</h5>
                
                @if(isset($recentActivities) && count($recentActivities) > 0)
                    @foreach($recentActivities as $activity)
                        <div class="activity-item">
                            <div class="activity-icon {{ $activity->type == 'view' ? 'bg-view' : ($activity->type == 'comment' ? 'bg-comment' : 'bg-update') }}">
                                <i class="fas {{ $activity->type == 'view' ? 'fa-eye' : ($activity->type == 'comment' ? 'fa-comment' : 'fa-sync') }}"></i>
                            </div>
                            <div>
                                <div>{{ $activity->message }}</div>
                                <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-history fa-2x mb-2"></i>
                        <p>Chưa có hoạt động nào</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="stats-widget mt-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Thống kê nhanh</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 col-6 mb-3 mb-md-0">
                        <div class="h2 fw-bold text-primary">{{ $totalViews ?? 0 }}</div>
                        <div class="text-muted">Lượt xem</div>
                    </div>
                    <div class="col-md-3 col-6 mb-3 mb-md-0">
                        <div class="h2 fw-bold text-success">{{ $totalChapters ?? 0 }}</div>
                        <div class="text-muted">Chương</div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="h2 fw-bold text-info">{{ $totalComments ?? 0 }}</div>
                        <div class="text-muted">Bình luận</div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="h2 fw-bold text-warning">{{ $totalFollowers ?? 0 }}</div>
                        <div class="text-muted">Người theo dõi</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('info_scripts')
<script>
    // Thêm hiệu ứng pulse cho các card
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.author-dashboard-card');
        
        cards.forEach((card, index) => {
            // Thêm delay cho mỗi card để tạo hiệu ứng lần lượt
            setTimeout(() => {
                card.style.animation = 'pulseGlow 2s infinite';
            }, index * 300);
        });
        
        // Thêm sự kiện hover cho các card
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.animation = 'none';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.animation = 'pulseGlow 2s infinite';
            });
        });
    });
</script>
@endpush