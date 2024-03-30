<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class JoinRegisterApp extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $title;
    public $link;

    public function __construct($title, $link)
    {
        $this->title = $title;
        $this->link = $link;
    }

    public function build()
    {
        return $this->view('emails.join_register_app')
                    ->subject($this->title)
                    ->with([
                        'title' => $this->title,
                        'link' => $this->link,
                    ]);
    }
}
