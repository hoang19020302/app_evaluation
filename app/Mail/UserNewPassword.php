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
    public $title;
    public $fullName;
    public $newPassword;
    public $updatedDate;

    public function __construct($title, $fullName, $newPassword, $updatedDate)
    {
        $this->title = $title;
        $this->fullName = $fullName;
        $this->newPassword = $newPassword;
        $this->updatedDate = $updatedDate;
    }

    public function build()
    {
        return $this->view('emails.user_new_password')
                    ->subject($this->title)
                    ->with([
                        'title' => $this->title,
                        'fullName' => $this->fullName,
                        'newPassword' => $this->newPassword,
                        'updatedDate' => $this->updatedDate
                    ]);
    }
}