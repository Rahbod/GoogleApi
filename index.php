<?
ini_set('date.timezone', 'Asia/Tehran');
require_once __DIR__ . '/vendor/autoload.php'; // Autoload files using Composer autoload
$services = new GoogleServices();
$token=json_decode(file_get_contents('auth_token.json'));
$services->setAuthToken($token->access_token,$token->token_type,$token->expires_in,$token->created,$token->refresh_token);
var_dump($services->calendar->update('primary','540aa34l4aceeris9gf6hk7go8',[
        'attachments' => [
            [
                'fileId' => '0B8V9PkAm4QfTbnBMODQtc1pvanc',
                'title' => 'BBC 6 Minute English-2015.rar',
                'fileUrl' => 'https://drive.google.com/file/d/0B8V9PkAm4QfTbnBMODQtc1pvanc/view?usp=drivesdk',
                'mimeType' => 'application/rar',
                'iconLink'=>'https://drive-thirdparty.googleusercontent.com/16/type/application/rar'
            ]
        ]
    ]
));exit();
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