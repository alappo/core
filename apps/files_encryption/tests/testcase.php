<?php
/**
 * @author Bjoern Schiessle <schiessle@owncloud.com>
 * @author Joas Schilling <nickvergessen@gmx.de>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
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
namespace OCA\Files_Encryption\Tests;

/**
 * Class TestCase
 */
abstract class TestCase extends \Test\TestCase {

	/**
	 * @param string $user
	 * @param bool $create
	 * @param bool $password
	 */
	public static function loginHelper($user, $create = false, $password = false, $loadEncryption = true) {
		if ($create) {
			try {
				\OC_User::createUser($user, $user);
			} catch (\Exception $e) {
				// catch username is already being used from previous aborted runs
			}
		}

		if ($password === false) {
			$password = $user;
		}

		\OC_Util::tearDownFS();
		\OC_User::setUserId('');
		\OC\Files\Filesystem::tearDown();
		\OC::$server->getUserSession()->setUser(new \OC\User\User($user, new \OC_User_Database()));
		\OC_Util::setupFS($user);

		if ($loadEncryption) {
			$params['uid'] = $user;
			$params['password'] = $password;
			\OCA\Files_Encryption\Hooks::login($params);
		}
	}

	public static function logoutHelper() {
		\OC_Util::tearDownFS();
		\OC_User::setUserId(false);
		\OC\Files\Filesystem::tearDown();
	}

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		// reset backend
		\OC_User::clearBackends();
		\OC_User::useBackend('database');

		\OCA\Files_Encryption\Helper::registerFilesystemHooks();
		\OCA\Files_Encryption\Helper::registerUserHooks();
		\OCA\Files_Encryption\Helper::registerShareHooks();

		\OC::registerShareHooks();
		\OCP\Util::connectHook('OC_Filesystem', 'setup', '\OC\Files\Storage\Shared', 'setup');

		// clear and register hooks
		\OC_FileProxy::clearProxies();
		\OC_FileProxy::register(new \OCA\Files_Encryption\Proxy());
	}

	public static function tearDownAfterClass() {
		\OC_Hook::clear();
		\OC_FileProxy::clearProxies();

		// Delete keys in /data/
		$view = new \OC\Files\View('/');
		$view->deleteAll('files_encryption');

		parent::tearDownAfterClass();
	}

	protected function tearDown() {
		parent::tearDown();
		$this->resetKeyCache();
	}

	protected function resetKeyCache() {
		// reset key cache for every testrun
		$keyCache = new \ReflectionProperty('\OCA\Files_Encryption\Keymanager', 'key_cache');
		$keyCache->setAccessible(true);
		$keyCache->setValue(array());
		$keyCache->setAccessible(false);
	}

}
