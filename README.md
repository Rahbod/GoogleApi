# GoogleApi

## Installation & loading
```sh
composer require rahbod/rahbod-google-api:dev-master
```
setup config.json file:
```sh
{
  "client_id": "", // get from google console
  "client_secret": "", // get from google console
  "auth_redirect_uri": "", // redirect url
  "auth_scopes": [
    "https://www.googleapis.com/auth/calendar",
    "https://www.googleapis.com/auth/drive",
    .
    .
  ],
  "debug_mode": true
}
```
## A Simple Example

```php
<?php
require 'vendor/autoload.php';

$services = new GoogleServices();
$calendar = $services->calendar;
$drive = $services->drive;
?>
```
