<?php
/**
 * A function library - Used for check file permission
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

namespace RazeSoldier\MWExtUpgrader;

# Windows system permission system and linux have a bit different
if ( PHP_OS === 'WINNT' || PHP_OS === 'Windows' || PHP_OS === 'WIN32') {
	/**
	 * Checks if file can be create
	 * @param string $dir
	 * @return boolean
	 */
	function createFileAble($dir) {
		if ( is_writable( $dir ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks if file can be delete
	 * @param string $file
	 * @return boolean
	 */
	function deleteFileAble($file) {
		$dir = dirname( $file );
		if ( is_writable( $dir ) && is_writable( $file ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks if file can be read
	 * @param string $file
	 * @return boolean
	 */
	function readFileAble($file) {
		if ( is_readable( $file ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks if file can be write
	 * @param string $file
	 * @return boolean
	 */
	function writeFileAble($file) {
		if ( is_writable( $file ) ) {
			return true;
		} else {
			return false;
		}
	}
} else {
	/**
	 * Checks if file can be create
	 * @param string $dir
	 * @return boolean
	 */
	function createFileAble($dir) {
		if ( is_writable( $dir ) && is_executable( $dir ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks if file can be delete
	 * @param string $file
	 * @return boolean
	 */
	function deleteFileAble($file) {
		$dir = dirname( $file );
		if ( is_writable( $dir ) && is_executable( $dir ) && is_writable( $file ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks if file can be read
	 * @param string $file
	 * @return boolean
	 */
	function readFileAble($file) {
		$dir = dirname( $file );
		if ( is_executable( $dir ) && is_readable( $file ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks if file can be write
	 * @param string $file
	 * @return boolean
	 */
	function writeFileAble($file) {
		$dir = dirname( $file );
		if ( is_executable( $dir ) && is_writable( $file ) ) {
			return true;
		} else {
			return false;
		}
	}
}