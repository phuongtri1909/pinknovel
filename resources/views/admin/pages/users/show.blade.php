@extends('admin.layouts.app')
@push('styles-admin')
    <style>
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }

        .toast {
            min-width: 250px;
        }
    </style>
@endpush
@section('content-auth')
    <div class="row">
        <div class="col-12">
            <div class="card mb-0 mx-0 mx-md-4 mb-md-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <h5 class="mb-0">Chi tiết người dùng</h5>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">Quay lại</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img src="{{ $user->avatar ? Storage::url($user->avatar) : asset('assets/images/avatar_default.jpg') }}"
                                class="rounded-circle img-fluid mb-3"
                                style="width: 150px; height: 150px; object-fit: cover;">
                            @if ($user->avatar && auth()->user()->role === 'admin')
                                <button class="btn btn-danger btn-sm" id="delete-avatar">
                                    <i class="fas fa-trash"></i> Xóa ảnh
                                </button>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <h6 class="text-sm">Tên người dùng</h6>
                                <p class="text-dark mb-0">{{ $user->name }}</p>
                            </div>
                            <div class="mb-3">
                                <h6 class="text-sm">Email</h6>
                                <p class="text-dark mb-0">{{ $user->email }}</p>
                            </div>

                            @php 
                                $superAdminEmails = explode(',', env('SUPER_ADMIN_EMAILS', 'admin@gmail.com'));
                                $isSuperAdmin = in_array(auth()->user()->email, $superAdminEmails);
                            @endphp
                            <div class="mb-3">
                                <h6 class="text-sm">Vai trò</h6>
                                @if (($isSuperAdmin && !in_array($user->email, $superAdminEmails)) || 
                                    (auth()->user()->role === 'admin' && $user->role !== 'admin' && !in_array($user->email, $superAdminEmails)))
                                    <select class="form-select form-select-sm w-auto" id="role-select">
                                        <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                                        <option value="mod" {{ $user->role === 'mod' ? 'selected' : '' }}>Mod</option>
                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                @else
                                    <p class="text-dark mb-0">{{ ucfirst($user->role) }}</p>
                                @endif
                            </div>
                            <div class="mb-3">
                                <h6 class="text-sm mb-3">Trạng thái hạn chế</h6>
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input ban-toggle" type="checkbox" data-type="login"
                                            {{ $user->ban_login ? 'checked' : '' }}>
                                        <label class="form-check-label">Cấm đăng nhập</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input ban-toggle" type="checkbox" data-type="comment"
                                            {{ $user->ban_comment ? 'checked' : '' }}>
                                        <label class="form-check-label">Cấm bình luận</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input ban-toggle" type="checkbox" data-type="rate"
                                            {{ $user->ban_rate ? 'checked' : '' }}>
                                        <label class="form-check-label">Cấm đánh giá</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input ban-toggle" type="checkbox" data-type="read"
                                            {{ $user->ban_read ? 'checked' : '' }}>
                                        <label class="form-check-label">Cấm đọc truyện</label>
                                    </div>

                                    @if (auth()->user()->role === 'admin')
                                        <div class="form-check form-switch">
                                            <input class="form-check-input ban-toggle" type="checkbox" data-type="ip"
                                                {{ $user->banned_ips()->exists() ? 'checked' : '' }}>
                                            <label class="form-check-label">Cấm IP</label>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container"></div>
@endsection

@push('scripts-admin')
    <script>
        function showToast(message, type = 'success') {
            const toast = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

            $('.toast-container').append(toast);
            const toastElement = $('.toast-container .toast').last();
            const bsToast = new bootstrap.Toast(toastElement, {
                delay: 3000
            });
            bsToast.show();

            // Remove toast after it's hidden
            toastElement.on('hidden.bs.toast', function() {
                $(this).remove();
            });
        }

        $(document).ready(function() {
            $('.ban-toggle').change(function() {
                const type = $(this).data('type');
                const value = $(this).prop('checked');
                const checkbox = $(this);

                if (type === 'ip') {
                    $.ajax({
                        url: '{{ route('users.banip', $user->id) }}',
                        type: 'POST',
                        data: {
                            ban: value,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            if (res.status === 'success') {
                                showToast(res.message, 'success');
                            }
                        },
                        error: function(xhr) {
                            showToast(xhr.responseJSON.message || 'Có lỗi xảy ra', 'error');
                            checkbox.prop('checked', !value);
                        }
                    });
                    return;
                }

                $.ajax({
                    url: '{{ route('users.update', $user->id) }}',
                    type: 'PATCH',
                    data: {
                        [`ban_${type}`]: value,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.status === 'success') {
                            showToast(res.message, 'success');
                        }
                    },
                    error: function(xhr) {
                        showToast(xhr.responseJSON.message || 'Có lỗi xảy ra', 'error');
                        checkbox.prop('checked', !value);
                    }
                });
            });
        });
    </script>


    {{-- edit role --}}
    <script>
        $(document).ready(function() {
            $('#role-select').change(function() {
                const newRole = $(this).val();
                const oldRole = $(this).find('option[selected]').val();

                if (confirm(
                        `Bạn có chắc muốn thay đổi quyền của người dùng thành ${newRole.toUpperCase()}?`)) {
                    $.ajax({
                        url: '{{ route('users.update', $user->id) }}',
                        type: 'PATCH',
                        data: {
                            role: newRole,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            if (res.status === 'success') {
                                showToast(res.message, 'success');
                            }
                        },
                        error: function(xhr) {
                            showToast(xhr.responseJSON.message || 'Có lỗi xảy ra', 'error');
                            $(this).val(oldRole);
                        }
                    });
                } else {
                    $(this).val(oldRole);
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#delete-avatar').click(function() {
                if (confirm('Bạn có chắc muốn xóa ảnh đại diện?')) {
                    $.ajax({
                        url: '{{ route('users.update', $user->id) }}',
                        type: 'PATCH',
                        data: {
                            delete_avatar: true,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            if (res.status === 'success') {
                                showToast(res.message, 'success');
                                location.reload();
                            }
                        },
                        error: function(xhr) {
                            showToast(xhr.responseJSON.message || 'Có lỗi xảy ra', 'error');
                        }
                    });
                }
            });
        });
    </script>
@endpush
