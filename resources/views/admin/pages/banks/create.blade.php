@extends('admin.layouts.app')

@section('title', 'Thêm Ngân hàng mới')

@section('content-auth')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Thêm Ngân hàng mới</h4>
                        <a href="{{ route('admin.banks.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @include('admin.pages.components.success-error')
                    
                    <form action="{{ route('admin.banks.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Tên Ngân hàng <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="code">Mã Ngân hàng <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                           id="code" name="code" value="{{ old('code') }}" required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Ví dụ: BIDV, VCB, TPB, MB, ...</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="account_number">Số tài khoản <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('account_number') is-invalid @enderror" 
                                           id="account_number" name="account_number" value="{{ old('account_number') }}" required>
                                    @error('account_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="account_name">Chủ tài khoản <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('account_name') is-invalid @enderror" 
                                           id="account_name" name="account_name" value="{{ old('account_name') }}" required>
                                    @error('account_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="logo">Logo</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input @error('logo') is-invalid @enderror" 
                                               id="logo" name="logo" accept="image/*">
                                        <label class="custom-file-label" for="logo">Chọn file</label>
                                        @error('logo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted">Kích thước tối đa: 2MB. Định dạng: JPG, PNG, GIF</small>
                                    
                                    <div class="mt-2 d-none" id="logo-preview-container">
                                        <img src="" alt="Logo Preview" id="logo-preview" class="img-thumbnail" style="max-height: 100px;">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="qr_code">Mã QR</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input @error('qr_code') is-invalid @enderror" 
                                               id="qr_code" name="qr_code" accept="image/*">
                                        <label class="custom-file-label" for="qr_code">Chọn file</label>
                                        @error('qr_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted">Kích thước tối đa: 2MB. Định dạng: JPG, PNG, GIF</small>
                                    
                                    <div class="mt-2 d-none" id="qr-preview-container">
                                        <img src="" alt="QR Code Preview" id="qr-preview" class="img-thumbnail" style="max-height: 100px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="status" name="status" value="1" checked>
                                <label class="custom-control-label" for="status">Hoạt động</label>
                            </div>
                            <small class="form-text text-muted">Ngân hàng không hoạt động sẽ không hiển thị cho người dùng.</small>
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Lưu ngân hàng
                            </button>
                            <a href="{{ route('admin.banks.index') }}" class="btn btn-secondary ml-2">
                                <i class="fas fa-times mr-1"></i> Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Preview for logo
        $('#logo').change(function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#logo-preview').attr('src', e.target.result);
                    $('#logo-preview-container').removeClass('d-none');
                }
                reader.readAsDataURL(file);
                
                // Update file label
                $(this).next('.custom-file-label').html(file.name);
            }
        });
        
        // Preview for QR code
        $('#qr_code').change(function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#qr-preview').attr('src', e.target.result);
                    $('#qr-preview-container').removeClass('d-none');
                }
                reader.readAsDataURL(file);
                
                // Update file label
                $(this).next('.custom-file-label').html(file.name);
            }
        });
    });
</script>
@endpush 