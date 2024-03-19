<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserInformation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $userName;
    public $fullName;
    public $password;
    public $createdDate;

    public function __construct($userName, $fullName, $password, $createdDate)
    {
        $this->userName = $userName;
        $this->fullName = $fullName;
        $this->password = $password;
        $this->createdDate = $createdDate;
    }

    public function build()
    {
        return $this->view('emails.user_information')
                    ->subject('Thông tin tài khoản đăng nhập')
                    ->with([
                        'userName' => $this->userName,
                        'fullName' => $this->fullName,
                        'password' => $this->password,
                        'createdDate' => $this->createdDate
                    ]);
    }
}