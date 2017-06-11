<?
ini_set('date.timezone', 'Asia/Tehran');
require_once __DIR__ . '../vendor/autoload.php'; // Autoload files using Composer autoload
$services = new GoogleServices();

// load auth token from db
//$services->setAuthToken($token->access_token,$token->token_type,$token->expires_in,$token->created,$token->refresh_token);


// for delete or change any fields of all instances

// get new model and change any fields values
$eventModel = $services->calendar->getNewEventModel();
$eventModel->status= "cancelled";
//
$calendarId = 'primary';
$eventId = 's6mto323t90io0rnc5otqnkaus'; // recurring event id
$services->calendar->update($calendarId,$eventId,$eventModel); // run update request and update all instances of recurring event

$services->calendar->delete($calendarId,$eventId); // run delete request and delete all instances of recurring event


// for delete or change any fields of all instances

// get new model and change any fields values
$eventModel = $services->calendar->getNewEventModel();
$eventModel->summary= "Changed";
//
$calendarId = 'primary';
$eventId = 's6mto323t90io0rnc5otqnkaus'; // recurring event id
$services->calendar->update($calendarId,$eventId,$eventModel); // run update request and update all instances of recurring event

$services->calendar->delete($calendarId,$eventId); // run delete request and delete all instances of recurring event

//-----------------------------------------------------------------------------------------------------------------------------------

// for delete or change any fields of one specific instance

// first time get all instances of recurring event
$eventId = 's6mto323t90io0rnc5otqnkaus'; // recurring event id
// with optional params or not
/*
 * Example $options:
    array(
      'timeMin'=>date('c', strtotime("8 am")),
      'timeMax'=>date('c', strtotime("5 pm")),
      'maxResults'=>5,
      'timeZone'=>'Asia/Tehran',
      'showDeleted'=>'true',
    )
 */
$optParams = array(
    'maxResults'=>5,
);
$instances = $services->calendar->getInstances($calendarId,$eventId,$optParams);

// get new model and change any fields values
$eventModel = $services->calendar->getNewEventModel();
$eventModel->summary= "Changed one instance, add attachment";
$eventModel->addAttachment('https://drive.google.com/file/d/0B8V9PkAm4QfTbnBMODQtc1pvanc/view?usp=drivesdk');
//
$calendarId = 'primary';
$services->calendar->update($calendarId,$instances[1],$eventModel); // run update request and update specific instance of recurring event

$services->calendar->delete($calendarId,$instances[1]); // run delete request and delete specific instance of recurring event