<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ViolationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $type;

    public function __construct($user, $type)
    {
        $this->user = $user;
        $this->type = $type;
    }

    public function build()
    {
        $view = $this->type === 'login' ? 'emails.login-violation' : 'emails.otp-violation';

        return $this->to('mfa.mypetra@petra.ac.id')
                    ->subject('Security Alert: Multiple Failed Attempts')
                    ->view($view)
                    ->with([
                        'email' => $this->user->email,
                        'attempts' => $this->type === 'login' ? $this->user->failed_login_attempts : $this->user->failed_otp_attempts,
                        'timestamp' => now()->format('Y-m-d H:i:s'),
                    ]);
    }
}
