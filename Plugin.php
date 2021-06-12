<?php namespace SunLab\SSOClient;

use Backend;
use Cms\Classes\Page;
use Illuminate\Support\Facades\Request;
use SunLab\SSOClient\Models\Settings;
use System\Classes\PluginBase;
use Validator;
use Winter\Storm\Database\Models\DeferredBinding as DeferredBindingModel;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Flash;
use Winter\Storm\Support\Facades\Http;

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

            $formWidget = $controller->formGetWidget();
            $model = $formWidget->model;
            if ($model instanceof \SunLab\SSOClient\Models\Settings) {
                $controller->addDynamicMethod('onGetSecretKey', static function () use ($formWidget, $model) {
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

                    // Get deferred splash_image, fallback to model's splash_image or leave null
                    $sessionKey = $formWidget->getSessionKey();
                    $binding = new DeferredBindingModel;

                    $binding->setConnection($model->getConnectionName());

                    $splash_image = null;
                    $deferred_splash_image =
                        $binding->where([
                                    ['master_type', '=', get_class($model)],
                                    ['session_key', '=', $sessionKey],
                                    ['is_bind', '=', 1]
                                ])
                                ->first();

                    if ($deferred_splash_image) {
                        $image = \System\Models\File::findOrFail($deferred_splash_image->slave_id);
                        $splash_image = $image->getThumb('500', '500');
                    }

                    if (!$splash_image && $model->splash_image) {
                        $splash_image = $model->splash_image->getThumb('500', '500');
                    }

                    $req = Http::put(
                        sprintf('%s/sunlab_sso/client', $host),
                        static function ($http) use ($callback_url, $splash_image) {
                            $http->data([
                                'name' => post('Settings[name]'),
                                'splash_image' => $splash_image,
                                'callback_url' => $callback_url,
                            ]);
                        }
                    );

                    if (!$req->ok && $req->code !== 406) {
                        Flash::error(__('sunlab.ssoclient::lang.errors.unable_to_reach_provider'));
                        return;
                    }

                    $response = json_decode($req->body, true);
                    if ($req->code === 406) {
                        if (isset($response['err_n'])) {
                            Flash::warning(__('sunlab.ssoclient::lang.errors.err_n_' . $response['err_n']));
                        } elseif (isset($response['reason'])) {
                            Flash::error($response['reason']);
                        } else {
                            Flash::error(__('sunlab.ssoclient::lang.errors.err_n_' . $response['err_n']));
                        }

                        return;
                    }

                    if (!isset($response['provider_url'], $response['secret'], $response['token_url_param'])) {
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
