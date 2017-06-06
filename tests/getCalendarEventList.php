<?
ini_set('date.timezone', 'Asia/Tehran');
require_once __DIR__ . '../vendor/autoload.php'; // Autoload files using Composer autoload
use RahbodGoogleApi\GoogleOAuth;
use RahbodGoogleApi\GoogleCalendar;
$client_id = '601203669206-g6ph1uud300ibqt5iv5dt3rkdi6v0nk8.apps.googleusercontent.com';
$client_secret = 'gVIv0owoL4ZcHt2mjnrfiPcP';
$redirect_uri = 'http://localhost/Oauth2-google/';
$oauth = new GoogleOAuth('http://localhost/Oauth2-google/');
$calendar = new GoogleCalendar($oauth->getAccessToken());
$calendarId = 'primary';
$optParams = array(
    'timeMin'=>date('c', strtotime("8 am")),
    'timeMax'=>date('c', strtotime("5 pm")),
    'maxResults'=>5,
);
$results = $calendar->getListEvents($calendarId, $optParams);