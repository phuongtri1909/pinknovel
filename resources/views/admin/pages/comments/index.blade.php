@extends('admin.layouts.app')

@push('styles-admin')
<!-- Thêm các style tùy chỉnh nếu cần -->
@endpush

@section('content-auth')
<div class="row">
    <div class="col-12">
        <div class="card mb-0 mx-0 mx-md-4 mb-md-4">
            <div class="card-header pb-0">
                <div class="d-flex flex-row justify-content-between">
                    <div>
                        <h5 class="mb-0">Danh sách bình luận: {{ $story->title }}</h5>
                    </div>
                    <form method="GET" class="mt-3 d-flex flex-column flex-md-row gap-2">
                        <div class="d-flex flex-column flex-md-row gap-2 mb-2 mb-md-0">
                            <select name="user" class="form-select form-select-sm w-100 w-md-auto">
                                <option value="">Tất cả người dùng</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            
                            <input type="date" name="date" 
                                   class="form-control form-control-sm w-100 w-md-auto" 
                                   value="{{ request('date') }}">
                        </div>
                
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" 
                                   name="search" placeholder="Nội dung..." 
                                   value="{{ request('search') }}">
                            <button class="btn bg-gradient-primary btn-sm px-2 mb-0" type="submit">
                                <i class="fa-solid fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">

                @include('admin.pages.components.success-error')

                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Thành viên
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                   Bình luận
                                </th>

                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Ngày tạo
                                </th>
                                
                                <th
                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Hành động
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($comments as $item)
                            <tr>
                                <td class="ps-4">
                                    <p class="text-xs font-weight-bold mb-0">
                                        @if($item->user)
                                        <a href="{{ route('users.show',$item->user->id) }}">{{ $item->user->name }}</a>
                                        
                                        @else
                                            Khách hàng không tồn tại
                                        @endif
                                    </p>
                                </td>

                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $item->comment }}</p>
                                </td>

                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $item->created_at }}</p>
                                </td>
                                
                                <td class="text-center d-flex flex-column">

                                    @include('admin.pages.components.delete-form', ['id' =>  $item->id, 'route' => route('comments.destroy', $item->id)])
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <x-pagination :paginator="$comments" />

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts-admin')

@endpush
