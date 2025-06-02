<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LdapAccountCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $uid;
    public string $email;
    public string $defaultPassword;

    public function __construct($uid, $email, $password)
    {
        $this->uid = $uid;
        $this->email = $email;
        $this->defaultPassword = $password;
    }

    public function build()
    {
        return $this->subject('Your My Petra LDAP Account')
            ->view('emails.ldap-created')
            ->with([
                'uid' => $this->uid,
                'email' => $this->email,
                'defaultPassword' => $this->defaultPassword,
            ]);
    }
}
