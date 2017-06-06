<?
ini_set('date.timezone', 'Asia/Tehran');
require_once __DIR__ . '/vendor/autoload.php'; // Autoload files using Composer autoload

use RahbodGoogleApi\GoogleOAuth;
use RahbodGoogleApi\GoogleCalendar;
$client_id = '601203669206-g6ph1uud300ibqt5iv5dt3rkdi6v0nk8.apps.googleusercontent.com';
$client_secret = 'gVIv0owoL4ZcHt2mjnrfiPcP';
$redirect_uri = 'http://localhost/Oauth2-google/';
$oauth = new GoogleOAuth('http://localhost/Oauth2-google/');
$oauth->setClientId($client_id);
$oauth->setClientSecret($client_secret);
$oauth->setRedirectUri($redirect_uri);
$oauth->setAccessType('offline');
$oauth->setScope("https://www.googleapis.com/auth/calendar https://www.googleapis.com/auth/drive");
if(file_exists('cred.json')){
    $oauth->setToken(file_get_contents('cred.json'));
}elseif(!file_exists('cred.json') && $oauth->authenticate()){
    $json = json_encode($oauth->getToken());
    file_put_contents('cred.json',$json);
}
if($oauth->isAccessTokenExpired() && $oauth->getRefreshToken()){
    $oauth->refreshAccessToken();
    $json = json_encode($oauth->getToken());
    file_put_contents('cred.json',$json);
    echo 'Token is Refreshed.';
}elseif($oauth->isAccessTokenExpired())
    echo 'Access Token not expired. Expired in: '.date('Y/m/d H:i:s',$oauth->getToken()['created']+$oauth->getToken()['expires_in']);
elseif(!$oauth->getRefreshToken())
    echo 'Access Token expired. refresh token not exists.';
$calendar = new GoogleCalendar($oauth->getAccessToken());
$calendarId = 'primary';
$optParams = array(
);
$results = $calendar->getCalendarList($optParams);
var_dump($results);exit;