<?php

namespace App\Http\Controllers\Api;

use App\RealWorld\Transformers\UserFactorTransformer;
use App\User;

class UserFactorController extends ApiController
{
    /**
     * UserController constructor.
     *
     * @param UserFactorTransformer $transformer
     */
    public function __construct(UserFactorTransformer $transformer)
    {
        $this->transformer = $transformer;

        $this->middleware('auth.api');
    }

    public function __invoke()
    {
        /** @var User $user */
        $user = auth()->user();
        $factors = $user->factors()->with('transaction')->get();
        // TODO convert it to pagination response
        return $this->respondWithTransformer($factors);
    }
}
