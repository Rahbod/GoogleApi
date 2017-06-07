<?
ini_set('date.timezone', 'Asia/Tehran');
require_once __DIR__ . '../vendor/autoload.php'; // Autoload files using Composer autoload
$services = new GoogleServices();
$services->authenticate();
$calendarId = 'primary';
$eventId = '540aa34l4aceeris9gf6hk7go8';
$services->calendar->delete($calendarId, $eventId);