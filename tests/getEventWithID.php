<?
ini_set('date.timezone', 'Asia/Tehran');
require_once __DIR__ . '../vendor/autoload.php'; // Autoload files using Composer autoload
$services = new GoogleServices();
//$services->setAuthToken($token->access_token,$token->token_type,$token->expires_in,$token->created,$token->refresh_token);
$calendarId = 'primary';
$eventId = '540aa34l4aceeris9gf6hk7go8';
$services->calendar->get($calendarId,$eventId);