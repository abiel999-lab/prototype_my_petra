<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminExternalAccessNotifyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $ip;
    public $os;
    public $device;
    public $timestamp;

    public function __construct($user, $ip, $os, $device, $timestamp)
    {
        $this->user = $user;
        $this->ip = $ip;
        $this->os = $os;
        $this->device = $device;
        $this->timestamp = $timestamp;
    }

    public function build()
    {
        return $this->subject("External Access Detected from {$this->user->email}")
                    ->view('emails.external-access');
    }
}

