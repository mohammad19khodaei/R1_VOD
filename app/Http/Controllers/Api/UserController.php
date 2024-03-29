<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ChargeUser;
use App\Http\Requests\Api\UpdateUser;
use App\RealWorld\Transformers\UserTransformer;
use App\Services\UserBalanceService;
use App\User;
use Illuminate\Support\Facades\Log;

class UserController extends ApiController
{
    /**
     * UserController constructor.
     *
     * @param UserTransformer $transformer
     */
    public function __construct(UserTransformer $transformer)
    {
        $this->transformer = $transformer;

        $this->middleware('auth.api');
    }

    /**
     * Get the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->respondWithTransformer(auth()->user());
    }

    /**
     * Update the authenticated user and return the user if successful.
     *
     * @param UpdateUser $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateUser $request)
    {
        $user = auth()->user();

        if ($request->has('user')) {
            $user->update($request->get('user'));
        }

        return $this->respondWithTransformer($user);
    }

    public function charge(ChargeUser $request)
    {
        /** @var User $user */
        $user = auth()->user();
        try {
            $user = (new UserBalanceService($user))->chargeUser($request->get('amount'));
        } catch (\Exception $exception) {
            Log::info($exception->getMessage());
            return $this->respondInternalError();
        }

        return $this->respondWithTransformer($user);
    }
}
