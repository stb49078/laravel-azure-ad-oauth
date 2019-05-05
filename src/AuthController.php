<?php

namespace Metrogistics\AzureSocialite;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class AuthController extends Controller
{
    public function redirectToOauthProvider()
    {
        return Socialite::driver('oauth-azure')->redirect();
    }

    public function handleOauthResponse(Request $request)
    {
        if (!$request->input('code')) {
            $redirect = redirect(config('oauth-azure.redirect_on_error'));
            $error = 'Login failed: ' .
                $request->input('error') .
                ' - ' .
                $request->input('error_description');
            return $redirect->withErrors($error);
        }

        try {
            $azure_user = Socialite::driver('oauth-azure')->user();
        } catch(InvalidStateException $e) {
            $azure_user = Socialite::driver('oauth-azure')->stateless()->user();
        }

        $user = $this->findOrCreateUser($azure_user);

        auth()->login($user, true);

        // session([
        //     'azure_user' => $user
        // ]);

        return redirect(
            config('oauth-azure.redirect_on_login')
        );
    }

    protected function findOrCreateUser($user)
    {
        $user_class = config('oauth-azure.user_class');
        $authUser = $user_class::where(config('oauth-azure.user_id_field'), $user->id)->first();

        if ($authUser) {
            return $authUser;
        }

        $UserFactory = new UserFactory();

        return $UserFactory->convertAzureUser($user);
    }
}
