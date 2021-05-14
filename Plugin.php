<?php namespace SunLab\SSOClient;

use Backend;
use SunLab\SSOClient\Models\Settings;
use System\Classes\PluginBase;

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
            'name'        => 'SSOClient',
            'description' => 'No description provided yet...',
            'author'      => 'SunLab',
            'icon'        => 'icon-leaf'
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
        return []; // Remove this line to activate

        return [
            'sunlab.ssoclient.some_permission' => [
                'tab' => 'SSOClient',
                'label' => 'Some permission'
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return []; // Remove this line to activate

        return [
            'ssoclient' => [
                'label'       => 'SSOClient',
                'url'         => Backend::url('sunlab/ssoclient/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['sunlab.ssoclient.*'],
                'order'       => 500,
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'sunlab.ssoclient::lang.settings.page_name',
                'description' => 'sunlab.ssoclient::lang.settings.page_desc',
                'category'    => 'sunlab.ssoclient::lang.plugin.name',
                'icon'        => 'oc-icon-key',
                'class'       => Settings::class,
                'order'       => 500,
                'permissions' => ['sunlab.ssoclient.settings']
            ]
        ];
    }
}
