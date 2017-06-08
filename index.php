<?
ini_set('date.timezone', 'Asia/Tehran');
require_once __DIR__ . '/vendor/autoload.php'; // Autoload files using Composer autoload
$services = new GoogleServices();
$token=json_decode(file_get_contents('auth_token.json'));
$services->setAuthToken($token->access_token,$token->token_type,$token->expires_in,$token->created,$token->refresh_token);

$eventModel = $services->calendar->getNewEventModel();
$eventModel->summary= "Test";
$eventModel->location= "Test";
$eventModel->addRecurrence('RRULE:FREQ=WEEKLY;BYDAY=Tu;UNTIL=20171206');
$eventModel->setStart('6 am','Asia/Tehran');
$eventModel->setEnd('6 pm','Asia/Tehran');
$calendarId = 'primary';
$eventId = 'hc9u49vfm5udgt2tq9ku4fhh4c';
$services->calendar->insert($calendarId,$eventModel);
//
//$optParams = array(
//    'id'=>'540aa34l4aceeris9gf6hk7go8',
//    'status' => 'confirmed',
//    'attachments' => array(
//        array(
//            'fileUrl' => 'https://drive.google.com/file/d/0B8V9PkAm4QfTNWw1VzgzLVNEQTA/view?usp=drivesdk' // alternateLink of google drive files
//        )
//    )
//);
//$results = $calendar->update($calendarId, $optParams);
//var_dump($results);exit;