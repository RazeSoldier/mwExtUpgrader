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

use RazeSoldier\MWExtUpgrader\Command\DefaultCommand;
use Symfony\Component\Console\Application;

/**
 * Program Kernel
 * @package RazeSoldier\MWExtUpgrader
 */
final class Kernel {
	private $app;

	public function __construct() {
		$this->app = new Application();
		$this->registerCommand();
	}

	private function registerCommand() {
		$defaultCmd = new DefaultCommand;
		$this->app->setCatchExceptions(true);
		$this->app->add($defaultCmd);
		$this->app->setDefaultCommand($defaultCmd->getName());
	}

	public function run() {
		$status = $this->app->run();
		exit($status);
	}
}
