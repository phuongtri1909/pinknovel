@extends('admin.layouts.app')

@push('styles-admin')
<style>
    @media (max-width: 768px) {
        .w-md-auto {
            width: 100% !important;
        }
    }
</style>
@endpush
@section('content-auth')
    <div class="row">
        <div class="col-12">
            <div class="card mb-0 mx-0 mx-md-4 mb-md-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">Danh sách người dùng</h5>
                            <p class="text-sm mb-0">
                                Tổng số: {{ $stats['total'] }} người dùng
                                ({{ $stats['admin'] }} Admin / {{ $stats['mod'] }} Mod / {{ $stats['user'] }} User)
                            </p>
                        </div>
                        
                    </div>
                    <form action="{{ route('users.index') }}" method="GET" class="mt-3 d-flex flex-column flex-md-row gap-2">
                        <div class="d-flex flex-column flex-md-row gap-2 mb-2 mb-md-0">
                            <select name="role" class="form-select form-select-sm w-100 w-md-auto">
                                <option value="">Tất cả vai trò</option>
                                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="mod" {{ request('role') == 'mod' ? 'selected' : '' }}>Mod</option>
                                <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                            </select>
                    
                            <input type="text" name="ip" class="form-control form-control-sm w-100 w-md-auto" 
                                   placeholder="Địa chỉ IP" value="{{ request('ip') }}">
                            
                            <input type="date" name="date" class="form-control form-control-sm w-100 w-md-auto" 
                                   value="{{ request('date') }}">
                        </div>
                    
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Tìm kiếm..." value="{{ request('search') }}">
                            <button class="btn bg-gradient-primary btn-sm px-2 mb-0" type="submit">
                                <i class="fa-solid fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-body px-0 pt-0 pb-2">

                    @include('admin.pages.components.success-error')

                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Avatar
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Full name
                                    </th>
                                   
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Email
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Quyền
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        IP
                                    </th>
                                    <th 
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Số xu
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Ngày tạo
                                    </th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Hành động
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                <tr>
                                    
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            @if ($user->avatar == null)
                                                <img src="{{ asset('assets/images/avatar_default.jpg') }}" class="avatar avatar-sm me-3" alt="user1">
                                            @else
                                                <img src="{{ Storage::url($user->avatar) }}" class="avatar avatar-sm me-3" alt="user1">
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $user->name }}</p>
                                    </td>
                                
                                    <td class="text-center">
                                        <p class="text-xs font-weight-bold mb-0">{{ $user->email }}</p>
                                    </td>
                                    <td class="text-center">
                                        <p class="text-xs font-weight-bold mb-0">

                                            @if ($user->role == 'admin')
                                                <span class="badge bg-gradient-danger">{{ $user->role }}</span>
                                            @elseif ($user->role == 'mod')
                                                <span class="badge bg-gradient-info">{{ $user->role }}</span>
                                            @elseif ($user->role == 'vip')
                                                <span class="badge bg-gradient-warning">{{ $user->role }}</span>
                                            @else
                                                <span class="badge bg-gradient-success">{{ $user->role }}</span>
                                            @endif

                                        </p>
                                    </td>
                                    <td class="text-center">
                                        <p class="text-xs font-weight-bold mb-0">{{ $user->ip_address }}</p>
                                    </td>
                                    <td class="text-center">
                                        <p class="text-xs font-weight-bold mb-0">{{ $user->coins }}</p>
                                    </td>
                                    <td class="text-center">
                                        <p class="text-xs font-weight-bold mb-0">{{ $user->created_at }}</p>
                                    </td>
                                    <td class="text-center d-flex flex-column">
                                       <a href="{{ route('users.show',$user->id) }}" class="btn btn-primary btn-sm"><i class="fa-regular fa-eye"></i></a>
                                    </td>

                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <x-pagination :paginator="$users" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="actionModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="actionModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="actionModalBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="confirmAction"></button>
                </div>
            </div>
        </div>
    </div>
    
  
    <form id="actionForm" method="post" style="display: none;">
        @csrf
        <input type="hidden" name="item_id" id="formItemId">
    </form>
@endsection
@push('scripts-admin')
<script>
    
</script>
@endpush
