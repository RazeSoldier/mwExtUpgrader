<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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

abstract class PendingUpgradeTargetBase implements PendingUpgradeTarget
{
	protected $path;
	protected $name;
	protected $downloadLink;

	public function __construct(string $path) {
		$this->path = $path;
		$this->name = basename($path);
	}

	public function getBasePath()
	{
		return $this->path;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setDownloadLink(string $link)
	{
		$this->downloadLink = $link;
	}

	public function getDownloadLink(): string
	{
		return $this->downloadLink;
	}
}
