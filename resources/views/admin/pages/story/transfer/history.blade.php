@extends('admin.layouts.app')

@section('content-auth')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-0 mx-md-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">Lịch sử chuyển nhượng truyện</h5>
                            <p class="text-sm mb-0">Theo dõi tất cả các giao dịch chuyển nhượng truyện</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.story-transfer.index') }}" class="btn bg-gradient-primary btn-sm">
                                <i class="fas fa-exchange-alt me-2"></i>Chuyển nhượng truyện
                            </a>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <form method="GET" class="d-flex gap-2 flex-wrap align-items-end">
                                <div class="form-group">
                                    <label class="form-control-label text-xs">Tác giả cũ</label>
                                    <select name="old_author_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="">- Tất cả -</option>
                                        @foreach($authors as $author)
                                            <option value="{{ $author->id }}" {{ request('old_author_id') == $author->id ? 'selected' : '' }}>
                                                {{ $author->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-control-label text-xs">Tác giả mới</label>
                                    <select name="new_author_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="">- Tất cả -</option>
                                        @foreach($authors as $author)
                                            <option value="{{ $author->id }}" {{ request('new_author_id') == $author->id ? 'selected' : '' }}>
                                                {{ $author->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-control-label text-xs">Loại</label>
                                    <select name="transfer_type" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="">- Tất cả -</option>
                                        <option value="single" {{ request('transfer_type') == 'single' ? 'selected' : '' }}>Đơn lẻ</option>
                                        <option value="bulk" {{ request('transfer_type') == 'bulk' ? 'selected' : '' }}>Hàng loạt</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-control-label text-xs">Trạng thái</label>
                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="">- Tất cả -</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Thất bại</option>
                                        <option value="reverted" {{ request('status') == 'reverted' ? 'selected' : '' }}>Đã hoàn tác</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-control-label text-xs">Từ ngày</label>
                                    <input type="date" name="date_from" class="form-control form-control-sm" 
                                           value="{{ request('date_from') }}" onchange="this.form.submit()">
                                </div>

                                <div class="form-group">
                                    <label class="form-control-label text-xs">Đến ngày</label>
                                    <input type="date" name="date_to" class="form-control form-control-sm" 
                                           value="{{ request('date_to') }}" onchange="this.form.submit()">
                                </div>

                                <div class="form-group">
                                    <label class="form-control-label text-xs">Tìm kiếm</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control" name="search" 
                                               value="{{ request('search') }}" placeholder="Tìm kiếm...">
                                        <button class="btn bg-gradient-primary btn-sm px-2 mb-0" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body px-0 pt-0 pb-2">
                    @include('admin.pages.components.success-error')

                    <!-- Statistics Cards -->
                    <div class="row px-4 mb-3">
                        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="numbers">
                                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Tổng chuyển nhượng</p>
                                                <h5 class="font-weight-bolder mb-0">
                                                    {{ \App\Models\StoryTransferHistory::count() }}
                                                </h5>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                                <i class="ni ni-chart-bar-32 text-lg opacity-10"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="numbers">
                                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Tháng này</p>
                                                <h5 class="font-weight-bolder mb-0">
                                                    {{ \App\Models\StoryTransferHistory::where('transferred_at', '>=', now()->startOfMonth())->count() }}
                                                </h5>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                                <i class="ni ni-calendar-grid-58 text-lg opacity-10"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="numbers">
                                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Hàng loạt</p>
                                                <h5 class="font-weight-bolder mb-0">
                                                    {{ \App\Models\StoryTransferHistory::where('transfer_type', 'bulk')->count() }}
                                                </h5>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                                                <i class="ni ni-collection text-lg opacity-10"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-sm-6">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="numbers">
                                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Thất bại</p>
                                                <h5 class="font-weight-bolder mb-0">
                                                    {{ \App\Models\StoryTransferHistory::where('status', 'failed')->count() }}
                                                </h5>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div class="icon icon-shape bg-gradient-danger shadow text-center border-radius-md">
                                                <i class="ni ni-fat-remove text-lg opacity-10"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Truyện</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Chuyển từ</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Chuyển đến</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Loại</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Trạng thái</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Thực hiện bởi</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Thời gian</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($histories as $history)
                                    <tr>
                                        <td class="ps-4">
                                            <p class="text-xs font-weight-bold mb-0">#{{ $history->id }}</p>
                                        </td>
                                        <td>
                                            <div class="d-flex">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-xs">{{ Str::limit($history->story_title, 30) }}</h6>
                                                    @if($history->story)
                                                        <p class="text-xs text-secondary mb-0">ID: {{ $history->story->id }}</p>
                                                    @else
                                                        <p class="text-xs text-danger mb-0">Truyện đã bị xóa</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-xs">{{ $history->old_author_name }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ $history->old_author_email }}</p>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-xs">{{ $history->new_author_name }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ $history->new_author_email }}</p>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-sm {{ $history->transfer_type === 'bulk' ? 'bg-gradient-warning' : 'bg-gradient-info' }}">
                                                {{ $history->transfer_type_text }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-sm {{ $history->status_badge }}">
                                                {{ $history->status_text }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-xs">{{ $history->transferred_by_name }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ $history->time_ago }}</p>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $history->transferred_at->format('d/m/Y') }}</p>
                                            <p class="text-xs text-secondary mb-0">{{ $history->transferred_at->format('H:i:s') }}</p>
                                        </td>
                                        <td class="align-middle text-center">
                                            <a href="{{ route('admin.story-transfer.history.show', $history) }}" 
                                               class="btn btn-link text-info text-gradient px-3 mb-0">
                                                <i class="fas fa-eye me-2"></i>Chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">Không có lịch sử chuyển nhượng nào</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 pt-4">
                        {{ $histories->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection