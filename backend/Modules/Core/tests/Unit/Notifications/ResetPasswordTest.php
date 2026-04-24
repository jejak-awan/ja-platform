<?php

namespace Modules\Core\Tests\Unit\Notifications;

use Modules\Core\Notifications\ResetPassword;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    public function test_reset_password_notification()
    {
        $token = 'test-token';
        $notification = new ResetPassword($token);

        $notifiable = (object) ['email' => 'test@example.com'];

        $this->assertEquals(['mail'], $notification->via($notifiable));

        $mail = $notification->toMail($notifiable);
        $this->assertEquals('Reset Password Notification', $mail->subject);
        $this->assertStringContainsString($token, $mail->actionUrl);
        $this->assertStringContainsString(urlencode('test@example.com'), $mail->actionUrl);
    }
}
