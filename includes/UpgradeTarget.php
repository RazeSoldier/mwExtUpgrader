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

class UpgradeTarget {
	public const TYPE_EXT = 1;
	public const TYPE_SKIN = 2;

	private $type;

	private $dst;

	private $src;

	private $name;

	public function __construct(int $type, string $dst) {
		$this->type = $type;
		$this->dst = $dst;
		$this->name = basename($this->dst);
	}

	public static function newExtTarget(string $dst) : self {
		return new self(self::TYPE_EXT, $dst);
	}

	public static function newSkinTarget(string $dst) : self {
		return new self(self::TYPE_SKIN, $dst);
	}

	public function getName() : string {
		return $this->name;
	}

	public function setSrc(string $url) {
		$this->src = $url;
	}

	public function getSrc() : string {
		return $this->src;
	}

	public function getDst() : string {
		return $this->dst;
	}

	public function getType() : int {
		return $this->type;
	}
}
