<?php

namespace OCA\Encryption_Recovery\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IConfig;
use OCP\ILogger;
use OCP\IRequest;
use OCP\IUserSession;

class RegenController extends Controller {

	protected $userSession;
	protected $config;
	protected $logger;
	protected $request;

	public function __construct(
		$appName,
		IRequest $request,
		IUserSession $userSession,
		ILogger $logger,
		IConfig $config) {
		parent::__construct($appName, $request);
		$this->config = $config;
		$this->logger = $logger;
		$this->userSession = $userSession;
		$this->request = $request;
	}

	/**
	 * @NoAdminRequired
	 * @return JSONResponse
	 * @throws \OCP\PreConditionNotMetException
	 */
	public function regen() {
		$user = $this->userSession->getUser();
		$regenerateKeys = $this->config->getUserValue(
			$user->getUID(),
			'encryption_recovery',
			'regenerate');
		if ($regenerateKeys === 'on') {
			$this->logger->debug('Regenerating recovery keys for ' . $user->getUid(), ['app' => 'encryption_recovery']);
			$userSession = \OC::$server->getUserSession();
			$crypt = new \OCA\Encryption\Crypto\Crypt($this->logger, $this->userSession, $this->config);
			$view = new \OC\Files\View();
			$recovery = new \OCA\Encryption\Recovery (
				$userSession,
				$crypt,
				\OC::$server->getSecureRandom(),
				new \OCA\Encryption\KeyManager(
					\OC::$server->getEncryptionKeyStorage(),
					$crypt,
					$this->config,
					$userSession,
					new \OCA\Encryption\Session(\OC::$server->getSession()),
					$this->logger,
					new \OCA\Encryption\Util (
						$view,
						$crypt,
						$this->logger,
						$userSession,
						$this->config,
						\OC::$server->getUserManager()
					)
				),
				$this->config,
				\OC::$server->getEncryptionKeyStorage(),
				\OC::$server->getEncryptionFilesHelper(),
				$view
			);
			// remove script execution time limit
			set_time_limit(0);
			try {
				$recovery->setRecoveryForUser('1'); // sets config and regenerates recovery keys
			} catch (\Exception $e) {
				$this->logger->logException($e, ['app' => 'encryption_recovery']);
				return new JSONResponse([], Http::STATUS_INTERNAL_SERVER_ERROR);
			}
			$this->config->setUserValue(
				$user->getUID(),
				'encryption_recovery',
				'regenerate',
				time());
			$this->logger->info('Regenerated recovery keys for ' . $user->getUid(), ['app' => 'encryption_recovery']);
			return new JSONResponse();
		} else {
			$this->logger->debug('Not regenerating recovery keys for ' . $user->getUid(), ['app' => 'encryption_recovery']);
			return new JSONResponse([], Http::STATUS_BAD_REQUEST);
		}
	}

}