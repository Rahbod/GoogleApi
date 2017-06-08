# GoogleApi
setup config.json file

## Installation & loading
```sh
composer require rahbod/rahbod-google-api:dev-master
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
