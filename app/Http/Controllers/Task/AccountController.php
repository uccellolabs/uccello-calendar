<?php

namespace Uccello\Calendar\Http\Controllers\Task;

use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Calendar\CalendarAccount;

class AccountController extends Controller
{
    public function signin()
    {

        // // Initialize the OAuth client
        // $oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
        //     'clientId'                => env('OAUTH_APP_ID'),
        //     'clientSecret'            => env('OAUTH_APP_PASSWORD'),
        //     'redirectUri'             => env('OAUTH_REDIRECT_URI'),
        //     'urlAuthorize'            => env('OAUTH_AUTHORITY').env('OAUTH_AUTHORIZE_ENDPOINT'),
        //     'urlAccessToken'          => env('OAUTH_AUTHORITY').env('OAUTH_TOKEN_ENDPOINT'),
        //     'urlResourceOwnerDetails' => '',
        //     'scopes'                  => env('OAUTH_SCOPES')
        // ]);

        // // Generate the auth URL
        // $authorizationUrl = $oauthClient->getAuthorizationUrl();

        // // Save client state so we can validate in response
        // session()->put('oauth_state', $oauthClient->getState());

        // // Redirect to authorization endpoint
        // return redirect($authorizationUrl);
    }

    public function gettoken()
    {

    }

    public static function getAccessToken(CalendarAccount $calendarAccount){

        
    }

    public function initClient($accountId)
    {

    }
}