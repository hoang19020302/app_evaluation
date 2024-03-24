<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserInformation;


class SendEmailJob2 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $title;
    protected $userName;
    protected $fullName;
    protected $password;
    protected $createdDate;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $title, $userName, $fullName, $password, $createdDate)
    {
        $this->email = $email;
        $this->title = $title;
        $this->userName = $userName;
        $this->fullName = $fullName;
        $this->password = $password;
        $this->createdDate = $createdDate;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Thực hiện gửi email 
        Mail::to($this->email)->send(new UserInformation($this->title, $this->userName, $this->fullName, $this->password, $this->createdDate));
    }
}


