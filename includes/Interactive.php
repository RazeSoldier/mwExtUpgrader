<?php
/**
 * Interactive feature
 *
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

namespace MWExtUpgrader;

class Interactive {
	/**
	 * @var array messages
	 */
	public static $msg = [
		'error' => [
			'no-cli' => "For security, this script can only be run in cli mode.\n"
		],
		'warning' => [
			'dir-not-exist' => 'The path you typed does not exist.',
			'no-dir' => 'The path you typed is not a directory.',
			'dir-not-write' => 'The path you typed is not writable.',
			'dir-not-read' => 'The path you typed is not readable.',
			'invalid-mwversion' => 'You typed an invalid version number.',
			'jsonfile-not-exist' => 'Missing extension.json file - skip.',
			'jsonfile-not-read' => 'extension.json file can\'t read - skip'
		],
		'notice' => [
			'welcome' => 'Welcome to use mwExtUpgrader. This script can help you '
				. "bulk upgrade MediaWiki extensions.\n",
			'type-extdir' => 'Please type the absolute path to the extension '
				. "directory, like: mediawiki/w/extensions.\n",
		],
		'type-yes' => ' Type \'yes\' to continue:',
		'type-dir' => ' Absolute path of the extension directory:',
		'confirm-mwversion' => ' Please type \'yes\' to confirm this version to upgrade,'
			. ' or type the correct version number:',
		'type-mwversion' => ' Please type the version number you want to upgrade:',
		'bool-no-exit' => 'Quit.',
		'line' => "----------------------\n"
	];

	/**
	 * @var array $shellColor Store the shell color code
	 */
	private static $shellColor = [
		'red' => '31m',
		'green' => '32m',
		'yellow' => '33m'
	];

	/**
	 * Render the font color of the output
	 *
	 * downloadFile::shellOutput callback function
	 *
	 * @param string $input
	 * @param string $color
	 * @return string
	 */
	private static function setShellColor($input, $color) {
		$output = "\033[". self::$shellColor[$color] . $input . " \033[0m";
		return $output;
	}

	/**
	 * Output
	 */
	public static function shellOutput($input, $color = 'red') {
		if ( PHP_OS === 'Linux' || PHP_OS === 'Unix' ) {
			$output = self::setShellColor( $input, $color ) . "\n";
		} else {
			$output = $input . "\n";
		}
		echo $output;
	}

	/**
	 * Check if the target path is a directory and is read-write
	 * @return bool
	 */
	private function checkDir($dir) {
		$dir = realpath($dir);
		if ( !file_exists( $dir ) ) {
			self::shellOutput( self::$msg['warning']['dir-not-exist'] );
			return false;
		}
		if ( !is_dir( $dir ) ) {
			self::shellOutput( self::$msg['warning']['no-dir'] );
			return false;
		}
		if ( !is_writable( $dir ) ) {
			self::shellOutput( self::$msg['warning']['dir-not-write'] );
			return false;
		}
		if ( !is_readable( $dir ) ) {
			self::shellOutput( self::$msg['warning']['dir-not-read'] );
			return false;
		}
		return true;
	}

	/**
	 * Handle user input
	 * @param string $handleMode The way to handle user input
	 * @param string|null $parameter1 Additional data passed to the Handler
	 * @param string|null $parameter2 Additional data passed to the Handler
	 */
	public function userInput($handleMode, $parameter1 = null, $parameter2 = null) {
		$userInput = trim( fgets( fopen( 'php://stdin', 'r' ) ) );
		switch ( $handleMode ) {
			case 'bool':
				$this->boolHandler( $userInput );
				break;
			case 'text':
				return $this->textHandler( $userInput, $parameter1, $parameter2 );
			case 'null':
				return $userInput;
		}
	}

	private function boolHandler($input) {
		if ( $input === 'yes' ) {
			
		} else {
			self::shellOutput( self::$msg['bool-no-exit'], 'yellow' );
			die( 1 );
		}
	}

	private function textHandler($input, $checkType, $parameter = null) {
		switch ( $checkType ) {
			case 'checkdir':
				$doCheckDir = $this->checkDir( $input );
				while ( !$doCheckDir ) {
					echo Interactive::$msg['type-dir'];
					$input = $this->userInput( 'null' );
					$doCheckDir = $this->checkDir( $input );
				}
				return realpath( $input );
			case 'checkversion':
				if ( $input === 'yes' ) {
					return $parameter;
				}
				$pattern = '/^(1\.[0-9][0-9](\.[0-9])?)$/';
				$result = preg_match( $pattern, $input , $matches );
				$minVersion = 1.27;
				$maxVerison = 1.31;
				if ( $result === 0 || $input < $minVersion || $input > $maxVerison ) {
					self::shellOutput( self::$msg['warning']['invalid-mwversion'] );
					echo self::$msg['type-mwversion'];
					$this->userInput( 'text', 'checkversion', $parameter );
					return $input;
				}

				return $matches[0];
		}
	}
}