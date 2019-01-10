<?php

namespace Uccello\Calendar\Http\Controllers\Google;

use Uccello\Core\Http\Controllers\Core\Controller;
use Google\Auth\OAuth2;
use Google\Client;

use Uccello\Calendar\CalendarToken;


class AuthController extends Controller
{
    public function signin()
    {
        // Initialize the OAuth client
        $oauthClient = new \Google_Client([
            'application_name'          => env('APP_NAME'),
            'client_id'                 => env('GOOGLE_CLIENT_ID'),
            'client_secret'             => env('GOOGLE_CLIENT_SECRET'),
            'redirect_uri'              => env('GOOGLE_REDIRECT_URI'),
        ]);

        $oauthClient->addScope(\Google_Service_Calendar::CALENDAR);
        $oauthClient->setAccessType('offline');
        $oauthClient->setApprovalPrompt('force');

        $oauthClient->setRedirectUri('http://localhost:8000');

        // // Generate the auth URL
        // $authorizationUrl = $oauthClient->getAuthorizationUrl();

        // // Save client state so we can validate in response
        // $_SESSION['oauth_state'] = $oauthClient->getState();

        // Redirect to authorization endpoint
        return redirect(
            $oauthClient->createAuthUrl()
        );
    }

  public function gettoken()
  {
        // Authorization code should be in the "code" query param
        if (isset($_GET['code'])) {

            // Initialize the OAuth client
            $oauthClient = new \Google_Client([
                'application_name'          => env('APP_NAME'),
                'client_id'                 => env('GOOGLE_CLIENT_ID'),
                'client_secret'             => env('GOOGLE_CLIENT_SECRET'),
                'redirect_uri'              => env('GOOGLE_REDIRECT_URI'),
            ]);
            $oauthClient->addScope(\Google_Service_Calendar::CALENDAR);
            $oauthClient->setAccessType('offline');

            $oauthClient->authenticate($_GET['code']);

            // Save the access token and refresh tokens in session
            // This is for demo purposes only. A better method would
            // be to store the refresh token in a secured database
            $tokenDb = new \Uccello\Calendar\CalendarToken([
                'user_id' => 1, //TODO : Change this
                'service_name' => 'google',
                'token' => $oauthClient->getAccessToken()['access_token'],
                'refresh_token' => $oauthClient->getRefreshToken(),
                'expiration'  => $oauthClient->getAccessToken()['created'].','.$oauthClient->getAccessToken()['expires_in']
            ]);

            $tokenDb->save();

            return redirect()->route('uccello.list', ['domain' => 'default', 'module' => 'calendar']);
        
        }
        elseif (isset($_GET['error'])) {
            exit('ERROR: '.$_GET['error'].' - '.$_GET['error_description']);
        }
    }

    public static function getAccessToken(CalendarToken $calendarToken){

        // Initialize the OAuth client
        $oauthClient = new \Google_Client([
            'application_name'          => env('APP_NAME'),
            'client_id'                 => env('GOOGLE_CLIENT_ID'),
            'client_secret'             => env('GOOGLE_CLIENT_SECRET'),
            'redirect_uri'              => env('GOOGLE_REDIRECT_URI'),
        ]);
        $oauthClient->addScope(\Google_Service_Calendar::CALENDAR);
        $oauthClient->setAccessType('offline');
        $oauthClient->setAccessToken($calendarToken->token);
        $oauthClient->setRefreshToken($calendarToken->refresh_token);
        // $t = $oauthClient->getAccessToken();
        // $t['expires_in'] = '10';
        // $oauthClient->setAccessToken($t);

        //dd($oauthClient);
        

        if($oauthClient->isAccessTokenExpired())
        {
            dd($oauthClient->getRefreshToken());
            $oauthClient->fetchAccessTokenWithRefreshToken($oauthClient->getRefreshToken());
            dd('1');
            $calendarToken->token = $oauthClient->getAccessToken();
            $calendarToken->expiration = intval($oauthClient->getAccessToken()['created'])+intval($oauthClient->getAccessToken()['expires_in']);
            $calendarToken->save();
        }

        return $calendarToken->token;
    }
}