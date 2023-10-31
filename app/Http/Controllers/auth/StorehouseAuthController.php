<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\auth\LoginRequest;
use App\Http\Requests\auth\RegisterRequest;
use App\Http\Requests\auth\StorehouseAuthFileRequest;
use App\Services\Auth\StorehouseAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StorehouseAuthController extends Controller
{
    /**
     * The auth service implementation.
     *
     * @var StorehouseAuthService
     */
    protected StorehouseAuthService $storehouseAuthService;

    // singleton pattern, service container
    public function __construct(StorehouseAuthService $storehouseAuthService)
    {
        $this->storehouseAuthService = $storehouseAuthService;
    }

    public function register(RegisterRequest $request): Response
    {
        return $this->storehouseAuthService->register($request);
    }

    public function login(LoginRequest $request): Response
    {
        return $this->storehouseAuthService->login($request);
    }

    public function logout(Request $request): Response
    {
        return $this->storehouseAuthService->logout($request);
    }

    public function sendAuthFiles(StorehouseAuthFileRequest $request): Response
    {
        return $this->storehouseAuthService->sendAuthFiles($request);
    }
}
