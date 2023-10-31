<?php

namespace App\Services\Auth;

use App\Http\Requests\auth\LoginRequest;
use App\Http\Requests\auth\RegisterRequest;
use App\Http\Requests\auth\StorehouseAuthFileRequest;
use App\Http\Traits\Base64Trait;
use App\Jobs\auth\storehouse\SendAuthFilesJob;
use App\Jobs\auth\storehouse\StoreRegisterJob;
use App\Models\User;
use App\Services\BaseService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class StorehouseAuthService extends BaseService
{
    use Base64Trait;
    /**
     * @param RegisterRequest
     * @return Response
     */
    public function register($request): Response
    {
        DB::beginTransaction();

        $user = User::create([
            'store_name' => $request->store_name,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => bcrypt($request->password)
        ]);

        $token = $user->createToken('Register Token')->accessToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        try {

            StoreRegisterJob::dispatch($user->toArray())->onQueue('admin');
            StoreRegisterJob::dispatch($user->toArray())->onQueue('main');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->customResponse(false, 'Bad Internet', null, 504);
        }
        DB::commit();

        return $this->customResponse(true, 'Register Success', $response);
    }

    /**
     * @param LoginRequest
     * @return Response
     */
    public function login($request): Response
    {
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];
//        config(['auth.guards.user-api.provider' => 'user']);
        if (auth()->attempt($data)) {
            $user = User::find(auth()->user()->id);
            $token = $user->createToken('Login Token')->accessToken;

            $response = [
                'user' => $user,
                'accessToken' => $token
            ];
            return $this->customResponse(true, 'Login success', $response);
        } else
            return $this->customResponse(false, 'Password is wrong', null, 400);
    }

    /**
     * @param Request
     * @return Response
     */
    public function logout($request): Response
    {
        $request->user()->token()->revoke();
        return $this->customResponse(true, 'Logout success');
    }

    /**
     * @param StorehouseAuthFileRequest
     * @return Response
     */
    public function sendAuthFiles(StorehouseAuthFileRequest $request): Response
    {
        DB::beginTransaction();

        $user = User::find(auth()->user()->id);

        if ($user->authenticated == 0 || $user->authenticated % 2 != 0 && $user->authenticated >= 3) {

            $storehouse_photo = array();

            if ($request->has('storehouse_photo')) {

                $files = $request->file('storehouse_photo');

                if ($files != null) {

                    foreach ($files as $file) {
                        $storehouse_photo[] = [$this->base64Encode($file), $file->getClientOriginalExtension()];
                    }
                }
            }

            $IDphoto = array();

            if ($request->has('IDphoto')) {

                $files = $request->file('IDphoto');

                if ($files != null) {

                    foreach ($files as $file) {

                        $IDphoto[] = [$this->base64Encode($file), $file->getClientOriginalExtension()];
                    }
                }
            }

            if($user->authenticated == 0)
                $user->authenticated = 2;
            else
                $user->authenticated++;
            $user->update();

            $response = [
                'storehouse_photo' => $storehouse_photo,
                'IDphoto' => $IDphoto,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'user_email' => $user->email,
            ];

            try {

                SendAuthFilesJob::dispatch($response)->onQueue('admin');

            } catch (\Exception $e) {
                DB::rollBack();
                return $this->customResponse(false, 'Bad Internet', null, 504);
            }
            DB::commit();

            return $this->customResponse(true, "send auth files success");

        }else
            return $this->customResponse(false, 'already sent', null, 400);
    }
}
