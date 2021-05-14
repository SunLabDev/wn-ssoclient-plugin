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
    public $providerUrl = null;

    public function init()
    {
        $routeParameter = $this->property('paramCode');

        if ($token = $this->param($routeParameter)) {
            try {
                $data = (array)JWT::decode($token, Settings::instance()->get('secret'), ['HS256']);

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
                Flash::error('An error occurred');
            }
        }

        $this->providerUrl = Settings::instance()->get('provider_url');
    }

    public function componentDetails()
    {
        return [
            'name'        => 'LoginButton Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [
            'paramToken' => [
                'title'       => 'sunlab.ssoclient::lang.login_button.token_param',
                'description' => 'sunlab.ssoclient::lang.login_button.token_param_desc',
                'type'        => 'string',
                'default'     => 'code'
            ]
        ];
    }
}
