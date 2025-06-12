<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpLdapMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otpCode;
    public $name;
    public $email;
    public $time;
    public $path;

    /**
     * Create a new message instance.
     */
    public function __construct($otpCode = null, $name, $email, $time, $path = null)
    {
        $this->otpCode = $otpCode;
        $this->name = $name;
        $this->email = $email;
        $this->time = $time;
        $this->path = $path;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $mail = $this->subject('⚠️ LDAP Access Notification');

        // Pilih tampilan email berdasarkan kondisi
        if ($this->otpCode) {
            $mail->view('emails.otp-ldap', [
                'otpCode' => $this->otpCode,
            ]);
        } else {
            $mail->view('emails.ldap-access-notify', [
                'name' => $this->name,
                'email' => $this->email,
                'time' => $this->time,
            ]);
        }

        // Lampiran jika ada file
        if (!empty($this->path)) {
            $file = public_path($this->path);
            if (file_exists($file)) {
                $mail->attach($file);
            }
        }

        return $mail;
    }
}
