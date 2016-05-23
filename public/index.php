<?php
require __DIR__ . '/../database/initialize.php';
require __DIR__ . '/../Jacwright/RestServer/RestServer.php';
require __DIR__ . '/class.InvoiceBuddyController.php';

$server = new \Jacwright\RestServer\RestServer('debug');
$server->addClass('InvoiceBuddyController');
$server->handle();
