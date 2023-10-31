<?php

namespace App\Jobs\auth\storehouse;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AcceptRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            $user = User::where('email',$this->data['user_email']) -> first();

            $user->latitude = $this->data['latitude'];
            $user->longitude = $this->data['longitude'];
            $user->authenticated = 1;

            $user->update();

        } catch (\Exception $exception) {
            echo $exception;
        }
    }
}
