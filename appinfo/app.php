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

$userSession = \OC::$server->getUserSession();
$userSession->listen('\OC\User', 'postLogin', function (\OCP\IUser $user) {
    $config = \OC::$server->getConfig();
    $logger = \OC::$server->getLogger();
    $regenerateKeys = $config->getUserValue($user->getUID(), 'encryption_recovery', 'regenerate');
    if ($regenerateKeys === 'on') {
        \OC::$server->getConfig()->setUserValue($user->getUID(), 'encryption_recovery', 'regenerate', time());
        $logger->debug('Regenerating recovery keys for ' . $user->getUid(), ['app' => 'encryption_recovery']);
        $userSession = \OC::$server->getUserSession();
        $crypt = new \OCA\Encryption\Crypto\Crypt($logger, $userSession, $config);
        $view = new \OC\Files\View();
        $recovery = new \OCA\Encryption\Recovery (
            $userSession,
            $crypt,
            \OC::$server->getSecureRandom(),
            new \OCA\Encryption\KeyManager(
                \OC::$server->getEncryptionKeyStorage(),
                $crypt,
                $config,
                $userSession,
                new \OCA\Encryption\Session(\OC::$server->getSession()),
                $logger,
                new \OCA\Encryption\Util (
                    $view,
                    $crypt,
                    $logger,
                    $userSession,
                    $config,
                    \OC::$server->getUserManager()
                )
            ),
            $config,
            \OC::$server->getEncryptionKeyStorage(),
            \OC::$server->getEncryptionFilesHelper(),
            $view
        );
        // remove script execution time limit
        set_time_limit(0);
        $recovery->setRecoveryForUser('1'); // sets config and regenerates recovery keys
        $logger->info('Regenerated recovery keys for ' . $user->getUid(), ['app' => 'encryption_recovery']);
    } else {
        $logger->debug('Not regenerating recovery keys for ' . $user->getUid(), ['app' => 'encryption_recovery']);
    }
});
