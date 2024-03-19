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

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $emailContent;
    protected $evaluationLink;
    protected $expiration;
    protected $groupName;
    protected $name;
    protected $brokenLink;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $emailContent, $evaluationLink, $expiration, $groupName, $name, $brokenLink)
    {
        $this->email = $email;
        $this->emailContent = $emailContent;
        $this->evaluationLink = $evaluationLink;
        $this->expiration = $expiration;
        $this->groupName = $groupName;
        $this->name = $name;
        $this->brokenLink = $brokenLink;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Thực hiện gửi email
        Mail::to($this->email)->send(new EvaluationInvitation($this->emailContent, $this->evaluationLink, $this->expiration, $this->groupName, $this->name, $this->brokenLink));
    }
}
