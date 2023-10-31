<?php

namespace App\Http\Controllers\order\purchase;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Services\Order\Purchase\PurchaseOrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PurchaseOrderController extends Controller
{
    /**
     * The workshop orders service implementation.
     *
     * @var PurchaseOrderService
     */
    protected PurchaseOrderService $purchaseOrderService;

    // singleton pattern, service container
    public function __construct(PurchaseOrderService $purchaseOrderService)
    {
        $this->purchaseOrderService = $purchaseOrderService;
    }

    /**
     * @return Response
     */
    public function getWaitingPurchaseOrders(): Response
    {
        return $this->purchaseOrderService->getWaitingPurchaseOrders();
    }

    /**
     * @return Response
     */
    public function getAcceptedPurchaseOrders(): Response
    {
        return $this->purchaseOrderService->getAcceptedPurchaseOrders();
    }

    /**
     * @return Response
     */
    public function getRejectedPurchaseOrders(): Response
    {
        return $this->purchaseOrderService->getRejectedPurchaseOrders();
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     * @return Response
     */
    public function acceptPurchaseOrder(PurchaseOrder $purchaseOrder): Response
    {
        return $this->purchaseOrderService->acceptPurchaseOrder($purchaseOrder);
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     * @return Response
     */
    public function rejectPurchaseOrder(PurchaseOrder $purchaseOrder): Response
    {
        return $this->purchaseOrderService->rejectPurchaseOrder($purchaseOrder);
    }
}
