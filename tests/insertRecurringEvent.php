<?
ini_set('date.timezone', 'Asia/Tehran');
require_once __DIR__ . '../vendor/autoload.php'; // Autoload files using Composer autoload

$services = new GoogleServices();
// load auth token from db
//$services->setAuthToken($token->access_token,$token->token_type,$token->expires_in,$token->created,$token->refresh_token);

// get new object from CalendarEventModel and set event fields
$eventModel = $services->calendar->getNewEventModel();
$eventModel->summary= "Hi";
$eventModel->description= "Hi Test.";
$eventModel->location= "My Office.";
$eventModel->setStart('8 am','Asia/Tehran');
$eventModel->setEnd('8 pm','Asia/Tehran');
$eventModel->recurrence = 'RRULE:FREQ=WEEKLY;BYDAY=Tu;UNTIL=20170606';
$calendarId = 'primary';
$services->calendar->insert($calendarId,$eventModel);