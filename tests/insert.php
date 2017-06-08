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
$eventModel->addAttachment(
    'https://drive.google.com/file/d/0B8V9PkAm4QfTbnBMODQtc1pvanc/view?usp=drivesdk', // required
    'BBC 6 Minute English-2015.rar', // optional
    'https://drive-thirdparty.googleusercontent.com/16/type/application/rar', // optional
    '0B8V9PkAm4QfTbnBMODQtc1pvanc', // optional
    'application/rar' // optional
);
$calendarId = 'primary';
$services->calendar->insert($calendarId,$eventModel);