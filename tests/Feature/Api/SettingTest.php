<?php

namespace Tests\Feature\Api;

use App\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_return_forbidden_response_if_loggin_user_is_not_admin()
    {
        $this->loggedInUser->update(['is_admin' => 0]);

        $this->getJson('api/settings', $this->headers)->assertStatus(403);

        $this->patchJson('api/settings/1', [], $this->headers)->assertStatus(403);
    }

    /** @test */
    public function it_return_valid_json_for_index_api()
    {
        factory(Setting::class)->times(2)->create();

        $response = $this->getJson('api/settings', $this->headers);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'settings' => [
                    '*' => [
                        'id',
                        'name',
                        'value'
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_return_422_response_when_value_is_not_presented()
    {
        $setting = factory(Setting::class)->create();

        $data = [
            'value' => null
        ];

        $response = $this->patchJson("api/settings/{$setting->id}", $data, $this->headers);
        $response->assertStatus(422)
            ->assertJson([
                'errors' => [
                    'value' => ['field is required.'],
                ]
            ]);
    }

    /** @test */
    public function it_successfully_update_setting_value()
    {
        $setting = factory(Setting::class)->create();

        $data = [
            'value' => 20000
        ];

        $response = $this->patchJson("api/settings/{$setting->id}", $data, $this->headers);
        $response->assertStatus(200)
            ->assertJson([
                'setting' => [
                    'id' => $setting['id'],
                    'name' => $setting['name'],
                    'value' => $data['value'],
                ]
            ]);
    }

    /** @test */
    public function it_save_setting_value_in_cache_on_calling_get()
    {
        $setting = factory(Setting::class)->create();

        Cache::shouldReceive('rememberForever')
            ->once()
            ->with("setting_{$setting->name}", \Closure::class)
            ->andReturn($setting->value);

        setting($setting->name);
    }

    /** @test */
    public function it_remove_cache_value_on_update()
    {
        $setting = factory(Setting::class)->create();

        Cache::shouldReceive('rememberForever')
            ->once()
            ->with("setting_{$setting->name}", \Closure::class)
            ->andReturn($setting->value);

        setting($setting->name);

        $data = [
            'value' => 20000
        ];

        Cache::shouldReceive('forget')
            ->once()
            ->with("setting_{$setting->name}");

        $this->patchJson("api/settings/{$setting->id}", $data, $this->headers)->assertStatus(200);
    }
}