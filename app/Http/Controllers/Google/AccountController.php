<?php

namespace Uccello\Calendar\Http\Controllers\Google;

use Uccello\Core\Http\Controllers\Core\Controller;
use Google\Auth\OAuth2;
use Google\Client;

use Uccello\Calendar\CalendarAccount;


class AccountController extends Controller
{
    public function signin()
    {
        // Initialize the Google Client
        $oauthClient = new \Google_Client([
            'application_name'          => env('APP_NAME'),
            'client_id'                 => env('GOOGLE_CLIENT_ID'),
            'client_secret'             => env('GOOGLE_CLIENT_SECRET'),
            'redirect_uri'              => env('GOOGLE_REDIRECT_URI'),
        ]);

        //Add authorizations needed : Calendar for Events and USERINFO to get username
        $oauthClient->addScope(\Google_Service_Calendar::CALENDAR);
        $oauthClient->addScope(\Google_Service_Oauth2::USERINFO_EMAIL);
        //Next two lines needed to get refresh token
        $oauthClient->setAccessType('offline');
        $oauthClient->setApprovalPrompt('force');

        // Redirect to authorization endpoint
        return redirect(
            $oauthClient->createAuthUrl()
        );
    }

    public function gettoken()
    {
        // Authorization code should be in the "code" query param
        if (isset($_GET['code'])) {

            // Initialize the Google Client
            $oauthClient = new \Google_Client([
                'application_name'          => env('APP_NAME'),
                'client_id'                 => env('GOOGLE_CLIENT_ID'),
                'client_secret'             => env('GOOGLE_CLIENT_SECRET'),
                'redirect_uri'              => env('GOOGLE_REDIRECT_URI'),
            ]);

            //Theses 2 scopes are needed to retrieve Calendar Events and user email
            $oauthClient->addScope(\Google_Service_Calendar::CALENDAR);
            $oauthClient->addScope(\Google_Service_Oauth2::USERINFO_EMAIL);
            $oauthClient->setAccessType('offline');

            $oauthClient->authenticate($_GET['code']);

            //Objects instanciation needed to retrieve email addresse
            $service = new \Google_Service_Oauth2($oauthClient);
            $tokeninfo = $service->tokeninfo(array("access_token" => $oauthClient->getAccessToken()['access_token']));

            //Create or retrieve token from database
            $tokenDb = \Uccello\Calendar\CalendarAccount::firstOrNew([
                'service_name'  => 'google',
                'user_id'       => \Auth::id(),
                'username'      => $tokeninfo->email,
            ]);


            $tokenDb->token = $oauthClient->getAccessToken()['access_token'];
            $tokenDb->refresh_token = $oauthClient->getRefreshToken();
            $tokenDb->expiration = $oauthClient->getAccessToken()['created'].','.$oauthClient->getAccessToken()['expires_in'];

            $tokenDb->save();

            return redirect()->route('uccello.calendar.manage', ['domain' => 'default', 'module' => 'calendar']);

        }
        elseif (isset($_GET['error'])) {
            exit('ERROR: '.$_GET['error'].' - '.$_GET['error_description']);
        }
    }

    public static function getAccessToken(CalendarAccount $calendarAccounts){

        // Initialize the Google_Client
        $oauthClient = new \Google_Client([
            'application_name'          => env('APP_NAME'),
            'client_id'                 => env('GOOGLE_CLIENT_ID'),
            'client_secret'             => env('GOOGLE_CLIENT_SECRET'),
            'redirect_uri'              => env('GOOGLE_REDIRECT_URI'),
        ]);
        $oauthClient->addScope(\Google_Service_Calendar::CALENDAR);
        $oauthClient->addScope(\Google_Service_Oauth2::USERINFO_EMAIL);
        $oauthClient->setAccessType('offline');
        $oauthClient->setAccessToken($calendarAccounts->token);

        //Retrieve and fill in token datas from database
        $t = $oauthClient->getAccessToken();
        $t['created'] = explode(',', $calendarAccounts->expiration)[0];
        $t['expires_in'] = explode(',', $calendarAccounts->expiration)[1];
        $t['refresh_token'] = $calendarAccounts->refresh_token;
        $oauthClient->setAccessToken($t);

        //If token is expired, refresh it and store new token
        if($oauthClient->isAccessTokenExpired())
        {
            $oauthClient->fetchAccessTokenWithRefreshToken($oauthClient->getRefreshToken());
            $calendarAccounts->token = $oauthClient->getAccessToken()['access_token'];
            $calendarAccounts->expiration = $oauthClient->getAccessToken()['created'].','.$oauthClient->getAccessToken()['expires_in'];

            $calendarAccounts->save();
        }

        return $calendarAccounts->token;
    }

    public function initClient($accountId)
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

        $account = \Uccello\Calendar\CalendarAccount::where([
            'service_name'  => 'google',
            'user_id'       => auth()->id(),
            'id'            => $accountId,
        ])->first();

        $oauthClient->setAccessToken(
            AccountController::getAccessToken($account)
        );

        return $oauthClient;
    }
}