<?php

/**
 * @copyright 2017 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2017 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Mail\Service\Avatar;

use OCP\ICache;
use OCP\ICacheFactory;

class Cache {

	// Cache for one week
	const CACHE_TTL = 7 * 24 * 60 * 60;

	/** @var ICache */
	private $cache;

	/** @var AvatarFactory */
	private $avatarFactory;

	/**
	 * @param ICacheFactory $cacheFactory
	 */
	public function __construct(ICacheFactory $cacheFactory, AvatarFactory $avatarFactory) {
		$this->cache = $cacheFactory->createDistributed('mail.avatars');
		$this->avatarFactory = $avatarFactory;
	}

	/**
	 * @param string $email
	 * @param string $uid
	 * @return string
	 */
	private function buildUrlKey($email, $uid) {
		return base64_encode(json_encode([$email, $uid]));
	}

	/**
	 * @param string $url
	 * @param string $uid
	 * @return string
	 */
	private function buildImageKey($url, $uid) {
		return base64_encode(json_encode([$url, $uid]));
	}

	/**
	 * @param string $email
	 * @return Avatar|null avatar URL
	 */
	public function get($email, $uid) {
		$cached = $this->cache->get($this->buildUrlKey($email, $uid));

		if (is_null($cached)) {
			return null;
		}

		if ($cached['isExternal']) {
			return $this->avatarFactory->createExternal($cached['url'], $cached['mime']);
		} else {
			return $this->avatarFactory->createInternal($cached['url'], $cached['mime']);
		}
	}

	/**
	 * @param string $email
	 * @param string $uid
	 * @param Avatar $avatar
	 */
	public function add($email, $uid, Avatar $avatar) {
		$this->cache->set($this->buildUrlKey($email, $uid), $avatar->jsonSerialize(), self::CACHE_TTL);
	}

	/**
	 * @param string $url
	 * @param string $uid
	 * @return string|null cached image data
	 */
	public function getImage($url, $uid) {
		return $this->cache->get($this->buildImageKey($url, $uid));
	}

	/**
	 * @param string $url
	 * @param string $uid
	 * @param string $image base64 encoded image data
	 */
	public function addImage($url, $uid, $image) {
		$this->cache->set($this->buildImageKey($url, $uid), $image, self::CACHE_TTL);
	}

}
