<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCode;

class SendEmailJob4 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $title;
    protected $fullName;
    protected $verifyCode;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $title, $fullName, $verifyCode)
    {
        $this->email = $email;
        $this->title = $title;
        $this->fullName = $fullName;
        $this->verifyCode = $verifyCode;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Thực hiện gửi email 
        Mail::to($this->email)->send(new VerificationCode($this->title, $this->fullName, $this->verifyCode));
    }
}


