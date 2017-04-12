<?php
/**
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 *
 * @copyright Copyright (c) 2017, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Encryption_Recovery\Command;

use OC\User\Manager;
use OCP\IConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Recovery extends Command {

	/** @var \OC\User\Manager */
	private $userManager;

	/** @var IConfig */
	private $config;

	/**
	 * @param Manager $userManager
	 * @param IConfig $config
	 */
	public function __construct(Manager $userManager,
								IConfig $config) {

		$this->userManager = $userManager;
		$this->config = $config;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setName('encryption:mark-for-recovery-regen')
			->setDescription('Regenerate keys for a given user upon next login')
			->addArgument(
				'user_id',
				InputArgument::REQUIRED | InputArgument::IS_ARRAY,
				'will migrate keys of the given user(s)'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$users = $input->getArgument('user_id');
		foreach ($users as $user) {
			if ($this->userManager->userExists($user)) {
				$output->writeln("Marking <info>$user</info> for recovery key regeneration");
				$this->config->setUserValue($user, 'encryption_recovery', 'regenerate', 'on');
			} else {
				$output->writeln("<error>Unknown user $user</error>");
			}
		}
	}
}
