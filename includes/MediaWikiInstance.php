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

/**
 * Modeling for a MediaWiki site
 * @package RazeSoldier\MWExtUpgrader
 */
class MediaWikiInstance {
	public const EXT_SUFFIX = '/extensions';
	public const SKIN_SUFFIX = '/skins';

	/**
	 * @var string MediaWiki installation directory
	 */
	private $baseDir;

	/**
	 * @var string DefaultSettings.php file path, removed in MediaWiki 1.46
	 * @see https://www.mediawiki.org/wiki/Manual:DefaultSettings.php
	 */
	private $defaultSettingsFilePath;

	/**
	 * @var string Defines.php file path
	 * @see https://www.mediawiki.org/wiki/Manual:Defines.php
	 */
	private $definesFilePath;

	/**
	 * @var MWVersion Version for the site
	 */
	private $version;

	/**
	 * @var MWBranch Branch name for the version
	 */
	private $branch;

	public function __construct(string $path) {
		$this->baseDir = $path;
		$this->defaultSettingsFilePath = "$this->baseDir/includes/DefaultSettings.php";
		$this->definesFilePath = "$this->baseDir/includes/Defines.php";
	}

	/**
	 * @return MWVersion
	 * @throws \RuntimeException
	 */
	public function getVersion() : MWVersion {
		if ($this->version !== null) {
			return $this->version;
		}
		// Since Mediawiki 1.46, includes/DefaultSettings.php has been removed
		if (file_exists($this->defaultSettingsFilePath)) {
			$text = file_get_contents($this->defaultSettingsFilePath);
			preg_match('/\$wgVersion\s=\s\'(?<version>.*?)\';/',
				$text, $matches);
			if (isset($matches['version'])) {
				$this->version = new MWVersion($matches['version']);
				return $this->version;
			}
		}
		// Try to search MW_VERSION constant in includes/Defines.php because $wgVersion deprecated in Mediawiki 1.35
		$text = file_get_contents($this->definesFilePath);
		preg_match('/define\(\s\'MW_VERSION\',\s\'(?<version>.*?)\'\s\);/',
			$text, $matches);
		if (!isset($matches['version'])) {
			throw new \RuntimeException('Failed to get MW version from DefaultSettings.php and Defines.php');
		}
		$this->version = new MWVersion($matches['version']);
		return $this->version;
	}

	public function getBranch() : string {
		if ($this->branch !== null) {
			return $this->branch;
		}
		$this->branch = $this->version->toBranch();
		return $this->branch;
	}

	public function getExtDir() : string {
		return $this->baseDir . self::EXT_SUFFIX;
	}

	public function getSkinDir() : string {
		return $this->baseDir . self::SKIN_SUFFIX;
	}

	/**
	 * Checks $path is valid MediaWiki directory
	 * @param string|null $path
	 * @return void
	 * @throws \UnexpectedValueException If the path is invalid, throw it
	 */
	public static function checkPath(?string $path = null) {
		if (!is_readable($path)) {
			throw new \UnexpectedValueException("Failed to read $path, or the directory does not exist.");
		}
		if (!is_dir($path)) {
			throw new \UnexpectedValueException("$path is not a directory.");
		}
		$path = realpath($path);
		if (!file_exists("$path/includes/Setup.php") || !file_exists("$path/includes/MediaWikiServices.php")) {
			throw new \UnexpectedValueException("$path is not a MediaWiki directory.");
		}
	}
}
