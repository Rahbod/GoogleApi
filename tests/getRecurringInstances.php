<?
ini_set('date.timezone', 'Asia/Tehran');
require_once __DIR__ . '../vendor/autoload.php'; // Autoload files using Composer autoload
$services = new GoogleServices();
// load auth token from db
//$services->setAuthToken($token->access_token,$token->token_type,$token->expires_in,$token->created,$token->refresh_token);
$calendarId = 'primary';
$eventId = 'ri48q59314s7jou9h8ee034mjo'; // recurring event id
$services->calendar->getInstances($calendarId, $eventId); // response list of instances