<?php namespace SunLab\SSOClient\Helpers;

use Cms\Classes\Page;

class FormHelper
{
    public static function loginPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('title', 'baseFileName');
    }
}
