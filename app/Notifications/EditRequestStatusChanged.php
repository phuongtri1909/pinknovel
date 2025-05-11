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
        $story = $this->editRequest->story;
        
        $mail = (new MailMessage)
            ->subject("Cập nhật yêu cầu chỉnh sửa truyện {$story->title}");
            
        if ($status === 'approved') {
            $mail->greeting("Xin chào {$notifiable->name}!")
                ->line("Yêu cầu chỉnh sửa thông tin truyện \"{$story->title}\" của bạn đã được phê duyệt và áp dụng thành công.")
                ->line("Những thay đổi của bạn hiện đã có hiệu lực và được hiển thị cho độc giả.");
        } else {
            $mail->greeting("Xin chào {$notifiable->name}!")
                ->line("Yêu cầu chỉnh sửa thông tin truyện \"{$story->title}\" của bạn đã bị từ chối.")
                ->line("Lý do từ admin: {$this->editRequest->admin_note}")
                ->line("Bạn có thể thực hiện chỉnh sửa mới và gửi lại yêu cầu.");
        }
        
        return $mail->line("Thời gian xét duyệt: {$this->editRequest->reviewed_at->format('H:i:s d/m/Y')}")
            ->action('Xem truyện của bạn', url(route('user.author.stories.edit', $story->id)))
            ->line('Cảm ơn bạn đã sử dụng trang web của chúng tôi!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $status = $this->editRequest->status;
        $story = $this->editRequest->story;
        
        return [
            'title' => "Yêu cầu chỉnh sửa " . ($status === 'approved' ? 'đã được phê duyệt' : 'đã bị từ chối'),
            'message' => $status === 'approved' 
                ? "Yêu cầu chỉnh sửa thông tin truyện \"{$story->title}\" của bạn đã được phê duyệt."
                : "Yêu cầu chỉnh sửa thông tin truyện \"{$story->title}\" của bạn đã bị từ chối.",
            'story_id' => $story->id,
            'edit_request_id' => $this->editRequest->id,
            'status' => $status,
            'admin_note' => $this->editRequest->admin_note,
        ];
    }
}
