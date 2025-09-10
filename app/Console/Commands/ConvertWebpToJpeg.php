<?php

namespace App\Console\Commands;

use App\Models\Story;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ConvertWebpToJpeg extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:webp-to-jpeg {--dry-run : Chỉ hiển thị danh sách, không convert}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert ảnh WebP cũ sang JPEG cho social media';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 CHẾ ĐỘ DRY RUN - Chỉ hiển thị danh sách, không convert');
        } else {
            $this->info('🚀 BẮT ĐẦU CONVERT ẢNH WEBP SANG JPEG');
        }
        
        $this->newLine();

        // Lấy tất cả stories có cover WebP nhưng chưa có cover_jpeg
        $stories = Story::whereNotNull('cover')
            ->where('cover', 'like', '%.webp')
            ->where(function($query) {
                $query->whereNull('cover_jpeg')
                      ->orWhere('cover_jpeg', '');
            })
            ->get();

        if ($stories->isEmpty()) {
            $this->info('✅ Không có ảnh nào cần convert!');
            return;
        }

        $this->info("📊 Tìm thấy {$stories->count()} truyện cần convert:");
        $this->newLine();

        $successCount = 0;
        $errorCount = 0;
        $progressBar = $this->output->createProgressBar($stories->count());

        foreach ($stories as $story) {
            $progressBar->advance();
            
            try {
                // Kiểm tra file WebP có tồn tại không
                if (!Storage::disk('public')->exists($story->cover)) {
                    $this->newLine();
                    $this->error("❌ File không tồn tại: {$story->cover}");
                    $errorCount++;
                    continue;
                }

                // Tạo tên file JPEG
                $webpPath = $story->cover;
                $jpegPath = str_replace('.webp', '.jpg', $webpPath);

                if ($isDryRun) {
                    $this->newLine();
                    $this->line("📝 {$story->title} -> {$jpegPath}");
                    continue;
                }

                // Đọc file WebP
                $webpContent = Storage::disk('public')->get($webpPath);
                
                // Convert WebP sang JPEG
                $image = Image::make($webpContent);
                $image->encode('jpg', 90);
                
                // Lưu file JPEG
                Storage::disk('public')->put($jpegPath, $image->stream());
                
                // Cập nhật database
                $story->update(['cover_jpeg' => $jpegPath]);
                
                $successCount++;
                
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("❌ Lỗi convert {$story->title}: " . $e->getMessage());
                $errorCount++;
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        if ($isDryRun) {
            $this->info("📋 DRY RUN hoàn thành - {$stories->count()} truyện sẽ được convert");
        } else {
            $this->info("✅ CONVERT HOÀN THÀNH!");
            $this->info("📊 Kết quả:");
            $this->info("   ✅ Thành công: {$successCount}");
            $this->info("   ❌ Lỗi: {$errorCount}");
            $this->info("   📁 Tổng: {$stories->count()}");
        }

        $this->newLine();
        $this->info("💡 Sử dụng: php artisan convert:webp-to-jpeg --dry-run để xem trước");
    }
}