<?php

namespace App\Jobs\auth\storehouse;

use App\Http\Traits\Base64Trait;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RejectRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Base64Trait;

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

            $user->authenticated++;

            $user->update();

        } catch (\Exception $exception) {
            echo $exception;
        }
    }
}
