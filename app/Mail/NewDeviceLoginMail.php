<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewDeviceLoginMail extends Mailable
{
    use Queueable, SerializesModels;

    public $ip;
    public $os;
    public $device;
    public $time;

    public function __construct($ip, $os, $device, $time)
    {
        $this->ip = $ip;
        $this->os = $os;
        $this->device = $device;
        $this->time = $time;
    }

    public function build()
    {
        return $this->subject('Login dari Perangkat Baru Terdeteksi')
            ->view('emails.new-device');
    }
}

