<?php

declare(strict_types=1);

return [
    'client_id' => env('OAUTH_CLIENT_ID'),
    'client_secret' => env('OAUTH_CLIENT_SECRET'),
    'redirect_uri' => env('OAUTH_REDIRECT_URI'),
    'authorize_url' => env('OAUTH_AUTHORIZE_URL'),
    'token_url' => env('OAUTH_TOKEN_URL'),
    'user_url' => env('OAUTH_USER_URL'),
    'licenses_url' => env('OAUTH_LICENSES_URL'),

    'user_id_field' => env('OAUTH_USER_ID_FIELD', 'id'),
    'user_name_field' => env('OAUTH_USER_NAME_FIELD', 'name'),
    'user_email_field' => env('OAUTH_USER_EMAIL_FIELD', 'email'),
    'user_avatar_field' => env('OAUTH_USER_AVATAR_FIELD', 'avatar'),
    
    // License field mappings
    'license_id_field' => env('OAUTH_LICENSE_ID_FIELD', 'id'),
    'license_name_field' => env('OAUTH_LICENSE_NAME_FIELD', 'name'),
    'license_item_id_field' => env('OAUTH_LICENSE_ITEM_ID_FIELD', 'item_id'),
    'license_created_field' => env('OAUTH_LICENSE_CREATED_FIELD', 'purchased_at'),
    'license_expires_field' => env('OAUTH_LICENSE_EXPIRES_FIELD', 'supported_until'),
    'license_active_field' => env('OAUTH_LICENSE_ACTIVE_FIELD', 'activated'),
    'license_domain_field' => env('OAUTH_LICENSE_DOMAIN_FIELD', 'domain'),
    
    // License validation rules
    'license_filter_field' => env('OAUTH_LICENSE_FILTER_FIELD', 'item_id'),
    'license_filter_values' => env('OAUTH_LICENSE_FILTER_VALUES', ''), // Comma-separated values
    
    'scopes' => [
        'profile' => env('OAUTH_SCOPE_PROFILE', 'read-profile'),
        'licenses_read' => env('OAUTH_SCOPE_LICENSES_READ', 'read-licenses'),
        'licenses_update' => env('OAUTH_SCOPE_LICENSES_UPDATE', 'update-licenses'),
    ],
];
