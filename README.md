# GoogleApi
setup config.json file

1- install:
<code>composer require rahbod/rahbod-google-api:dev-master<code>
2- create an onbject:
<code>
require 'vendor/autoload.php';
$services = new GoogleServices();
$calendar = $services->calendar;
$drive = $services->drive;
<code>
