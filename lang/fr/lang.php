<?php return [
    'plugin' => [
        'name' => 'Client SSO',
        'desc' => "Permet à votre site d'utiliser la base utilisateur d'un autre site."
    ],

    'settings' => [
        'label' => 'Client SSO',
        'desc' => 'Obtenez vos identifiants'
    ],

    'permissions' => [
        'settings' => 'Gérer les paramètres'
    ],

    'components' => [
        'login_button' => [
            'name' => 'Bouton de connexion',
            'desc' => 'Affiche le bouton de connexion SSO',
        ]
    ],

    'fields' => [
        'is_active' => 'Est actif',
        'is_active_comment' => 'Active/Désactive le bouton de connexion sur la page de connexion.',
        'login_page' => 'Page de connexion',
        'login_page_comment' => "Sera transmis au fournisseur en tant qu'URL de retour.",
        'provider_host' => 'Fournisseur hôte',
        'provider_host_comment' => 'Nom de domaine du fournisseur, eg. https://wintercms.com .',
        'get_client_credentials' => '',
        'get_client_credentials_comment' => 'Demandera vos identifiants unique au fournisseur.',
        'provider_url' => "URL du fournisseur",
        'provider_url_comment' => 'Transmis par le fournisseur.',
        'token_url_param' => "Paramètre du token dans l'URL",
        'token_url_param_comment' => 'Transmis par le fournisseur.',
        'secret' => 'Clé secrète',
        'secret_comment' => 'Transmis par le fournisseur.',
        'design_section' => "Apparence de la page d'autorisation",
        'design_section_comment' => "Personaliser votre page d'autorisation côté fournisseur",
        'name' => 'Nom',
        'name_comment' => 'Sera affiché comme ceci: {{ name }} veut accéder à vos informations.',
        'splash_image' => 'Image de présentation',
        'splash_image_comment' => "Sera affichée sur votre page d'autorisation"
    ],

    'get_client_credentials' => "Obtenir des identifiants",

    'errors' => [
        'unknown' => 'Une erreur est survenue.',
        'err_n_1' => "Le fournisseur SSO n'est pas encore configuré.",
        'err_n_2' => "Le fournisseur SSO n'accepte pas de nouveaux clients.",
        'expired_session' => 'Cette session a expirée.'
    ]
];
