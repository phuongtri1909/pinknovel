@extends('admin.layouts.app')

@section('title', 'Quản lý duyệt truyện')

@push('styles-admin')
    <style>
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .status-published {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-draft {
            background-color: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
        }

        .tab-count {
            background-color: rgba(0, 0, 0, 0.1);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
            margin-left: 5px;
        }

        .nav-link.active .tab-count {
            background-color: rgba(255, 255, 255, 0.2);
        }
    </style>
@endpush

@section('content-auth')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Quản lý duyệt truyện</h4>
                </div>
                <div class="card-body">
                    <!-- Filter tabs -->
                    <ul class="nav nav-tabs mb-3">
                        <li class="nav-item">
                            <a class="nav-link {{ !request('status') || request('status') == 'pending' ? 'active' : '' }}"
                                href="{{ route('admin.story-reviews.index', ['status' => 'pending']) }}">
                                Chờ duyệt <span class="tab-count">{{ $pendingCount }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('status') == 'published' ? 'active' : '' }}"
                                href="{{ route('admin.story-reviews.index', ['status' => 'published']) }}">
                                Đã duyệt <span class="tab-count">{{ $publishedCount }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('status') == 'rejected' ? 'active' : '' }}"
                                href="{{ route('admin.story-reviews.index', ['status' => 'rejected']) }}">
                                Từ chối <span class="tab-count">{{ $rejectedCount }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('status') == 'draft' ? 'active' : '' }}"
                                href="{{ route('admin.story-reviews.index', ['status' => 'draft']) }}">
                                Nháp <span class="tab-count">{{ $draftCount }}</span>
                            </a>
                        </li>
                    </ul>

                    <!-- Search form -->
                    <form action="{{ route('admin.story-reviews.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <input type="text" class="form-control" name="search"
                                    placeholder="Tìm kiếm theo tiêu đề hoặc tên tác giả" value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <select class="form-control" name="author_id">
                                    <option value="">-- Chọn tác giả --</option>
                                    @foreach ($authors as $author)
                                        <option value="{{ $author->id }}"
                                            {{ request('author_id') == $author->id ? 'selected' : '' }}>
                                            {{ $author->name }} ({{ ucfirst($author->role) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <input type="date" class="form-control" name="submitted_date" placeholder="Ngày gửi"
                                    value="{{ request('submitted_date') }}">
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="hidden" name="status" value="{{ request('status', 'pending') }}">
                                <button class="btn btn-outline-secondary w-100 mb-0" type="submit">
                                    <i class="fas fa-search"></i> Tìm kiếm
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Stories table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Bìa</th>
                                    <th>Tiêu đề</th>
                                    <th>Tác giả</th>
                                    <th>Ngày gửi</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($stories as $story)
                                    <tr>
                                        <td>{{ $story->id }}</td>
                                        <td>
                                            <img src="{{ Storage::url($story->cover_thumbnail) }}"
                                                alt="{{ $story->title }}" class="img-thumbnail" style="max-width: 60px;">
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <strong>{{ $story->title }}</strong>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span>{{ $story->user->name }}</span>
                                                <small class="text-muted">{{ $story->user->email }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold">
                                                {{ \Carbon\Carbon::parse($story->submitted_at)->format('H:i:s d/m/Y') }}
                                            </div>
                                        </td>
                                        <td>
                                            @if ($story->status == 'pending')
                                                <span class="status-badge status-pending">Chờ duyệt</span>
                                            @elseif ($story->status == 'published')
                                                <span class="status-badge status-published">Đã xuất bản</span>
                                            @elseif ($story->status == 'rejected')
                                                <span class="status-badge status-rejected">Từ chối</span>
                                            @elseif ($story->status == 'draft')
                                                <span class="status-badge status-draft">Nháp</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.story-reviews.show', $story) }}"
                                                class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Không có truyện nào</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        <x-pagination :paginator="$stories" />
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
