<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserNewPassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $fullName;
    public $newPassword;
    public $updatedDate;

    public function __construct($fullName, $newPassword, $updatedDate)
    {
        $this->fullName = $fullName;
        $this->newPassword = $newPassword;
        $this->updatedDate = $updatedDate;
    }

    public function build()
    {
        return $this->view('emails.user_new_password')
                    ->subject('Lấy lại mật khẩu')
                    ->with([
                        'fullName' => $this->fullName,
                        'newPassword' => $this->newPassword,
                        'updatedDate' => $this->updatedDate
                    ]);
    }
}