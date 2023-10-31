<?php

namespace App\Services\Order\Purchase;

use App\Http\Traits\Base64Trait;
use App\Jobs\orders\purchase\DeletePurchaseOrderJob;
use App\Jobs\orders\purchase\PurchaseOrderChangesJob;
use App\Jobs\product\DeleteProductJob;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use App\Models\PurchaseOrder;
use App\Services\BaseService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService extends BaseService
{
    use Base64Trait;

    private function  deleteOldPurchaseOrders(){
        DB::beginTransaction();
        $responseAdmin = array();
        $responseMain = array();

        $deletePurchaseOrders = PurchaseOrder::where('user_id', auth()->user()->id)->whereDate('updated_at', '<', Carbon::yesterday())->get();

        foreach ($deletePurchaseOrders as $deletePurchaseOrder){
            if($deletePurchaseOrder->stage == 0){
                $product = Product::where('id', $deletePurchaseOrder->product_id)->first();
                $product->update([
                    'quantity' => $product->quantity + $deletePurchaseOrder->quantity,
                ]);
                $responseAdmin [] = [
                    "email" => $deletePurchaseOrder->user->email,
                    "product_code" => $deletePurchaseOrder->product->product_code,
                    "made" => $deletePurchaseOrder->product->made,
                    'type' => 0,
                    'quantity' => $deletePurchaseOrder->quantity,
                ];
            }

            $responseMain [] = [
                "email" => $deletePurchaseOrder->user->email,
                "product_code" => $deletePurchaseOrder->product->product_code,
                "made" => $deletePurchaseOrder->product->made,
                "buyer_id" => $deletePurchaseOrder->buyer_id,
                'type' => 0,
            ];

            $deletePurchaseOrder->delete();
        }

        try{
            PurchaseOrderChangesJob::dispatch($responseAdmin)->onQueue('admin');
            PurchaseOrderChangesJob::dispatch($responseMain)->onQueue('main');
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }

    private function deleteSpentProduct(){
        $deleteProducts = Product::where('user_id', auth()->user()->id)->where('quantity', 0)->whereDate('updated_at', '<', Carbon::yesterday())->get();

        DB::beginTransaction();

        $user = User::find(auth()->user()->id);

        foreach ($deleteProducts as $deleteProduct) {

            $this->deleteFile($deleteProduct->image_path);

            $deleteProduct->delete();

            $deleteProduct->user_email = $user->email;

            try {

                DeleteProductJob::dispatch($deleteProduct->toArray())->onQueue('admin');
                DeleteProductJob::dispatch($deleteProduct->toArray())->onQueue('main');

            } catch (\Exception $e) {
                DB::rollBack();
                return false;
            }
            DB::commit();
        }
        return true;
    }

    /**
     * @return Response
     */
    public function getWaitingPurchaseOrders(): Response
    {
        if(!$this->deleteOldPurchaseOrders())
            return $this->customResponse(false, 'Bad Internet', null, 504);

/*        if(!$this->deleteSpentProduct())
            return $this->customResponse(false, 'Bad Internet', null, 504);*/

        $waitingPurchaseOrders = PurchaseOrder::where('user_id', auth()->user()->id)->where('stage', null)->paginate(\request('size'));

        return $this->customResponse(true, 'تمت الحصول على الطلبات بنجاح', $waitingPurchaseOrders);
    }

    /**
     * @return Response
     */
    public function getAcceptedPurchaseOrders(): Response
    {
        if(!$this->deleteOldPurchaseOrders())
            return $this->customResponse(false, 'Bad Internet', null, 504);

/*        if(!$this->deleteSpentProduct())
            return $this->customResponse(false, 'Bad Internet', null, 504)*/;

        $acceptedPurchaseOrders = PurchaseOrder::where('user_id', auth()->user()->id)->where('stage', 0)->paginate(\request('size'));

        return $this->customResponse(true, 'تمت الحصول على الطلبات بنجاح', $acceptedPurchaseOrders);
    }

    /**
     * @return Response
     */
    public function getRejectedPurchaseOrders(): Response
    {
        if(!$this->deleteOldPurchaseOrders())
            return $this->customResponse(false, 'Bad Internet', null, 504);

/*        if(!$this->deleteSpentProduct())
            return $this->customResponse(false, 'Bad Internet', null, 504);*/

        $rejectedPurchaseOrders = PurchaseOrder::where('user_id', auth()->user()->id)->where('stage', 1)->paginate(\request('size'));

        return $this->customResponse(true, 'تمت الحصول على الطلبات بنجاح', $rejectedPurchaseOrders);
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     * @return Response
     */
    public function acceptPurchaseOrder(PurchaseOrder $purchaseOrder): Response
    {
        if ($purchaseOrder && $purchaseOrder->stage === null && $purchaseOrder->product && $purchaseOrder->product->quantity >= $purchaseOrder->quantity) {

            if ($purchaseOrder->payment_method == 1 && !$purchaseOrder->user->wallet) {
                return $this->customResponse(false, 'لا يوجد لديك محفظة، الرجاء إنشاء محفظة');
            }

            DB::beginTransaction();

            $purchaseOrder->update([
                'stage' => 0,
            ]);

            $purchaseOrder->product->update([
                'quantity' => $purchaseOrder->product->quantity - $purchaseOrder->quantity,
            ]);

            $response[] = [
                "email" => $purchaseOrder->user->email,
                "product_code" => $purchaseOrder->product->product_code,
                "made" => $purchaseOrder->product->made,
                "buyer_id" => $purchaseOrder->buyer_id,
                'type' => 1,
                'quantity' => $purchaseOrder->quantity,
            ];

            try {
                PurchaseOrderChangesJob::dispatch($response)->onQueue('admin');
                PurchaseOrderChangesJob::dispatch($response)->onQueue('main');
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->customResponse(false, 'Bad Internet', null, 504);
            }

            DB::commit();


            return $this->customResponse(true, 'تمت الموافقة على الطلب بنجاح', $purchaseOrder);
        }

        return $this->customResponse(false, 'لا يمكن الموافقة على هذا الطلب');
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     * @return Response
     */
    public function rejectPurchaseOrder(PurchaseOrder $purchaseOrder): Response
    {
        if ($purchaseOrder && $purchaseOrder->stage === null) {

            DB::beginTransaction();

            $purchaseOrder->update([
                'stage' => 1,
            ]);

            $response[] = [
                "email" => $purchaseOrder->user->email,
                "product_code" => $purchaseOrder->product->product_code,
                "made" => $purchaseOrder->product->made,
                "buyer_id" => $purchaseOrder->buyer_id,
                'type' => 2,
            ];

            try{
                PurchaseOrderChangesJob::dispatch($response)->onQueue('main');
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->customResponse(false, 'Bad Internet', null, 504);
            }

            DB::commit();

            return $this->customResponse(true, 'تم رفض الطلب بنجاح', $purchaseOrder);
        }

        return $this->customResponse(false, 'لا يمكن رفض هذا الطلب');
    }
}
