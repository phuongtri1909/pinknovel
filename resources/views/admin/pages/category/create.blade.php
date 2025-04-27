@extends('admin.layouts.app')

@section('content-auth')
<div class="row">
    <div class="col-12">
        <div class="card mb-0 mx-0 mx-md-4 mb-md-4">
            <div class="card-header pb-0 px-3">
                <h5 class="mb-0">Thêm thể loại mới</h5>
            </div>
            <div class="card-body pt-4 p-3">
                @include('admin.pages.components.success-error')

                <form action="{{ route('categories.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name">Tên thể loại</label>
                                <input type="text" name="name" id="name" class="form-control"
                                    value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="description">Mô tả</label>
                                <textarea name="description" id="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12 text-center">
                            <button type="submit" class="btn bg-gradient-primary">Lưu</button>
                            <a href="{{ route('categories.index') }}" class="btn btn-secondary">Trở về</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection