<?php

namespace Uccello\Calendar\Http\Controllers\Microsoft;

use Uccello\Core\Http\Controllers\Core\Controller;
use Uccello\Calendar\CalendarToken;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

class AuthController extends Controller
{
    public function signin()
    {

        // Initialize the OAuth client
        $oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => env('OAUTH_APP_ID'),
            'clientSecret'            => env('OAUTH_APP_PASSWORD'),
            'redirectUri'             => env('OAUTH_REDIRECT_URI'),
            'urlAuthorize'            => env('OAUTH_AUTHORITY').env('OAUTH_AUTHORIZE_ENDPOINT'),
            'urlAccessToken'          => env('OAUTH_AUTHORITY').env('OAUTH_TOKEN_ENDPOINT'),
            'urlResourceOwnerDetails' => '',
            'scopes'                  => env('OAUTH_SCOPES')
        ]);

        // Generate the auth URL
        $authorizationUrl = $oauthClient->getAuthorizationUrl();

        // Save client state so we can validate in response
        session()->put('oauth_state', $oauthClient->getState());

        // Redirect to authorization endpoint
        return redirect($authorizationUrl);
    }

  public function gettoken()
  {

        // Authorization code should be in the "code" query param
        if (isset($_GET['code'])) {
            // Check that state matches
            if (empty($_GET['state']) || ($_GET['state'] !== session('oauth_state'))) {
                exit('State provided in redirect does not match expected value.');
            }

            // Clear saved state
            session()->forget('oauth_state');

            // Initialize the OAuth client
            $oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
                'clientId'                => env('OAUTH_APP_ID'),
                'clientSecret'            => env('OAUTH_APP_PASSWORD'),
                'redirectUri'             => env('OAUTH_REDIRECT_URI'),
                'urlAuthorize'            => env('OAUTH_AUTHORITY').env('OAUTH_AUTHORIZE_ENDPOINT'),
                'urlAccessToken'          => env('OAUTH_AUTHORITY').env('OAUTH_TOKEN_ENDPOINT'),
                'urlResourceOwnerDetails' => '',
                'scopes'                  => env('OAUTH_SCOPES')
            ]);

            try {
                // Make the token request
                $accessToken = $oauthClient->getAccessToken('authorization_code', [
                    'code' => $_GET['code']
                ]);


                //Graph instanciation to retrieve user email
                $graph = new Graph();
                $graph->setAccessToken($accessToken->getToken());
                $user = $graph->createRequest('GET', '/me')
                                ->setReturnType(Model\User::class)
                                ->execute();

                // Create or retrieve token from database
                $tokenDb = \Uccello\Calendar\CalendarToken::firstOrNew([
                    'service_name'  => 'microsoft',
                    'user_id'       => \Auth::id(),
                    'username'      => $user->getUserPrincipalName(),
                ]);

                
                $tokenDb->token = $accessToken->getToken();
                $tokenDb->refresh_token = $accessToken->getRefreshToken();
                $tokenDb->expiration = $accessToken->getExpires();
                
                $tokenDb->save();

                // Redirect back to home page
                return redirect()->route('uccello.calendar.manage', ['domain' => 'default', 'module' => 'calendar']);
            }
            catch (League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
                exit('ERROR getting tokens: '.$e->getMessage());
            }
            exit();
        }
        elseif (isset($_GET['error'])) {
            exit('ERROR: '.$_GET['error'].' - '.$_GET['error_description']);
        }
    }

    public static function getAccessToken(CalendarToken $calendarToken){

        $now = time() + 300;

        if($calendarToken->expiration <= $now)
        // Token is expired (or very close to it) so let's refresh
        {
            // Initialize the OAuth client
            $oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
                'clientId'                => env('OAUTH_APP_ID'),
                'clientSecret'            => env('OAUTH_APP_PASSWORD'),
                'redirectUri'             => env('OAUTH_REDIRECT_URI'),
                'urlAuthorize'            => env('OAUTH_AUTHORITY').env('OAUTH_AUTHORIZE_ENDPOINT'),
                'urlAccessToken'          => env('OAUTH_AUTHORITY').env('OAUTH_TOKEN_ENDPOINT'),
                'urlResourceOwnerDetails' => '',
                'scopes'                  => env('OAUTH_SCOPES')
            ]);

            try {
                $newToken = $oauthClient->getAccessToken('refresh_token', [
                'refresh_token' => $calendarToken->refresh_token
                ]);

                // Store the new values

                $calendarToken->token = $newToken->getToken();
                $calendarToken->refresh_token = $newToken->getRefreshToken();
                $calendarToken->expiration = $newToken->getExpires();

                $calendarToken->save();

                return $calendarToken->token;
            }
            catch (League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
                return '';
            }   
        }

        return $calendarToken->token;
    }
}