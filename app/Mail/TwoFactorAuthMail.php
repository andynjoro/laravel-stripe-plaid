<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TwoFactorAuthMail extends Mailable {
    use Queueable, SerializesModels;

    public $token;

    public function __construct($token) {
        $this->token = $token;
    }

    public function build() {
        return $this->view('email.2fa')->subject('Your Authentication Token');
    }
}
