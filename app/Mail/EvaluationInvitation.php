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
    public $content;
    public $evaluationLink;
    public $expirationTime;
    public $name;

    public function __construct($content, $evaluationLink, $expirationTime, $name)
    {
        $this->content = $content;
        $this->evaluationLink = $evaluationLink;
        $this->expirationTime = $expirationTime;
        $this->name = $name;
    }

    public function build()
    {
        return $this->view('emails.evaluation_invitation')
                    ->subject('Tham gia bài test')
                    ->with([
                        'content' => $this->content,
                        'evaluationLink' => $this->evaluationLink,
                        'expirationTime' => $this->expirationTime,
                        'name' => $this->name,
                    ]);
    }
}
