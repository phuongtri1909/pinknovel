<?php

namespace App\Notifications;

use App\Models\Story;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class StoryStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected $story;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Story $story)
    {
        $this->story = $story;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $status = $this->story->status;
        $title = $this->story->title;
        
        $message = (new MailMessage)
            ->subject("Cập nhật trạng thái truyện: {$title}");
            
        if ($status === 'published') {
            $message->line('Truyện của bạn đã được phê duyệt và xuất bản thành công!')
                ->line("Tiêu đề: {$title}")
                ->action('Xem truyện', route('stories.show', $this->story->slug))
                ->line('Cảm ơn bạn đã đóng góp nội dung cho trang web của chúng tôi!');
        } else if ($status === 'rejected') {
            $message->line('Truyện của bạn đã bị từ chối.')
                ->line("Tiêu đề: {$title}")
                ->line("Lý do: {$this->story->admin_note}")
                ->action('Chỉnh sửa truyện', route('user.author.stories.edit', $this->story->id))
                ->line('Bạn có thể chỉnh sửa và gửi lại truyện để được xem xét lại.');
        }
        
        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $status = $this->story->status;
        $title = $this->story->title;
        
        if ($status === 'published') {
            $message = "Truyện \"{$title}\" của bạn đã được phê duyệt và xuất bản thành công!";
        } else if ($status === 'rejected') {
            $message = "Truyện \"{$title}\" của bạn đã bị từ chối. Lý do: {$this->story->admin_note}";
        } else {
            $message = "Trạng thái của truyện \"{$title}\" đã được cập nhật thành: {$status}";
        }
        
        return [
            'story_id' => $this->story->id,
            'title' => $title,
            'status' => $status,
            'message' => $message,
            'admin_note' => $this->story->admin_note,
        ];
    }
} 