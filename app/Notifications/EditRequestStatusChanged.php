<?php

namespace App\Notifications;

use App\Models\StoryEditRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EditRequestStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The edit request instance.
     */
    protected $editRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(StoryEditRequest $editRequest)
    {
        $this->editRequest = $editRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $status = $this->editRequest->status;
        $title = $this->editRequest->title;
        $storyTitle = $this->editRequest->story->title;
        
        $message = (new MailMessage)
            ->subject("Cập nhật yêu cầu chỉnh sửa truyện: {$storyTitle}");
            
        if ($status === 'approved') {
            $message->line('Yêu cầu chỉnh sửa truyện của bạn đã được phê duyệt!')
                ->line("Tiêu đề truyện: {$storyTitle}")
                ->action('Xem truyện', route('stories.show', $this->editRequest->story->slug))
                ->line('Những thay đổi của bạn đã được áp dụng vào truyện.');
                
            if ($this->editRequest->admin_note) {
                $message->line("Ghi chú từ quản trị viên: {$this->editRequest->admin_note}");
            }
        } else if ($status === 'rejected') {
            $message->line('Yêu cầu chỉnh sửa truyện của bạn đã bị từ chối.')
                ->line("Tiêu đề truyện: {$storyTitle}")
                ->line("Lý do: {$this->editRequest->admin_note}")
                ->action('Xem truyện', route('stories.show', $this->editRequest->story->slug))
                ->line('Bạn có thể tạo một yêu cầu chỉnh sửa mới với những thay đổi phù hợp hơn.');
        }
        
        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $status = $this->editRequest->status;
        $storyTitle = $this->editRequest->story->title;
        
        if ($status === 'approved') {
            $message = "Yêu cầu chỉnh sửa truyện \"{$storyTitle}\" của bạn đã được phê duyệt!";
        } else if ($status === 'rejected') {
            $message = "Yêu cầu chỉnh sửa truyện \"{$storyTitle}\" của bạn đã bị từ chối. Lý do: {$this->editRequest->admin_note}";
        } else {
            $message = "Trạng thái của yêu cầu chỉnh sửa truyện \"{$storyTitle}\" đã được cập nhật thành: {$status}";
        }
        
        return [
            'edit_request_id' => $this->editRequest->id,
            'story_id' => $this->editRequest->story_id,
            'story_title' => $storyTitle,
            'status' => $status,
            'message' => $message,
            'admin_note' => $this->editRequest->admin_note,
        ];
    }
}
