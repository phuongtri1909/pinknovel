<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Chapter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishScheduledChapters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chapters:publish-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xuất bản các chương truyện đã được hẹn giờ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        
        // Tìm tất cả các chương có lịch xuất bản và thời gian trong quá khứ
        $chapters = Chapter::where('status', 'draft')
            ->whereNotNull('scheduled_publish_at')
            ->where('scheduled_publish_at', '<=', $now)
            ->get();
            
        $count = count($chapters);
        
        if ($count == 0) {
            $this->info('Không có chương nào cần xuất bản vào lúc này.');
            return;
        }
        
        foreach ($chapters as $chapter) {
            try {
                // Cập nhật trạng thái chương thành published
                $chapter->status = 'published';
                $chapter->save();
                
                $this->info("Đã xuất bản chương {$chapter->number}: {$chapter->title} của truyện ID={$chapter->story_id}");
                
                // Ghi log
                Log::info("Xuất bản tự động: Chương {$chapter->number} '{$chapter->title}' của truyện ID={$chapter->story_id}");
            } catch (\Exception $e) {
                $this->error("Lỗi khi xuất bản chương {$chapter->number}: {$e->getMessage()}");
                Log::error("Lỗi xuất bản tự động: Chương {$chapter->number} - {$e->getMessage()}");
            }
        }
        
        $this->info("Đã xuất bản tổng cộng {$count} chương.");
    }
}
