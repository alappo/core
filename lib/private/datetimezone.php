<?php
/**
 * ownCloud
 *
 * @author Joas Schilling
 * @copyright 2014 Joas Schilling nickvergessen@owncloud.com
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OC;


use OCP\IConfig;
use OCP\IDateTimeZone;
use OCP\ISession;

class DateTimeZone implements IDateTimeZone {
	/** @var IConfig */
	protected $config;

	/** @var ISession */
	protected $session;

	/**
	 * Constructor
	 *
	 * @param IConfig $config
	 * @param ISession $session
	 */
	public function __construct(IConfig $config, ISession $session) {
		$this->config = $config;
		$this->session = $session;
	}

	/**
	 * Get the timezone of the current user, based on his session information and config data
	 *
	 * @return \DateTimeZone
	 */
	public function getTimeZone() {
		$timeZone = $this->config->getUserValue($this->session->get('user_id'), 'core', 'timezone', null);
		if ($timeZone === null) {
			if ($this->session->exists('timezone')) {
				return $this->guessTimeZoneFromOffset($this->session->get('timezone'));
			}
			$timeZone = 'UTC';
		}

		try {
			return new \DateTimeZone($timeZone);
		} catch (\Exception $e) {
			\OCP\Util::writeLog('datetimezone', 'Failed to created DateTimeZone "' . $timeZone . "'", \OCP\Util::DEBUG);
			return new \DateTimeZone('UTC');
		}
	}

	/**
	 * Guess the DateTimeZone for a given offset
	 *
	 * We first try to find a Etc/GMT* timezone, if that does not exist,
	 * we try to find it manually, before falling back to UTC.
	 *
	 * @param mixed $offset
	 * @return \DateTimeZone
	 */
	protected function guessTimeZoneFromOffset($offset) {
		try {
			// Note: the timeZone name is the inverse to the offset,
			// so a positive offset means negative timeZone
			// and the other way around.
			if ($offset > 0) {
				$timeZone = 'Etc/GMT-' . $offset;
			} else {
				$timeZone = 'Etc/GMT+' . abs($offset);
			}

			return new \DateTimeZone($timeZone);
		} catch (\Exception $e) {
			// If the offset has no Etc/GMT* timezone,
			// we try to guess one timezone that has the same offset
			foreach (\DateTimeZone::listIdentifiers() as $timeZone) {
				$dtz = new \DateTimeZone($timeZone);
				$dtOffset = $dtz->getOffset(new \DateTime());
				if ($dtOffset == 3600 * $offset) {
					return $dtz;
				}
			}

			// No timezone found, fallback to UTC
			\OCP\Util::writeLog('datetimezone', 'Failed to find DateTimeZone for offset "' . $offset . "'", \OCP\Util::DEBUG);
			return new \DateTimeZone('UTC');
		}
	}
}
