<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EvaluationInvitation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $title;
    public $linkArray;
    public $sentTime;

    public function __construct($title, $linkArray, $sentTime)
    {
        $this->title = $title;
        $this->linkArray = $linkArray;
        $this->sentTime = $sentTime;
    }

    public function build()
    {
        return $this->view('emails.evaluation_invitation')
                    ->subject($this->title)
                    ->with([
                        'title' => $this->title,
                        'linkArray' => $this->linkArray,
                        'sentTime' => $this->sentTime,
                    ]);
    }
}

