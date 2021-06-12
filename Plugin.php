<?php namespace SunLab\SSOClient;

use Backend;
use Cms\Classes\Page;
use Illuminate\Support\Facades\Request;
use SunLab\SSOClient\Models\Settings;
use System\Classes\PluginBase;
use Validator;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Flash;
use Winter\Storm\Support\Facades\Http;
use Winter\Storm\Support\Facades\Str;

/**
 * SSOClient Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'sunlab.ssoclient::lang.plugin.name',
            'description' => 'sunlab.ssoclient::lang.plugin.desc',
            'author'      => 'SunLab',
            'icon'        => 'icon-key'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        \System\Controllers\Settings::extend(function (\System\Controllers\Settings $controller) {
            if (url()->current() === Backend\Facades\Backend::url('system/settings')) {
                return;
            }

            if ($controller->formGetWidget()->model instanceof \SunLab\SSOClient\Models\Settings) {
                $controller->addDynamicMethod('onGetSecretKey', static function () use ($controller) {
                    $host = post('Settings[provider_host]');

                    $validator = Validator::make(
                        ['host' => $host],
                        ['host' => 'required|url']
                    );

                    throw_if(
                        $validator->fails(),
                        new ValidationException($validator)
                    );

                    $callback_url = Page::url(post('Settings[login_page]'));
                    $req = Http::put(
                        sprintf('%s/sunlab_sso/client', $host),
                        static function ($http) use ($callback_url) {
                            $http->data([
                                'name' => config('app.name'),
                                'callback_url' => $callback_url
                            ]);
                        }
                    );

                    if (!$req->ok && $req->code !== 406) {
                        Flash::error(__('sunlab.ssoclient::lang.errors.unable_to_reach_provider'));
                        return;
                    }

                    $response = json_decode($req->body, true);
                    if ($req->code === 406) {
                        Flash::warning(__('sunlab.ssoclient::lang.errors.err_n_' . $response['err_n']));
                        return;
                    }

                    if (!isset($response['provider_url'], $response['secret'])) {
                        Flash::error(__('sunlab.ssoclient::lang.errors.unknown'));
                        return;
                    }

                    return json_encode($response);
                });
            }
        });
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            \SunLab\SSOClient\Components\LoginButton::class => 'SSOLoginButton',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'sunlab.ssoclient.settings' => [
                'tab' => 'sunlab.ssoclient::lang.plugin.name',
                'label' => 'sunlab.ssoclient::lang.permissions.settings'
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'sunlab.ssoclient::lang.settings.label',
                'description' => 'sunlab.ssoclient::lang.settings.desc',
                'category'    => 'sunlab.ssoclient::lang.plugin.name',
                'icon'        => 'icon-key',
                'class'       => Settings::class,
                'order'       => 500,
                'permissions' => ['sunlab.ssoclient.settings']
            ]
        ];
    }
}
