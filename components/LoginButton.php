<?php namespace SunLab\SSOClient\Components;

use Cms\Classes\ComponentBase;
use Firebase\JWT\JWT;
use SunLab\SSOClient\Models\Settings;
use Tymon\JWTAuth\Exceptions\JWTException;
use Winter\Storm\Support\Facades\Flash;
use Winter\Storm\Support\Facades\Str;
use Winter\User\Facades\Auth;
use Winter\User\Models\User;

class LoginButton extends ComponentBase
{
    public $providerUrl;
    public $isActive;

    public function init()
    {
        $settings = Settings::instance();

        if ($token = $this->param($settings->token_url_param)) {
            try {
                $data = (array)JWT::decode($token, $settings->secret, ['HS256']);

                $user = User::query()->firstWhere('email', $data['email']);

                if (!$user) {
                    $password = Str::random(10);

                    $user = Auth::register(array_merge($data, [
                        'password' => $password,
                        'password_confirmation' => $password,
                    ]), true);
                }

                Auth::login($user);
            } catch (JWTException $e) {
                Flash::error('sunlab.ssoclient::lang.errors.unknown');
            }
        }

        $this->isActive = $settings->is_active;
        $this->providerUrl = $settings->provider_url;
    }

    public function componentDetails()
    {
        return [
            'name'        => 'sunlab.ssoclient::lang.components.login_button.name',
            'description' => 'sunlab.ssoclient::lang.components.login_button.desc'
        ];
    }

    public function defineProperties()
    {
        return [];
    }
}
