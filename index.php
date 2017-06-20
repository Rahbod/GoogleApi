<?
ini_set('date.timezone', 'Asia/Tehran');
require_once __DIR__ . '/vendor/autoload.php'; // Autoload files using Composer autoload
$services = new GoogleServices();
$token=json_decode(file_get_contents('auth_token.json'));
$services->setAuthToken($token->access_token,$token->token_type,$token->expires_in,$token->created,$token->refresh_token);
var_dump($services->calendar->getListEvents());exit;