<?php namespace SunLab\SSOClient\Models;

use Model;
use System\Models\File;

/**
 * Settings Model
 */
class Settings extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    public $rules = [
        'provider_host' => 'required',
        'login_page' => 'required',
        'provider_url' => 'required',
        'secret' => 'required'
    ];

    public $implement = [\System\Behaviors\SettingsModel::class];

    public $settingsCode = 'sunlab_ssoclient_settings';

    public $settingsFields = 'fields.yaml';

    public $attachOne = ['splash_image' => File::class];
}
