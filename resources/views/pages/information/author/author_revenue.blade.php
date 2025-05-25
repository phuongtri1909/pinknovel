@extends('layouts.information')

@section('info_title', 'Doanh thu tác giả')
@section('info_description', 'Theo dõi doanh thu của bạn trên ' . request()->getHost())
@section('info_keyword', 'doanh thu, tác giả, xu, truyện, ' . request()->getHost())
@section('info_section_title', 'Doanh thu tác giả')
@section('info_section_desc', 'Theo dõi doanh thu tháng ' . date('m/Y') . ' và lịch sử giao dịch')

@push('info_styles')
<style>
    .revenue-card {
        border-radius: 12px;
        padding: 25px;
        height: 170px;
        position: relative;
        overflow: hidden;
        transition: all 0.4s ease;
        box-shadow: 0 6px 15px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        margin-bottom: 25px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        background-color: white;
    }
    
    .revenue-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    }
    
    .revenue-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        opacity: 0.1;
        background: rgba(255, 255, 255, 0.8);
        transition: all 0.5s ease;
    }
    
    .revenue-card:hover::before {
        transform: scale(6);
        opacity: 0.15;
    }
    
    .revenue-card-title {
        color: #333;
        font-size: 16px;
        font-weight: 600;
        letter-spacing: 0.5px;
        position: relative;
        z-index: 1;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        padding-bottom: 10px;
        margin-bottom: 15px;
    }
    
    .revenue-card-value {
        color: #333;
        font-size: 28px;
        font-weight: 700;
        display: flex;
        align-items: center;
        position: relative;
        z-index: 1;
        margin: 12px 0;
    }
    
    .revenue-card-value i {
        margin-right: 15px;
        font-size: 24px;
        padding: 15px;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }
    
    .card-total {
        background: linear-gradient(135deg, #fff, #fff);
        border: 1px solid #20b2aa;
        box-shadow: 0 6px 15px rgba(32, 178, 170, 0.1);
    }
    
    .card-chapter {
        background: linear-gradient(135deg, #fff, #fff);
        border: 1px solid #7bc5ae;
        box-shadow: 0 6px 15px rgba(123, 197, 174, 0.1);
    }
    
    .card-story {
        background: linear-gradient(135deg, #fff, #fff);
        border: 1px solid #e9b384;
        box-shadow: 0 6px 15px rgba(233, 179, 132, 0.1);
    }
    
    .card-comparison {
        background: linear-gradient(135deg, #fff, #fff);
        border: 1px solid #64b5f6;
        box-shadow: 0 6px 15px rgba(100, 181, 246, 0.1);
    }
    
    .card-total .revenue-card-value i {
        background-color: rgba(32, 178, 170, 0.1);
        color: #20b2aa;
    }
    
    .card-chapter .revenue-card-value i {
        background-color: rgba(123, 197, 174, 0.1);
        color: #7bc5ae;
    }
    
    .card-story .revenue-card-value i {
        background-color: rgba(233, 179, 132, 0.1);
        color: #e9b384;
    }
    
    .card-comparison .revenue-card-value i {
        background-color: rgba(100, 181, 246, 0.1);
        color: #64b5f6;
    }
    
    .revenue-card-footer {
        font-size: 13px;
        color: #777;
        font-weight: 500;
        position: relative;
        z-index: 1;
        border-top: 1px solid rgba(0, 0, 0, 0.08);
        padding-top: 10px;
        margin-top: 5px;
    }
    
    .revenue-filter {
        background-color: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        margin-bottom: 35px;
        transition: all 0.3s ease;
    }
    
    .revenue-filter:hover {
        box-shadow: 0 12px 25px rgba(0,0,0,0.08);
    }
    
    .chart-container {
        background-color: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        margin-top: 25px;
        position: relative;
        height: 450px;
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    .chart-container:hover {
        box-shadow: 0 12px 25px rgba(0,0,0,0.08);
    }
    
    .chart-loading {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: rgba(255, 255, 255, 0.85);
        z-index: 10;
        border-radius: 16px;
    }
    
    .transaction-history {
        background-color: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        margin-top: 35px;
        transition: all 0.3s ease;
    }
    
    .transaction-history:hover {
        box-shadow: 0 12px 25px rgba(0,0,0,0.08);
    }
    
    .transaction-item {
        padding: 15px 0;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    
    .transaction-item:hover {
        background-color: rgba(0,0,0,0.02);
        border-radius: 8px;
        padding-left: 10px;
        padding-right: 10px;
    }
    
    .transaction-item:last-child {
        border-bottom: none;
    }
    
    .transaction-date {
        font-size: 12px;
        color: #777;
    }
    
    .transaction-amount {
        font-weight: 700;
        color: #20b2aa;
    }
    
    .transaction-title {
        font-weight: 600;
    }
    
    .transaction-type {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .type-chapter {
        background-color: rgba(123, 197, 174, 0.2);
        color: #7bc5ae;
    }
    
    .type-story {
        background-color: rgba(233, 179, 132, 0.2);
        color: #e9b384;
    }
    
    .filter-label {
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
    }
    
    .form-select {
        border-radius: 10px;
        padding: 10px 15px;
        border-color: rgba(0,0,0,0.1);
        box-shadow: none;
        transition: all 0.3s ease;
    }
    
    .form-select:focus {
        border-color: #57c5b6;
        box-shadow: 0 0 0 0.25rem rgba(32, 178, 170, 0.25);
    }
    
    .text-success, .text-success i {
        color: #2ecc71 !important;
    }
    
    .text-danger, .text-danger i {
        color: #e74c3c !important;
    }
    
    .card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .card:hover {
        box-shadow: 0 12px 25px rgba(0,0,0,0.08);
    }
    
    .card-header {
        border-bottom: 1px solid rgba(0,0,0,0.05);
        padding: 18px 25px;
    }
    
    .card-header h5 {
        font-weight: 600;
        color: #333;
        margin-bottom: 0;
    }
    
    .card-body {
        padding: 25px;
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table th {
        font-weight: 600;
        color: #555;
        border-bottom-width: 1px;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .table a {
        color: #20b2aa;
        transition: all 0.3s ease;
    }
    
    .table a:hover {
        color: #57c5b6;
    }
    
    @media (max-width: 767px) {
        .revenue-card {
            height: auto;
            min-height: 150px;
        }
    }
    
    .card-comparison .revenue-card-footer .text-success {
        background-color: rgba(46, 204, 113, 0.1);
        padding: 5px 10px;
        border-radius: 20px;
        display: inline-block;
    }
    
    .card-comparison .revenue-card-footer .text-danger {
        background-color: rgba(231, 76, 60, 0.1);
        padding: 5px 10px;
        border-radius: 20px;
        display: inline-block;
    }
</style>
@endpush

@section('info_content')
<div class="container">
    <div class="row">
        <!-- Tổng doanh thu -->
        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
            <div class="revenue-card card-total">
                <div class="revenue-card-title">
                    Tổng doanh thu
                </div>
                <div class="revenue-card-value">
                    <i class="fas fa-chart-line"></i>
                    <span id="total-revenue">{{ number_format($grandTotal) }} xu</span>
                </div>
                <div class="revenue-card-footer">
                    Tổng doanh thu từ trước đến nay
                </div>
            </div>
        </div>
        
        <!-- Doanh thu tháng trước -->
        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
            <div class="revenue-card card-comparison">
                <div class="revenue-card-title">
                    So sánh với tháng trước
                </div>
                <div class="revenue-card-value">
                    <i class="fas fa-balance-scale"></i>
                    <span>{{ number_format($lastMonthRevenue ?? 0) }} xu</span>
                </div>
                <div class="revenue-card-footer">
                    @if(isset($revenueChangePercent) && $revenueChangePercent != 0)
                        @if($revenueIncreased)
                            <span class="text-success">
                                <i class="fas fa-arrow-up"></i> Tăng {{ $revenueChangePercent }}%
                            </span>
                        @else
                            <span class="text-danger">
                                <i class="fas fa-arrow-down"></i> Giảm {{ abs($revenueChangePercent) }}%
                            </span>
                        @endif
                    @else
                        Không thay đổi
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Doanh thu từ chương -->
        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
            <div class="revenue-card card-chapter">
                <div class="revenue-card-title">
                    Doanh thu từ chương
                </div>
                <div class="revenue-card-value">
                    <i class="fas fa-book-open"></i>
                    <span id="chapter-revenue">Đang tải...</span>
                </div>
                <div class="revenue-card-footer">
                    Doanh thu từ việc bán chương trong kỳ
                </div>
            </div>
        </div>
        
        <!-- Doanh thu từ trọn bộ -->
        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
            <div class="revenue-card card-story">
                <div class="revenue-card-title">
                    Doanh thu từ trọn bộ
                </div>
                <div class="revenue-card-value">
                    <i class="fas fa-book"></i>
                    <span id="story-revenue">Đang tải...</span>
                </div>
                <div class="revenue-card-footer">
                    Doanh thu từ việc bán trọn bộ trong kỳ
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bộ lọc -->
    <div class="revenue-filter">
        <div class="row">
            <div class="col-md-4">
                <div class="filter-label">Chọn năm</div>
                <select id="year-filter" class="form-select">
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <div class="filter-label">Chọn tháng</div>
                <select id="month-filter" class="form-select">
                    <option value="">Tất cả các tháng</option>
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $i == date('m') ? 'selected' : '' }}>Tháng {{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-4">
                <div class="filter-label">Thống kê theo</div>
                <select id="chart-type" class="form-select">
                    <option value="bar">Biểu đồ cột</option>
                    <option value="line">Biểu đồ đường</option>
                </select>
            </div>
        </div>
    </div>
    
    <!-- Biểu đồ -->
    <div class="chart-container">
        <div class="chart-loading" id="chart-loading">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
            </div>
        </div>
        <canvas id="revenue-chart"></canvas>
    </div>
    
    <!-- Lịch sử giao dịch -->
    <div class="transaction-history">
        <h5 class="mb-4">Lịch sử giao dịch gần đây</h5>
        <div id="transaction-list">
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Đang tải...</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Nội dung bán chạy -->
    <div class="row mt-4">
        <!-- Truyện bán chạy -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Truyện bán chạy nhất</h5>
                </div>
                <div class="card-body">
                    @if(count($topStories) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tên truyện</th>
                                        <th>Lượt mua</th>
                                        <th>Doanh thu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topStories as $index => $story)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <a href="{{ route('show.page.story', $story->slug) }}" class="text-decoration-none">
                                                    {{ $story->title }}
                                                </a>
                                            </td>
                                            <td>{{ $story->purchase_count }}</td>
                                            <td>{{ number_format($story->total_revenue) }} xu</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-book fa-2x mb-2"></i>
                            <p>Chưa có dữ liệu bán truyện trọn bộ</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Chương bán chạy -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Chương bán chạy nhất</h5>
                </div>
                <div class="card-body">
                    @if(count($topChapters) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tên chương</th>
                                        <th>Lượt mua</th>
                                        <th>Doanh thu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topChapters as $index => $chapter)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <a href="{{ route('chapter', [$chapter->story_slug, $chapter->slug]) }}" class="text-decoration-none">
                                                    {{ $chapter->title }}
                                                </a>
                                                <div class="small text-muted">
                                                    {{ $chapter->story_title }}
                                                </div>
                                            </td>
                                            <td>{{ $chapter->purchase_count }}</td>
                                            <td>{{ number_format($chapter->total_revenue) }} xu</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-book-open fa-2x mb-2"></i>
                            <p>Chưa có dữ liệu bán chương</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('info_scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    let revenueChart = null;
    
    // Hiệu ứng đếm số
    function animateNumber(element, targetValue) {
        // Đảm bảo targetValue là số nguyên
        targetValue = parseInt(targetValue) || 0;
        
        // Reset lại giá trị trước khi bắt đầu animation
        element.textContent = '0 xu';
        
        const duration = 1500;
        const frameDuration = 1000 / 60;
        const totalFrames = Math.round(duration / frameDuration);
        let frame = 0;
        const initialValue = 0;
        const valueIncrement = (targetValue - initialValue) / totalFrames;
        
        const counter = setInterval(() => {
            frame++;
            const newValue = Math.floor(initialValue + (valueIncrement * frame));
            element.textContent = newValue.toLocaleString() + ' xu';
            
            if (frame === totalFrames) {
                clearInterval(counter);
                element.textContent = targetValue.toLocaleString() + ' xu';
            }
        }, frameDuration);
    }
    
    // Hiệu ứng hover cho card
    function initCardHoverEffects() {
        const cards = document.querySelectorAll('.revenue-card');
        
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                // Thêm hiệu ứng khi hover
                this.style.transform = 'translateY(-8px)';
            });
            
            card.addEventListener('mouseleave', function() {
                // Loại bỏ hiệu ứng khi rời đi
                this.style.transform = 'translateY(0)';
            });
        });
    }
    
    // Hàm tải dữ liệu và cập nhật biểu đồ
    function loadRevenueData() {
        const year = document.getElementById('year-filter').value;
        const month = document.getElementById('month-filter').value;
        const chartType = document.getElementById('chart-type').value;
        
        // Hiển thị loading
        document.getElementById('chart-loading').style.display = 'flex';
        
        // Gọi API để lấy dữ liệu
        fetch(`{{ route('user.author.revenue.data') }}?year=${year}&month=${month}`)
            .then(response => response.json())
            .then(data => {
                // Cập nhật biểu đồ
                updateChart(data, chartType);
                
                // Cập nhật thông tin doanh thu
                updateRevenueSummary(data);
                
                // Ẩn loading
                document.getElementById('chart-loading').style.display = 'none';
                
                // Hiệu ứng xuất hiện dần dần cho biểu đồ
                const chartContainer = document.getElementById('revenue-chart').parentElement;
                chartContainer.style.opacity = '0';
                chartContainer.style.display = 'block';
                
                setTimeout(() => {
                    chartContainer.style.transition = 'opacity 0.5s ease';
                    chartContainer.style.opacity = '1';
                }, 100);
            })
            .catch(error => {
                console.error('Error fetching revenue data:', error);
                // Ẩn loading
                document.getElementById('chart-loading').style.display = 'none';
            });
    }
    
    // Hàm cập nhật biểu đồ
    function updateChart(data, chartType) {
        const ctx = document.getElementById('revenue-chart').getContext('2d');
        
        // Nếu biểu đồ đã tồn tại, hủy nó
        if (revenueChart) {
            revenueChart.destroy();
        }
        
        // Kiểm tra dữ liệu
        if (!data || !data.datasets || data.datasets.length === 0 || !data.labels) {
            // Hiển thị thông báo không có dữ liệu
            const chartContainer = document.getElementById('revenue-chart').parentElement;
            chartContainer.innerHTML = '<div class="text-center py-5"><i class="fas fa-chart-bar fa-3x text-muted mb-3"></i><p>Không có dữ liệu doanh thu trong khoảng thời gian này</p></div>';
            return;
        }
        
        // Tạo biểu đồ mới
        revenueChart = new Chart(ctx, {
            type: chartType,
            data: {
                labels: data.labels,
                datasets: data.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1500,
                    easing: 'easeOutQuart'
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' xu';
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw.toLocaleString() + ' xu';
                            }
                        }
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                }
            }
        });
    }
    
    // Hàm cập nhật thông tin tổng hợp doanh thu
    function updateRevenueSummary(data) {
        let totalChapterRevenue = 0;
        let totalStoryRevenue = 0;
        
        // Tính tổng doanh thu từ chương
        if (data.datasets && data.datasets.length > 0) {
            totalChapterRevenue = data.datasets[0].data.reduce((sum, value) => sum + value, 0);
            if (data.datasets.length > 1) {
                totalStoryRevenue = data.datasets[1].data.reduce((sum, value) => sum + value, 0);
            }
        }
        
        // Cập nhật UI với hiệu ứng đếm số
        const chapterRevenueElement = document.getElementById('chapter-revenue');
        const storyRevenueElement = document.getElementById('story-revenue');
        
        // Hiệu ứng đếm số với giá trị đúng
        animateNumber(chapterRevenueElement, totalChapterRevenue);
        animateNumber(storyRevenueElement, totalStoryRevenue);
    }
    
    // Hàm tải lịch sử giao dịch
    function loadTransactionHistory() {
        document.getElementById('transaction-list').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Đang tải...</span>
                </div>
            </div>
        `;
        
        // Hiển thị lịch sử giao dịch giả định
        setTimeout(() => {
            const transactionList = document.getElementById('transaction-list');
            transactionList.style.opacity = '0';
            
            transactionList.innerHTML = `
                <div class="transaction-item">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="transaction-date">25/05/2023 08:45</div>
                        </div>
                        <div class="col-md-6">
                            <div class="transaction-title">Mua chương 10: Cuộc chiến bắt đầu</div>
                            <div class="transaction-type type-chapter">Chương lẻ</div>
                        </div>
                        <div class="col-md-2 text-end">
                            <div class="transaction-amount">+50 xu</div>
                        </div>
                        <div class="col-md-2 text-end">
                            <a href="#" class="btn btn-sm btn-outline-primary">Chi tiết</a>
                        </div>
                    </div>
                </div>
                <div class="transaction-item">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="transaction-date">24/05/2023 16:20</div>
                        </div>
                        <div class="col-md-6">
                            <div class="transaction-title">Mua trọn bộ: Vũ trụ song song</div>
                            <div class="transaction-type type-story">Trọn bộ</div>
                        </div>
                        <div class="col-md-2 text-end">
                            <div class="transaction-amount">+500 xu</div>
                        </div>
                        <div class="col-md-2 text-end">
                            <a href="#" class="btn btn-sm btn-outline-primary">Chi tiết</a>
                        </div>
                    </div>
                </div>
                <div class="transaction-item">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="transaction-date">22/05/2023 10:15</div>
                        </div>
                        <div class="col-md-6">
                            <div class="transaction-title">Mua chương 5: Bí mật được hé lộ</div>
                            <div class="transaction-type type-chapter">Chương lẻ</div>
                        </div>
                        <div class="col-md-2 text-end">
                            <div class="transaction-amount">+30 xu</div>
                        </div>
                        <div class="col-md-2 text-end">
                            <a href="#" class="btn btn-sm btn-outline-primary">Chi tiết</a>
                        </div>
                    </div>
                </div>
            `;
            
            // Hiệu ứng xuất hiện dần dần
            setTimeout(() => {
                transactionList.style.transition = 'opacity 0.5s ease';
                transactionList.style.opacity = '1';
                
                // Thêm hiệu ứng xuất hiện lần lượt cho từng item
                const items = transactionList.querySelectorAll('.transaction-item');
                items.forEach((item, index) => {
                    item.style.opacity = '0';
                    item.style.transform = 'translateY(20px)';
                    item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0)';
                    }, 100 * (index + 1));
                });
            }, 100);
        }, 1000);
    }
    
    // Hiệu ứng chuyển động nổi cho các card
    function floatingAnimation() {
        const cards = document.querySelectorAll('.revenue-card');
        
        cards.forEach((card, index) => {
            const delay = index * 200;
            
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
                
                // Hiệu ứng nổi liên tục
                setTimeout(() => {
                    card.style.animation = `floating-${index + 1} 3s ease-in-out infinite`;
                    const keyframes = `
                        @keyframes floating-${index + 1} {
                            0% { transform: translateY(0); }
                            50% { transform: translateY(-8px); }
                            100% { transform: translateY(0); }
                        }
                    `;
                    const styleSheet = document.createElement('style');
                    styleSheet.innerHTML = keyframes;
                    document.head.appendChild(styleSheet);
                }, 500);
            }, delay);
        });
    }
    
    // Xử lý sự kiện khi trang được tải
    document.addEventListener('DOMContentLoaded', function() {
        // Khởi chạy hiệu ứng nổi cho các card
        floatingAnimation();
        
        // Khởi tạo hiệu ứng hover cho card
        initCardHoverEffects();
        
        // Tải dữ liệu ban đầu
        loadRevenueData();
        loadTransactionHistory();
        
        // Hiệu ứng cho tổng doanh thu
        const totalRevenueElement = document.getElementById('total-revenue');
        // Lấy giá trị số từ chuỗi, loại bỏ định dạng và từ "xu"
        const totalRevenueText = totalRevenueElement.textContent.replace(/[^\d]/g, '');
        const totalRevenueValue = parseInt(totalRevenueText) || 0;
        
        // Hiệu ứng đếm số cho tổng doanh thu
        animateNumber(totalRevenueElement, totalRevenueValue);
        
        // Xử lý sự kiện thay đổi bộ lọc
        document.getElementById('year-filter').addEventListener('change', loadRevenueData);
        document.getElementById('month-filter').addEventListener('change', loadRevenueData);
        document.getElementById('chart-type').addEventListener('change', loadRevenueData);
    });
</script>
@endpush 