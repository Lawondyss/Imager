<?php

if (!(@include __DIR__ . '/../vendor/autoload.php')) {
  echo 'Install Nette Tester using "composer update --dev"';
  exit(1);
}

date_default_timezone_set('Europe/Prague');

Tester\Environment::setup();

define('TEMP_DIR', __DIR__ . '/tmp/' . getmypid());

define('ASSETS', __DIR__ . '/assets/');
define('IMAGE', 'avatar.jpg');
define('NON_IMAGE', 'non-image.txt');


Tester\Helpers::purge(TEMP_DIR);
