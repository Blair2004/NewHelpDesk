<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\UserLicense;
use Illuminate\Support\Facades\Http;

class OAuthService
{
    public function getAuthorizationUrl(): string
    {
        $scopes = $this->getActiveScopes();
        
        $params = http_build_query([
            'client_id' => config('oauth.client_id'),
            'redirect_uri' => config('oauth.redirect_uri'),
            'response_type' => 'code',
            'scope' => implode(' ', $scopes),
        ]);

        return config('oauth.authorize_url').'?'.$params;
    }

    protected function getActiveScopes(): array
    {
        $scopes = [];
        
        // Get scopes from settings or fall back to config
        $profileScope = $this->getSetting('oauth_scope_profile', config('oauth.scopes.profile'));
        $licensesReadScope = $this->getSetting('oauth_scope_licenses_read', config('oauth.scopes.licenses_read'));
        $licensesUpdateScope = $this->getSetting('oauth_scope_licenses_update', config('oauth.scopes.licenses_update'));
        
        if ($profileScope) {
            $scopes[] = $profileScope;
        }
        
        if ($licensesReadScope) {
            $scopes[] = $licensesReadScope;
        }
        
        // Only include update scope if enabled in settings
        if ($this->getSetting('oauth_enable_licenses_update', false) && $licensesUpdateScope) {
            $scopes[] = $licensesUpdateScope;
        }
        
        return $scopes;
    }

    protected function getSetting(string $key, mixed $default = null): mixed
    {
        $setting = \App\Models\Setting::where('key', $key)->first();
        
        return $setting ? $setting->getValue() : $default;
    }

    public function exchangeCodeForToken(string $code): array
    {
        $response = Http::asForm()->post(config('oauth.token_url'), [
            'grant_type' => 'authorization_code',
            'client_id' => config('oauth.client_id'),
            'client_secret' => config('oauth.client_secret'),
            'redirect_uri' => config('oauth.redirect_uri'),
            'code' => $code,
        ]);

        if (! $response->successful()) {
            throw new \Exception('Failed to exchange OAuth code for token');
        }

        return $response->json();
    }

    public function getUserInfo(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->get(config('oauth.user_url'));

        if (! $response->successful()) {
            throw new \Exception('Failed to fetch user information');
        }

        return $response->json();
    }

    public function getUserLicenses(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->get(config('oauth.licenses_url'));

        if (! $response->successful()) {
            throw new \Exception('Failed to fetch user licenses');
        }

        return $response->json();
    }

    public function createOrUpdateUser(array $userData): User
    {
        $user = User::updateOrCreate(
            ['external_user_id' => $userData[ config( 'oauth.user_id_field', 'id' ) ] ],
            [
                'name' => $userData[ config( 'oauth.user_name_field', 'name' ) ],
                'email' => $userData[ config( 'oauth.user_email_field', 'email' ) ],
                'avatar' => $userData[ config( 'oauth.user_avatar_field', 'avatar' ) ] ?? null,
                'preferred_locale' => $userData['locale'] ?? 'en',
                'last_login_at' => now(),
            ]
        );

        // First user becomes admin
        if (User::count() === 1) {
            $user->is_admin = true;
            $user->is_staff = true;
            $user->save();
        }

        return $user;
    }

    public function syncUserLicenses(User $user, array $licenses): void
    {
        // Mark all existing licenses as inactive
        $user->licenses()->update(['is_active' => false]);

        // Filter licenses based on configuration
        $validLicenses = $this->filterLicenses($licenses);

        foreach ($validLicenses as $licenseData) {
            $licenseIdField = config('oauth.license_id_field', 'id');
            $licenseNameField = config('oauth.license_name_field', 'name');
            $licenseItemIdField = config('oauth.license_item_id_field', 'item_id');
            $licenseCreatedField = config('oauth.license_created_field', 'purchased_at');
            $licenseExpiresField = config('oauth.license_expires_field', 'supported_until');
            $licenseActiveField = config('oauth.license_active_field', 'activated');
            $licenseDomainField = config('oauth.license_domain_field', 'domain');

            // Generate unique identifier for licenses without an external ID
            $externalLicenseId = $licenseData[$licenseIdField] ?? null;
            if (! $externalLicenseId) {
                // Create a hash based on license data to ensure uniqueness
                $externalLicenseId = md5(serialize([
                    $licenseData[$licenseItemIdField] ?? '',
                    $licenseData[$licenseNameField] ?? '',
                    $licenseData[$licenseDomainField] ?? '',
                ]));
            }

            UserLicense::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'external_license_id' => $externalLicenseId,
                ],
                [
                    'product_name' => $licenseData[$licenseNameField] ?? 'Unknown',
                    'item_id' => $licenseData[$licenseItemIdField] ?? null,
                    'domain' => $licenseData[$licenseDomainField] ?? null,
                    'purchased_at' => isset($licenseData[$licenseCreatedField]) 
                        ? \Carbon\Carbon::parse($licenseData[$licenseCreatedField]) 
                        : null,
                    'expires_at' => isset($licenseData[$licenseExpiresField]) 
                        ? \Carbon\Carbon::parse($licenseData[$licenseExpiresField]) 
                        : null,
                    'is_active' => $licenseData[$licenseActiveField] ?? true,
                ]
            );
        }
    }

    protected function filterLicenses(array $licenses): array
    {
        $filterField = config('oauth.license_filter_field');
        $filterValues = config('oauth.license_filter_values');

        // If no filter is configured, return all licenses
        if (empty($filterField) || empty($filterValues)) {
            return $licenses;
        }

        // Parse comma-separated filter values
        $allowedValues = array_map('trim', explode(',', $filterValues));

        // Filter licenses based on the configured field and values
        return array_filter($licenses, function ($license) use ($filterField, $allowedValues) {
            $fieldValue = $license[$filterField] ?? null;
            
            return $fieldValue && in_array((string) $fieldValue, $allowedValues, true);
        });
    }
}
