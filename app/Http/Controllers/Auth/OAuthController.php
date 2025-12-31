<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as RoutingController;
use Illuminate\Support\Facades\Auth;

class OAuthController extends RoutingController
{
    public function __construct(private readonly OAuthService $oauthService)
    {
    }

    public function redirect(): RedirectResponse
    {
        $authUrl = $this->oauthService->getAuthorizationUrl();

        return redirect()->away($authUrl);
    }

    public function callback(Request $request): RedirectResponse
    {
        if (! $request->has('code')) {
            $errorMessage = $request->input('error_description', 'Authorization failed');
            
            return redirect('/login?error='.urlencode($errorMessage));
        }

        try {
            $tokenData = $this->oauthService->exchangeCodeForToken($request->input('code'));
            $accessToken = $tokenData['access_token'];

            $userData = $this->oauthService->getUserInfo($accessToken);
            $user = $this->oauthService->createOrUpdateUser($userData);

            // Sync licenses
            $licenses = $this->oauthService->getUserLicenses($accessToken);
            $this->oauthService->syncUserLicenses($user, $licenses);

            Auth::login($user);

            return redirect()->intended('/dashboard');
        } catch (\Exception $e) {
            return redirect('/login?error='.urlencode($e->getMessage()));
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
