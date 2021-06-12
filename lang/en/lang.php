<?php return [
    'plugin' => [
        'name' => 'SSO Client',
        'desc' => 'Allows your website to use the user-base from another website.'
    ],

    'settings' => [
        'label' => 'SSO Client',
        'desc' => 'Get your credentials'
    ],

    'permissions' => [
        'settings' => 'Manage settings'
    ],

    'components' => [
        'login_button' => [
            'name' => 'Login button',
            'desc' => 'Display the SSO Login button',
        ]
    ],

    'fields' => [
        'is_active' => 'Is active',
        'is_active_comment' => 'Disable/Enable the SSO button on login form.',
        'login_page' => 'Login page',
        'login_page_comment' => 'Will be sent to the provider as the callback url.',
        'provider_host' => 'Provider host',
        'provider_host_comment' => 'Domain name of the provider, eg. https://wintercms.com',
        'get_client_credentials' => '',
        'get_client_credentials_comment' => 'Will ask the provider to get your credentials',
        'provider_url' => "Provider's url",
        'provider_url_comment' => 'Filled by the provider',
        'secret' => 'Secret key',
        'secret_comment' => 'Filled by the provider'
    ],

    'get_client_credentials' => "Get client's credentials",

    'errors' => [
        'unknown' => 'An error occurred.',
        'err_n_1' => 'SSO Provider is not configured yet.',
        'err_n_2' => "SSO Provider doesn't accept new clients.",
    ]
];