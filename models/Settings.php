<?php namespace SunLab\SSOClient\Models;

use Model;

/**
 * Settings Model
 */
class Settings extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    public $rules = [];

    public $implement = [\System\Behaviors\SettingsModel::class];

    public $settingsCode = 'sunlab_ssoclient_settings';

    public $settingsFields = 'fields.yaml';
}
