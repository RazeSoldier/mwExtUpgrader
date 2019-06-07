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

class MWBranch {
	/**
	 * @var string Like: REL1_29, REL1_32
	 */
	private $branchText;

	public function __construct(string $major, string $minor) {
		$this->branchText = "REL{$major}_{$minor}";
	}

	/**
	 * @return string
	 */
	public function getBranchText() : string
	{
		return $this->branchText;
	}

	/**
	 * @return MWVersion
	 * @throws \UnexpectedValueException
	 */
	public function toVersion() : MWVersion {
		return MWVersion::parseBranch($this);
	}

	public function __toString() : string {
		return $this->branchText;
	}

	/**
	 * Build MWBranch from a version
	 * @param string $version
	 * @return MWBranch
	 * @throws \UnexpectedValueException
	 */
	public static function parseVersion(string $version) : self {
		$v = new MWVersion($version);
		$formatV = $v->getFormatVersion();
		if (!isset($formatV['major']) || !isset($formatV['minor'])) {
			throw new \UnexpectedValueException("Invalid MW version: $version");
		}
		return new self($formatV['major'], $formatV['minor']);
	}

	/**
	 * Build MWBranch from a branch name
	 * @param string $name
	 * @return MWBranch
	 * @throws \UnexpectedValueException
	 */
	public static function parseBranchName(string $name) : self {
		$hit = preg_match('/REL(?<major>[0-9])_(?<minor>[0-9]*)/', $name, $matches);
		if ($hit !== 1 || !isset($matches['major']) || !isset($matches['minor'])) {
			throw new \UnexpectedValueException("Invalid MW branch: $name");
		}
		return new self($matches['major'], $matches['minor']);
	}
}
