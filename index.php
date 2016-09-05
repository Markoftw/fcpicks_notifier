<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

include 'vendor/autoload.php';
use Markoftw\fcpicks\FCpicks;
use GuzzleHttp\Client;

$fc = new FCpicks(new Client());
$fc->getPageContent()->findNewPosts()->mailResults();
