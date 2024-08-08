<?php

namespace App\Jobs\Mails;

use App\Http\Controllers\Api\v1\MailController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAccountCreationSuccessNotificationJob implements ShouldQueue
{
    use Queueable, SerializesModels, Dispatchable, InteractsWithQueue;

    /**
     * Create a new job instance.
     */
    public function __construct(protected string $email, protected string $userName)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        MailController::sendSuccessfulAccountCreation($this->email, $this->userName);
    }
}
