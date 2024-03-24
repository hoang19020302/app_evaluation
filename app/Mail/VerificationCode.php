<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationCode extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $title;
    public $fullName;
    public $verifyCode;

    public function __construct($title, $fullName, $verifyCode)
    {
        $this->title = $title;
        $this->fullName = $fullName;
        $this->verifyCode = $verifyCode;
    }

    public function build()
    {
        return $this->view('emails.verification_code')
                    ->subject($this->title)
                    ->with([
                        'title' => $this->title,
                        'fullName' => $this->fullName,
                        'verifyCode' => $this->verifyCode,
                    ]);
    }
}