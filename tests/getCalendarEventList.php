<?
ini_set('date.timezone', 'Asia/Tehran');
require_once __DIR__ . '../vendor/autoload.php'; // Autoload files using Composer autoload
$services = new GoogleServices();
// load auth token from db
//$services->setAuthToken($token->access_token,$token->token_type,$token->expires_in,$token->created,$token->refresh_token);
$calendarId = 'primary';

/*
 * Example $options:
    array(
      'timeMin'=>date('c', strtotime("8 am")),
      'timeMax'=>date('c', strtotime("5 pm")),
      'maxResults'=>5,
      'orderBy'=>'startTime',
      'timeZone'=>'Asia/Tehran',
    )
 */
$optParams = array(
    'timeMin'=>"2017-06-05 8 am",
    'timeMax'=>"2017-06-08 5 pm",
    'maxResults'=>5,
);
$services->calendar->getListEvents($calendarId, $eventId);