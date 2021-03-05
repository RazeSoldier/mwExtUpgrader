<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

namespace RazeSoldier\MWExtUpgrader;

use RazeSoldier\MWExtUpgrader\Extractor\Extractor;
use RazeSoldier\MWExtUpgrader\Extractor\ExtractorFactory;

/**
 * Services
 * @package RazeSoldier\MWExtUpgrader
 */
class Services
{
	private static $instance;

	private $services = [];

	private function __construct(){}

	public static function getInstance() : self {
		if (self::$instance == null) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function getHttpClient() : HttpClient {
		if (!isset($this->services['http_client'])) {
			$this->services['http_client'] = HttpClient::getInstance();
		}
		return $this->services['http_client'];
	}

	public function getExtensionRepo() : ExtensionRepo {
		if (!isset($this->services['extension_repo'])) {
			$this->services['extension_repo'] = new ExtensionRepo;
		}
		return $this->services['extension_repo'];
	}

	public function getExtractor(): Extractor {
		if (!isset($this->services['extractor'])) {
			$this->services['extractor'] = ExtractorFactory::make();
		}
		return $this->services['extractor'];
	}
}
