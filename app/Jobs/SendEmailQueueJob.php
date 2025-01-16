<?php

namespace App\Jobs;

use App\Mail\SendConfirmationEmailToRenter;
use App\Mail\SendConfirmationEmailToAdmin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\LogController;
use Throwable;

class SendEmailQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    protected $mailTo;
    protected $type;
    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct(string $type,array $data,string $email)
    {
        $this->mailTo = $email;
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if($this->type === 'confirmation_to_renter'){
            $email = new SendConfirmationEmailToRenter($this->data);
            Mail::to($this->mailTo)->send($email);
        }
        if($this->type === 'confirmation_to_admin'){
            $email = new SendConfirmationEmailToAdmin($this->data);
            Mail::to($this->mailTo)->send($email);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        LogController::saveLog('jobs','SendEmailQueueJob','A job of type '.$this->type.' failed: '.$exception->getMessage());
    }
}
