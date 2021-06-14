<?php namespace SunLab\SSOClient;

use Backend;
use Cms\Classes\Page;
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
        // Extends the SettingsController to add the AJAX Handler needed to get clients credentials
        \System\Controllers\Settings::extend(function (\System\Controllers\Settings $controller) {
            if (url()->current() === Backend\Facades\Backend::url('system/settings')) {
                return;
            }

            $formWidget = $controller->formGetWidget();
            $model = $formWidget->model;

            if ($model instanceof \SunLab\SSOClient\Models\Settings) {
                $controller->addDynamicMethod('onGetSecretKey', static function () use ($formWidget, $model) {
                    return $this->onGetSecretKey($formWidget, $model);
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

    protected function onGetSecretKey($formWidget, $model)
    {
        $host = post('Settings[provider_host]');

        // Make sure the admin provided a host, abort if not
        $validator = Validator::make(
            ['host' => $host],
            ['host' => 'required|url']
        );

        throw_if(
            $validator->fails(),
            new ValidationException($validator)
        );

        // Locate splash_image, if any:
        // Priority:
        // - get deferred splash image, for first saving or when modified
        // - fallback to model's splash image
        // - leave null, no splash image
        $sessionKey = $formWidget->getSessionKey();
        $binding = new DeferredBindingModel;

        $binding->setConnection($model->getConnectionName());

        $splash_image = null;
        $deferred_splash_image = $binding->where([
            ['master_type', '=', get_class($model)],
            ['session_key', '=', $sessionKey],
            ['is_bind', '=', 1]
        ])->first();

        if ($deferred_splash_image) {
            $image = \System\Models\File::findOrFail($deferred_splash_image->slave_id);
            $splash_image = $image->getThumb('500', '500');
        }

        if (!$splash_image && $model->splash_image) {
            $splash_image = $model->splash_image->getThumb('500', '500');
        }

        // Build the callback URL
        $callback_url = Page::url(post('Settings[login_page]'));
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

        // Handle eventual errors

        // Provider can't be reached, 404 or plugin's not installed
        if (!$req->ok && $req->code !== 406) {
            Flash::error(__('sunlab.ssoclient::lang.errors.unable_to_reach_provider'));
            return;
        }

        $response = json_decode($req->body, true);
        if ($req->code === 406) {
            // If the provider returned an error n°, display the message as a warning
            if (isset($response['err_n'])) {
                Flash::warning(__('sunlab.ssoclient::lang.errors.err_n_' . $response['err_n']));
            }

            // Else if the provider returned an error reason, display it as an error
            elseif (isset($response['reason'])) {
                Flash::error($response['reason']);
            }

            // Else we don't know what happen
            else {
                Flash::error(__('sunlab.ssoclient::lang.errors.unknown'));
            }

            return;
        }

        // Make sure the provider returned all the needed credentials, if not display unknown error
        if (!isset($response['provider_url'], $response['secret'], $response['token_url_param'])) {
            Flash::error(__('sunlab.ssoclient::lang.errors.unknown'));
            return;
        }

        // If everything is fine:
        // return the credentials to the form to update the form fields
        return json_encode($response);
    }
}
