<?php

namespace OCA\Encryption_Recovery;

use \OCP\AppFramework\App;

class Application extends App {

	public function __construct(array $urlParams=array()){
		parent::__construct('encryption_recovery', $urlParams);
	}

}