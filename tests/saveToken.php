<?
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload

$services = new GoogleServices();

// in the first time only authentication
$services->authenticate();
// get auth token and save returned array values to database
// response values:
//      access_token
//      token_type
//      expires_in
//      created
//      refresh_token
$services->getAuthToken();

// in the next time get token fields of user from database and set it into setAuthToken() function, then authenticate
$services->setAuthToken($token->access_token,$token->token_type,$token->expires_in,$token->created,$token->refresh_token);