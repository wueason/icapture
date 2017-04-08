<?php

require dirname(__FILE__) . '/../vendor/autoload.php';

$iCaptureService = new \Icapture\CaptureService();
$iCaptureService->run();
