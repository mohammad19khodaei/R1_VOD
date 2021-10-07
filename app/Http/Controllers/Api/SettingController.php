<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UpdateSetting;
use App\RealWorld\Transformers\SettingTransformer;
use App\Services\SettingService;
use App\Setting;

class SettingController extends ApiController
{
    public function __construct(SettingTransformer $transformer)
    {
        $this->transformer = $transformer;
        $this->middleware(['auth.api', 'check.user.is.admin']);
    }

    public function index()
    {
        return $this->respondWithTransformer(Setting::all());
    }

    public function update(UpdateSetting $request, Setting $setting)
    {
        $setting = (new SettingService())->updateSetting($setting, $request->validated());

        return $this->respondWithTransformer($setting);
    }
}
