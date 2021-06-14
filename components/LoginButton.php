<?php namespace SunLab\SSOClient\Components;

use Cms\Classes\ComponentBase;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Request;
use SunLab\SSOClient\Models\Settings;
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

        // If the request already contains the token, try to log the user in
        if (Request::has($settings->token_url_param)) {
            try {
                $token = Request::get($settings->token_url_param);
                $data = (array)JWT::decode($token, $settings->secret, ['HS256']);

                $user = User::query()->firstWhere('email', $data['email']);

                // If the email is unknown, create a User model
                if (!$user) {
                    $password = Str::random(10);

                    $user = Auth::register(array_merge($data, [
                        'password' => $password,
                        'password_confirmation' => $password,
                    ]), true);
                }

                Auth::login($user);
            }

            // Handle eventual JWToken expiration
            catch (ExpiredException $e) {
                Flash::error(__('sunlab.ssoclient::lang.errors.expired_session'));
                return;
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
