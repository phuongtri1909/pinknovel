@extends('admin.layouts.app')

@section('content-auth')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                        <h6 class="mb-0">Cộng/Trừ xu cho người dùng</h6>
                        <a href="{{ route('coins.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i><span class="d-none d-md-inline">Quay lại</span><span class="d-md-none">Quay lại</span>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="d-flex align-items-center">
                                <img src="{{ $user->avatar ? Storage::url($user->avatar) : asset('assets/images/avatar_default.jpg') }}" 
                                     class="rounded-circle flex-shrink-0" style="width: 60px; height: 60px; object-fit: cover;">
                                <div class="ms-3 flex-grow-1" style="min-width: 0;">
                                    <h5 class="mb-0 text-truncate" style="max-width: 100%;">{{ $user->name }}</h5>
                                    <p class="text-muted mb-0 text-truncate" style="max-width: 100%;" title="{{ $user->email }}">{{ Str::limit($user->email, 30) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end text-start">
                            <div class="bg-gradient-primary text-white p-3 rounded">
                                <h6 class="mb-0">Số xu hiện tại</h6>
                                <h3 class="mb-0">{{ number_format($user->coins) }}</h3>
                            </div>
                        </div>
                    </div>
                    
                    @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif
                    
                    <form action="{{ route('coins.store', $user->id) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount" class="form-control-label">Số xu</label>
                                    <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                           id="amount" name="amount" min="1" value="{{ old('amount', 1) }}" required>
                                    @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type" class="form-control-label">Loại giao dịch</label>
                                    <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                        <option value="add" {{ old('type') === 'add' ? 'selected' : '' }}>Cộng xu</option>
                                        <option value="subtract" {{ old('type') === 'subtract' ? 'selected' : '' }}>Trừ xu</option>
                                    </select>
                                    @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="note" class="form-control-label">Ghi chú (không bắt buộc)</label>
                                    <textarea class="form-control @error('note') is-invalid @enderror" 
                                              id="note" name="note" rows="3">{{ old('note') }}</textarea>
                                    @error('note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column flex-md-row justify-content-end gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-save me-2"></i><span class="d-none d-md-inline">Lưu giao dịch</span><span class="d-md-none">Lưu</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection