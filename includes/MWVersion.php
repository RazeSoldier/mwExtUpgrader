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

class MWVersion {
	/**
	 * @var string
	 */
	private $rawVersion;

	private $formatVersion = [
		'major' => null,
		'minor' => null,
		'small' => null,
		'suffix' => null,
	];

	/**
	 * MWVersion constructor.
	 * @param string $version MediaWiki version
	 * @throws \UnexpectedValueException Throw it if $version is invalided
	 */
	public function __construct(string $version) {
		$hit = preg_match('/(?<major>[0-9]*)\.(?<minor>[0-9]*)(\.(?<small>[0-9]*))?(-(?<suffix>[a-z]*))?/',
			$version, $matches);
		if ($hit !== 1) {
			throw new \UnexpectedValueException("Invalid MW version: $version");
		}
		$this->rawVersion = $version;
		$this->formatVersion = [
			'major' => $matches['major'] ?? null,
			'minor' => $matches['minor'] ?? null,
			'small' => $matches['small'] ?? null,
			'suffix' => $matches['suffix'] ?? null,
		];
	}

	/**
	 * @return string
	 */
	public function getRawVersion() : string {
		return $this->rawVersion;
	}

	/**
	 * @return array
	 */
	public function getFormatVersion() : array {
		return $this->formatVersion;
	}

	public function getMainPart() : string {
		return "{$this->formatVersion['major']}.{$this->formatVersion['minor']}";
	}

	/**
	 * @return MWBranch
	 * @throws \UnexpectedValueException
	 */
	public function toBranch() : MWBranch {
		return MWBranch::parseVersion($this);
	}

	public function __toString() : string {
		return $this->rawVersion;
	}

	/**
	 * Build MWVersion from a branch name
	 * @param string $branch
	 * @return MWVersion
	 * @throws \UnexpectedValueException
	 */
	public static function parseBranch(string $branch) : self {
		$hit = preg_match('/REL(?<major>[0-9])_(?<minor>[0-9]*)/', $branch, $matches);
		if ($hit !== 1 || !isset($matches['major']) || !isset($matches['minor'])) {
			throw new \UnexpectedValueException("Invalid MW branch: $branch");
		}
		return new self("{$matches['major']}.{$matches['minor']}");
	}
}
