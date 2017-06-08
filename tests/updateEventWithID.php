<?
ini_set('date.timezone', 'Asia/Tehran');
require_once __DIR__ . '../vendor/autoload.php'; // Autoload files using Composer autoload
$services = new GoogleServices();

// load auth token from db
//$services->setAuthToken($token->access_token,$token->token_type,$token->expires_in,$token->created,$token->refresh_token);


// create new object from CalendarEventModel and fill properties that you want to be updated
$eventModel = $services->calendar->getNewEventModel();
$eventModel->location= "My Home.";
$eventModel->status= "confirmed";
$eventModel->setEnd('6 pm','Asia/Tehran');
$eventModel->attachments = null;
$calendarId = 'primary';

$eventId = '540aa34l4aceeris9gf6hk7go8';
$services->calendar->update($calendarId,$eventId,$eventModel);