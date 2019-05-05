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

    protected function findOrCreateUser($azure_user)
    {
	    $user_class = config('oauth-azure.user_class');
	    $user_field = config('oauth-azure.user_azure_field');

	    $authUser = $user_class::where(config('oauth-azure.user_id_field'), $azure_user->$user_field)->first();

	    $userFactory = new UserFactory();

	    if ($authUser) {
	    	$userFactory->userLogin($azure_user, $authUser);
		    return $authUser;

	    } else {
		    $convertAzureUser = $userFactory->convertAzureUser($azure_user, $authUser);
		    $userFactory->userLogin($azure_user, $convertAzureUser);
		    return $convertAzureUser;
	    }
    }
}
