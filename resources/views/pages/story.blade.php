@extends('layouts.app')

@section('title', $story->title . ' - Truyện Pink novel - Đọc Truyện Online Miễn Phí | ' . config('app.name'))

@section('description', Str::limit(strip_tags($story->description), 160))

@section('keyword',
    implode(', ', [
    $story->title,
    'đọc truyện ' . $story->title,
    'truyện online',
    $story->categories->pluck('name')->implode(', '),
    $story->user->name ?? 'tác giả',
    'Truyện Pink novel - Đọc Truyện
    Online Miễn Phí',
    $story->completed ? 'truyện hoàn thành' : 'truyện đang cập nhật',
    'novel',
    'web đọc truyện',
    ]))

    @push('styles')
        <style>
            .card-search {
                background: #e7e7e7;
                border-radius: 10px;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
                text-align: center;
                transition: transform 0.3s ease;
            }

            .search-wrapper {
                max-width: min(500px, 90%);
                margin: 0 auto;
            }

            .search-wrapper .form-control {
                height: 50px;
                border-radius: 25px 0 0 25px;
                border: none;
                padding-left: 20px;
            }

            .search-wrapper .btn {
                border-radius: 0 25px 25px 0;
                padding: 0 25px;
            }

            @media (max-width: 768px) {
                .search-wrapper .form-control {
                    height: 40px;
                }

                .search-wrapper .btn {
                    padding: 0 15px;
                }
            }

            .banner-image-home {
                width: 100%;
                height: 350px;
                object-position: center;
            }

            @media (max-width: 768px) {
                .banner-image-home {
                    height: 250px;
                }
            }

            @media (max-width: 576px) {
                .banner-image-home {
                    height: 200px;
                }
            }
        </style>
    @endpush

@section('content')
    <section id="page-story" class="">
        <div class=""></div>

        @include('components.info_book_home')



        <div class=" mt-4">

            <div class="container">
                @if (isset($story) && $story->has_combo)
                    @include('components.combo_story', ['story' => $story])
                @endif
            </div>

            <div class="" id="chapters">
                @if (!Auth()->check() || (Auth()->check() && Auth()->user()->ban_read == false))
                    @include('components.all_chapter', ['chapters' => $chapters, 'isAdmin' => Auth::check() && in_array(Auth::user()->role, ['admin', 'mod']), 'isAuthor' => Auth::check() && Auth::user()->role == 'author' && Auth::user()->id == $story->user_id])
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-sad-tear fa-4x text-muted mb-3 animate__animated animate__shakeX"></i>
                        <h5 class="text-danger">Bạn đã bị cấm đọc truyện!</h5>
                    </div>
                @endif
            </div>

            <div class="" id="comments">
                @if (!Auth()->check() || (Auth()->check() && Auth()->user()->ban_comment == false))
                    @include('components.comment', [
                        'pinnedComments' => $pinnedComments,
                        'regularComments' => $regularComments,
                    ])
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-sad-tear fa-4x text-muted mb-3 animate__animated animate__shakeX"></i>
                        <h5 class="text-danger">Bạn đã bị cấm bình luận!</h5>
                    </div>
                @endif
            </div>


            {{-- @include('components.list_story_full', ['completedStories' => $completedStories]) --}}

            {{-- @include('components.list_story_de_xuat', ['newStories' => $newStories]) --}}

            @include('components.stories_same_author_translator', ['story' => $story])
        </div>
    </section>

    @auth
        @include('components.modals.chapter-purchase-modal')
    @endauth

@endsection

@push('scripts')
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // @if($story->is_18_plus)
            //     // Tạo key duy nhất cho mỗi truyện
            //     const storyWarningKey = 'story_18_warning_{{ $story->id }}';
                
            //     // Kiểm tra xem đã hiển thị cảnh báo cho truyện này chưa
            //     if (!localStorage.getItem(storyWarningKey)) {
            //         Swal.fire({
            //             icon: 'warning',
            //             title: '⚠️ Cảnh báo nội dung 18+',
            //             html: `
            //                 <div class="text-start">
            //                     <p><strong>Truyện này chứa nội dung dành cho người từ 18 tuổi trở lên:</strong></p>
            //                     <ul class="text-start" style="margin-left: 20px;">
            //                         <li>Nội dung bạo lực, tình dục</li>
            //                         <li>Ngôn từ không phù hợp với trẻ em</li>
            //                         <li>Tình tiết người lớn</li>
            //                     </ul>
            //                     <p class="text-danger"><strong>Bạn có đủ 18 tuổi và muốn tiếp tục đọc?</strong></p>
            //                 </div>
            //             `,
            //             showCancelButton: true,
            //             confirmButtonText: '✅ Tôi đủ 18 tuổi',
            //             cancelButtonText: '❌ Quay lại',
            //             confirmButtonColor: '#d33',
            //             cancelButtonColor: '#6c757d',
            //             allowOutsideClick: false,
            //             allowEscapeKey: false,
            //             customClass: {
            //                 popup: 'animate__animated animate__fadeInDown',
            //                 confirmButton: 'btn btn-danger fw-bold',
            //                 cancelButton: 'btn btn-secondary fw-bold'
            //             },
            //             backdrop: `
            //                 rgba(0,0,0,0.8)
            //                 left top
            //                 no-repeat
            //             `
            //         }).then((result) => {
            //             if (result.isConfirmed) {
            //                 // Lưu vào localStorage để không hiển thị lại
            //                 localStorage.setItem(storyWarningKey, 'confirmed');
                            
            //                 // Hiển thị thông báo xác nhận
            //                 Swal.fire({
            //                     icon: 'success',
            //                     title: 'Đã xác nhận',
            //                     text: 'Chúc bạn đọc truyện vui vẻ!',
            //                     timer: 1500,
            //                     showConfirmButton: false,
            //                     customClass: {
            //                         popup: 'animate__animated animate__fadeInUp'
            //                     }
            //                 });
            //             } else {
            //                 // Người dùng không đồng ý, chuyển về trang chủ
            //                 Swal.fire({
            //                     icon: 'info',
            //                     title: 'Đã hủy',
            //                     text: 'Bạn sẽ được chuyển về trang chủ',
            //                     timer: 2000,
            //                     showConfirmButton: false,
            //                     customClass: {
            //                         popup: 'animate__animated animate__fadeOut'
            //                     }
            //                 }).then(() => {
            //                     window.location.href = '{{ route("home") }}';
            //                 });
            //             }
            //         });
            //     }
            // @endif
        });
    </script>
@endpush
