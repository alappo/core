<?php
/**
 * @author Bjoern Schiessle <schiessle@owncloud.com>
 * @author Gadzy <dev@gadzy.fr>
 * @author j-ed <juergen@eisfair.org>
 * @author Joas Schilling <nickvergessen@gmx.de>
 * @author Michael Gapczynski <gapczynskim@gmail.com>
 * @author Michael Gapczynski <GapczynskiM@gmail.com>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Vincent Petry <pvince81@owncloud.com>
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
$l = \OC::$server->getL10N('files_sharing');

OC::$CLASSPATH['OC_Share_Backend_File'] = 'files_sharing/lib/share/file.php';
OC::$CLASSPATH['OC_Share_Backend_Folder'] = 'files_sharing/lib/share/folder.php';
OC::$CLASSPATH['OC\Files\Storage\Shared'] = 'files_sharing/lib/sharedstorage.php';
OC::$CLASSPATH['OC\Files\Cache\SharedScanner'] = 'files_sharing/lib/scanner.php';
OC::$CLASSPATH['OC\Files\Cache\Shared_Cache'] = 'files_sharing/lib/cache.php';
OC::$CLASSPATH['OC\Files\Cache\Shared_Permissions'] = 'files_sharing/lib/permissions.php';
OC::$CLASSPATH['OC\Files\Cache\Shared_Updater'] = 'files_sharing/lib/updater.php';
OC::$CLASSPATH['OC\Files\Cache\Shared_Watcher'] = 'files_sharing/lib/watcher.php';
OC::$CLASSPATH['OCA\Files\Share\Maintainer'] = 'files_sharing/lib/maintainer.php';
OC::$CLASSPATH['OCA\Files\Share\Proxy'] = 'files_sharing/lib/proxy.php';

// Exceptions
OC::$CLASSPATH['OCA\Files_Sharing\Exceptions\BrokenPath'] = 'files_sharing/lib/exceptions.php';

\OCP\App::registerAdmin('files_sharing', 'settings-admin');

\OCA\Files_Sharing\Helper::registerHooks();

OCP\Share::registerBackend('file', 'OC_Share_Backend_File');
OCP\Share::registerBackend('folder', 'OC_Share_Backend_Folder', 'file');

OCP\Util::addScript('files_sharing', 'share');
OCP\Util::addScript('files_sharing', 'external');

OC_FileProxy::register(new OCA\Files\Share\Proxy());

\OC::$server->getActivityManager()->registerExtension(function() {
		return new \OCA\Files_Sharing\Activity(
			\OC::$server->query('L10NFactory'),
			\OC::$server->getURLGenerator()
		);
});

$config = \OC::$server->getConfig();
if ($config->getAppValue('core', 'shareapi_enabled', 'yes') === 'yes') {

	\OCA\Files\App::getNavigationManager()->add(
		array(
			"id" => 'sharingin',
			"appname" => 'files_sharing',
			"script" => 'list.php',
			"order" => 10,
			"name" => $l->t('Shared with you')
		)
	);

	if (\OCP\Util::isSharingDisabledForUser() === false) {

		\OCA\Files\App::getNavigationManager()->add(
			array(
				"id" => 'sharingout',
				"appname" => 'files_sharing',
				"script" => 'list.php',
				"order" => 15,
				"name" => $l->t('Shared with others')
			)
		);
		// Check if sharing by link is enabled
		if ($config->getAppValue('core', 'shareapi_allow_links', 'yes') === 'yes') {
			\OCA\Files\App::getNavigationManager()->add(
				array(
					"id" => 'sharinglinks',
					"appname" => 'files_sharing',
					"script" => 'list.php',
					"order" => 20,
					"name" => $l->t('Shared by link')
				)
			);
		}
	}
}
