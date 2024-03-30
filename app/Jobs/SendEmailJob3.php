<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\JoinRegisterApp;


class SendEmailJob3 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $title;
    protected $link;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $title, $link)
    {
        $this->email = $email;
        $this->title = $title;
        $this->link = $link;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Thực hiện gửi email 
        Mail::to($this->email)->send(new JoinRegisterApp($this->title, $this->link));
    }
}


