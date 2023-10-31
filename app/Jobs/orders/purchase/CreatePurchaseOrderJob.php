<?php

namespace App\Jobs\orders\purchase;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreatePurchaseOrderJob implements ShouldQueue
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
     *
     * @return void
     */
    public function handle()
    {
        try {

            foreach ($this->data as $response){

                $user = User::where('email', $response['email'])->first();

                $product = Product::where([
                        'made' => $response['made'],
                        'product_code' => $response['product_code'],
                        'user_id' => $user->id
                    ])->first();

                PurchaseOrder::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'payment_method'=> $response['payment_method'],
                    'buyer_id' => $response['buyer_id'],
                    'quantity' => $response['quantity'],
                ]);

            }

        } catch (\Exception $exception) {
            echo $exception;
        }
    }
}
