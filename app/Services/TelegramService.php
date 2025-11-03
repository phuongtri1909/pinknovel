<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /**
     * Escape special characters for MarkdownV2
     */
    private static function escapeMarkdown(string $text): string
    {
        $specialChars = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
        foreach ($specialChars as $char) {
            $text = str_replace($char, '\\' . $char, $text);
        }
        return $text;
    }

    /**
     * Send a message to Telegram bot
     * 
     * @param string $message The message to send
     * @param string|null $parseMode Optional parse mode (Markdown, HTML, or null for plain text)
     * @return bool Returns true if sent successfully, false otherwise
     */
    public static function sendMessage(string $message, ?string $parseMode = null): bool
    {
        try {
            $token = env('TELEGRAM_BOT_TOKEN');
            $chatId = env('TELEGRAM_CHAT_ID');

            if (!$token || !$chatId) {
                Log::warning('Telegram bot token or chat ID not configured');
                return false;
            }

            $url = "https://api.telegram.org/bot{$token}/sendMessage";

            $params = [
                'chat_id' => $chatId,
                'text' => $message,
            ];

            // Only add parse_mode if specified
            if ($parseMode) {
                $params['parse_mode'] = $parseMode;
            }

            $response = Http::timeout(5)->get($url, $params);

            if ($response->successful()) {
                return true;
            } else {
                Log::warning('Telegram API Error: ' . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Telegram API Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a formatted notification about new author application
     * 
     * @param \App\Models\AuthorApplication $application
     * @return bool
     */
    public static function notifyNewAuthorApplication($application): bool
    {
        try {
            $user = $application->user;
            
            // Build message without Markdown formatting to avoid parsing errors
            $message = "🔔 Đơn đăng ký tác giả mới\n\n";
            $message .= "👤 Người dùng: {$user->name} ({$user->email})\n";
            $message .= "📅 Ngày gửi: " . $application->submitted_at->format('d/m/Y H:i') . "\n";
            $message .= "🔗 Facebook: {$application->facebook_link}\n";
            
            if ($application->telegram_link) {
                $message .= "💬 Telegram: {$application->telegram_link}\n";
            }
            
            if ($application->other_platform && $application->other_platform_link) {
                $message .= "📱 {$application->other_platform}: {$application->other_platform_link}\n";
            }
            
            if ($application->introduction) {
                $intro = mb_substr($application->introduction, 0, 200);
                $message .= "\n📝 Giới thiệu: {$intro}" . (mb_strlen($application->introduction) > 200 ? '...' : '') . "\n";
            }
            
            $message .= "\n🔗 Xem chi tiết: " . route('admin.author-applications.show', $application->id);

            // Send without parse_mode to avoid Markdown parsing errors
            return self::sendMessage($message, null);
        } catch (\Exception $e) {
            Log::error('Error sending Telegram notification for author application: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a formatted notification about story review request
     * 
     * @param \App\Models\Story $story
     * @return bool
     */
    public static function notifyStoryReviewRequest($story): bool
    {
        try {
            $user = $story->user;
            $chapterCount = $story->chapters()->count();
            
            // Format submitted_at date
            $submittedDate = now()->format('d/m/Y H:i');
            if ($story->submitted_at) {
                if (is_object($story->submitted_at)) {
                    $submittedDate = $story->submitted_at->format('d/m/Y H:i');
                } elseif (is_string($story->submitted_at)) {
                    try {
                        $submittedDate = \Carbon\Carbon::parse($story->submitted_at)->format('d/m/Y H:i');
                    } catch (\Exception $e) {
                        // Keep default if parsing fails
                    }
                }
            }
            
            // Build message without Markdown formatting to avoid parsing errors
            $message = "📚 Yêu cầu duyệt truyện mới\n\n";
            $message .= "📖 Truyện: {$story->title}\n";
            $message .= "👤 Tác giả: {$user->name} ({$user->email})\n";
            $message .= "📝 Số chương: {$chapterCount}\n";
            $message .= "📅 Ngày gửi: {$submittedDate}\n";
            
            if ($story->review_note) {
                $note = mb_substr($story->review_note, 0, 200);
                $message .= "\n💬 Ghi chú: {$note}" . (mb_strlen($story->review_note) > 200 ? '...' : '') . "\n";
            }
            
            $message .= "\n🔗 Xem chi tiết: " . route('admin.story-reviews.show', $story->id);

            // Send without parse_mode to avoid Markdown parsing errors
            return self::sendMessage($message, null);
        } catch (\Exception $e) {
            Log::error('Error sending Telegram notification for story review request: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a formatted notification about story edit request
     * 
     * @param \App\Models\StoryEditRequest $editRequest
     * @return bool
     */
    public static function notifyStoryEditRequest($editRequest): bool
    {
        try {
            $story = $editRequest->story;
            $user = $editRequest->user;
            
            $submittedDate = now()->format('d/m/Y H:i');
            if ($editRequest->submitted_at) {
                if (is_object($editRequest->submitted_at)) {
                    $submittedDate = $editRequest->submitted_at->format('d/m/Y H:i');
                } elseif (is_string($editRequest->submitted_at)) {
                    try {
                        $submittedDate = \Carbon\Carbon::parse($editRequest->submitted_at)->format('d/m/Y H:i');
                    } catch (\Exception $e) {
                    }
                }
            }
            
            $message = "✏️ Yêu cầu chỉnh sửa truyện mới\n\n";
            $message .= "📖 Truyện: {$story->title}\n";
            $message .= "👤 Tác giả: {$user->name} ({$user->email})\n";
            $message .= "📅 Ngày gửi: {$submittedDate}\n";
            $message .= "\n🔗 Xem chi tiết: " . route('admin.edit-requests.show', $editRequest->id);

            return self::sendMessage($message, null);
        } catch (\Exception $e) {
            Log::error('Error sending Telegram notification for story edit request: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a formatted notification about new comment that needs approval
     * 
     * @param \App\Models\Comment $comment
     * @return bool
     */
    public static function notifyNewComment($comment): bool
    {
        try {
            $user = $comment->user;
            $story = $comment->story;
            
            $strippedComment = strip_tags($comment->comment);
            $commentText = mb_substr($strippedComment, 0, 150);
            $commentText = mb_strlen($strippedComment) > 150 ? $commentText . '...' : $commentText;
            
            $isReply = !empty($comment->reply_id);
            $messageType = $isReply ? "💬 Phản hồi bình luận mới cần duyệt" : "💬 Bình luận mới cần duyệt";
            
            $message = "{$messageType}\n\n";
            $message .= "📖 Truyện: {$story->title}\n";
            $message .= "👤 Người bình luận: {$user->name} ({$user->email})\n";
            $message .= "📝 Nội dung: {$commentText}\n";
            $message .= "📅 Ngày gửi: " . $comment->created_at->format('d/m/Y H:i') . "\n";
            $message .= "\n🔗 Xem chi tiết: " . route('comments.all') . "?search=" . urlencode(strip_tags($comment->comment));

            return self::sendMessage($message, null);
        } catch (\Exception $e) {
            Log::error('Error sending Telegram notification for new comment: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a formatted notification about PayPal deposit confirmation
     * 
     * @param \App\Models\PaypalDeposit $paypalDeposit
     * @return bool
     */
    public static function notifyPaypalDepositConfirmation($paypalDeposit): bool
    {
        try {
            $user = $paypalDeposit->user;
            $requestPayment = $paypalDeposit->requestPaymentPaypal;
            
            $message = "💳 Xác nhận nạp PayPal mới\n\n";
            $message .= "👤 Người dùng: {$user->name} ({$user->email})\n";
            $message .= "💰 Số tiền: $" . number_format($requestPayment->base_usd_amount, 2) . " USD\n";
            $message .= "🪙 Xu nhận được: " . number_format($paypalDeposit->coins) . " xu\n";
            $message .= "📋 Mã giao dịch: {$requestPayment->transaction_code}\n";
            $message .= "📅 Ngày xác nhận: " . $paypalDeposit->created_at->format('d/m/Y H:i') . "\n";
            $message .= "\n🔗 Xem chi tiết: " . route('admin.paypal-deposits.index') . "?view=" . $paypalDeposit->id;

            return self::sendMessage($message, null);
        } catch (\Exception $e) {
            Log::error('Error sending Telegram notification for PayPal deposit confirmation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a formatted notification about bank deposit confirmation
     * 
     * @param \App\Models\Deposit $deposit
     * @return bool
     */
    public static function notifyBankDepositConfirmation($deposit): bool
    {
        try {
            $user = $deposit->user;
            $bank = $deposit->bank;
            
            $message = "💳 Xác nhận nạp ngân hàng mới\n\n";
            $message .= "👤 Người dùng: {$user->name} ({$user->email})\n";
            $message .= "💰 Số tiền: " . number_format($deposit->amount) . " VNĐ\n";
            $message .= "🏦 Ngân hàng: {$bank->name}\n";
            $message .= "🪙 Xu nhận được: " . number_format($deposit->coins) . " xu\n";
            if ($deposit->fee > 0) {
                $message .= "💸 Phí: " . number_format($deposit->fee) . " VNĐ\n";
            }
            $message .= "📋 Mã giao dịch: {$deposit->transaction_code}\n";
            $message .= "📅 Ngày xác nhận: " . $deposit->created_at->format('d/m/Y H:i') . "\n";
            $message .= "\n🔗 Xem chi tiết: " . route('deposits.index') . "?view=" . $deposit->id;

            return self::sendMessage($message, null);
        } catch (\Exception $e) {
            Log::error('Error sending Telegram notification for bank deposit confirmation: ' . $e->getMessage());
            return false;
        }
    }
}

