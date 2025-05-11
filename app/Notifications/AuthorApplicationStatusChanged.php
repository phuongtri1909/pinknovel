<?php

namespace App\Notifications;

use App\Models\AuthorApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuthorApplicationStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The application instance.
     */
    protected $application;

    /**
     * Create a new notification instance.
     */
    public function __construct(AuthorApplication $application)
    {
        $this->application = $application;
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
        $status = $this->application->status;
        
        $mail = (new MailMessage)
            ->subject("Thông báo đơn đăng ký tác giả");
            
        if ($status === 'approved') {
            $mail->greeting("Xin chào {$notifiable->name}!")
                ->line("Chúc mừng! Đơn đăng ký trở thành tác giả của bạn đã được phê duyệt.")
                ->line("Bạn hiện đã có thể truy cập khu vực tác giả và đăng truyện mới.");
                
            if ($this->application->admin_note) {
                $mail->line("Ghi chú từ admin: {$this->application->admin_note}");
            }
            
            $mail->action('Khu vực tác giả', url(route('user.author.index')));
        } else {
            $mail->greeting("Xin chào {$notifiable->name}!")
                ->line("Rất tiếc, đơn đăng ký trở thành tác giả của bạn đã bị từ chối.")
                ->line("Lý do từ admin: {$this->application->admin_note}")
                ->line("Bạn có thể gửi lại đơn đăng ký sau khi khắc phục các vấn đề trên.")
                ->action('Thử lại', url(route('user.author.application')));
        }
        
        return $mail->line("Thời gian xét duyệt: {$this->application->reviewed_at->format('H:i:s d/m/Y')}")
            ->line('Cảm ơn bạn đã sử dụng trang web của chúng tôi!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $status = $this->application->status;
        
        return [
            'title' => $status === 'approved' 
                ? "Đơn đăng ký tác giả đã được phê duyệt" 
                : "Đơn đăng ký tác giả đã bị từ chối",
            'message' => $status === 'approved' 
                ? "Đơn đăng ký trở thành tác giả của bạn đã được phê duyệt. Bạn hiện có thể đăng truyện." 
                : "Đơn đăng ký trở thành tác giả của bạn đã bị từ chối.",
            'application_id' => $this->application->id,
            'status' => $status,
            'admin_note' => $this->application->admin_note,
        ];
    }
}
