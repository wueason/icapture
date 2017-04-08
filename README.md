# icapture
PhantomJS as a service with Swoole module. Icapture can be a service
for your screenshop purpose.

## Requirements ##

* PHP 5.3+
* Redis 2.2+ and make sure the redis is up
* Swoole extension 1.8.13+
* Optional but Recommended: Composer

## Getting Started ##
If you're not familiar with Composer, please see <http://getcomposer.org/>.

1. Add icapture to your application's composer.json.

```json
{
    "require": {
        "wueason/icapture": "1.0.x"
    }
}
```

2. Run `composer install`.

3. If you haven't already, add the Composer autoload to your project's
   initialization file. (example)

```sh
require 'vendor/autoload.php';
```

4. Service management.

```
make start
make stop
make restart
```

5. Composer a request with `\Icapture\CaptureClient`, and get the capture.

```php
$client = new \Icapture\CaptureClient();

$client->request();

echo $clien->getCaptureFile();
```

## Tips ##

PhantomJS binary file in Screen may be broken, you can use your own
with `phantomjsBinPath` setting instead.
