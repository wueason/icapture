<?php

require dirname(__FILE__) . '/../vendor/autoload.php';

$client = new \Icapture\CaptureClient(['url'=>'http://www.bing.com']);
$client->request();
echo $client->getCaptureFile() . "\n";

?>
