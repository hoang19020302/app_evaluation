<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\EvaluationInvitation;

class SendEmailJob1 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $title;
    protected $emailContent;
    protected $evaluationLink;
    protected $expirationTime;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $title, $emailContent, $evaluationLink, $expirationTime)
    {
        $this->email = $email;
        $this->title = $title;
        $this->emailContent = $emailContent;
        $this->evaluationLink = $evaluationLink;
        $this->expirationTime = $expirationTime;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Thực hiện gửi email 
        Mail::to($this->email)->send(new EvaluationInvitation($this->title, $this->emailContent, $this->evaluationLink, $this->expirationTime));

    }
}


