<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Jenssegers\Agent\Agent;

class ViolationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $type;
    public $ipAddress;
    public $os;
    public $device;
    public $browser;

    public function __construct($user, $type)
    {
        $this->user = $user;
        $this->type = $type;
        $this->ipAddress = request()->ip();

        // Use Jenssegers Agent to detect OS, browser, and device
        $agent = new Agent();
        $agent->setUserAgent(request()->header('User-Agent'));

        $this->os = $agent->platform() ?? 'Unknown';
        $this->browser = $agent->browser() ?? 'Unknown';
        $this->device = $agent->isDesktop() ? 'Desktop' : ($agent->isMobile() || $agent->isTablet() ? 'Mobile' : 'Unknown');
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
                        'ipAddress' => $this->ipAddress,
                        'os' => $this->os,
                        'browser' => $this->browser,
                        'device' => $this->device,
                    ]);
    }
}
