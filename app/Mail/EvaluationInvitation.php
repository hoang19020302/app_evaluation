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
    public $group;
    public $name;
    public $brokenLink;

    public function __construct($content, $evaluationLink, $expirationTime, $group, $name, $brokenLink)
    {
        $this->content = $content;
        $this->evaluationLink = $evaluationLink;
        $this->expirationTime = $expirationTime;
        $this->group = $group;
        $this->name = $name;
        $this->brokenLink = $brokenLink;
    }

    public function build()
    {
        return $this->view('emails.evaluation_invitation')
                    ->subject('Tham gia bài đánh giá')
                    ->with([
                        'content' => $this->content,
                        'evaluationLink' => $this->evaluationLink,
                        'expirationTime' => $this->expirationTime,
                        'group' => $this->group,
                        'name' => $this->name,
                        'brokenLink' => $this->brokenLink
                    ]);
    }
}
