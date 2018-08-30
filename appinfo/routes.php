<?php

use OCA\Encryption_Recovery\Application;

$application = new Application();
$application->registerRoutes($this, array(
	'routes' => array(
		array('name' => 'regen#regen', 'url' => '/regenerate', 'verb' => 'GET'),
	)
));