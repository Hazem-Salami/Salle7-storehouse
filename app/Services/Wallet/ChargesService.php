<?php

namespace App\Services\Wallet;

use App\Jobs\wallets\UserWalletJob;
use App\Models\User;
use App\Services\BaseService;
use App\Mail\VerificationMail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\verify\VerificationRequest;
use Carbon\Carbon;

class ChargesService extends BaseService
{

    public function createWallet(): Response
    {
        DB::beginTransaction();
        $user = User::find(auth()->user()->id);

        if ($user->wallet === null) {
            $wallet = $user->wallet()->create();

            try {
                UserWalletJob::dispatch(['email' => $user->email])->onQueue('admin');
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->customResponse(false, 'Bad Internet', null, 504);
            }
            DB::commit();
            return $this->customResponse(true, 'تم إنشاء محفظة بنكية لك بنجاح، شكراً', $wallet);
        }
        return $this->customResponse(false, 'لديك محفظة مسبقاً');
    }

    public function getStatus(): Response
    {
        $user = User::find(auth()->user()->id);

        if ($user->wallet === null) {
            return $this->customResponse(true, 'ليس لديك محفظة بنكية', 0);
        } else {
            return $this->customResponse(true, 'لديك محفظة بنكية', 1);
        }
    }

    public function getAmount(): Response
    {
        $user = User::find(auth()->user()->id);

        if ($user->wallet === null) {
            return $this->customResponse(false, 'ليس لديك محفظة بنكية');
        }
        return $this->customResponse(true, 'الرصيد الحالي', $user->wallet->amount);
    }
}
