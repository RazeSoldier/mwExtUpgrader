<?php
/**
 * Extract tarball action
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

class ExtractTarball {
	/**
	 * @var string
	 */
	private $tarPath;

	/**
	 * @var string
	 */
	private $targetPath;

	/**
	 * @var array messages
	 */
	private $msg = [
		'error' => [
			'without-phar' => 'Missing phar extension.'
		]
	];

	public function __construct($tarPath) {
		$this->tarPath = $tarPath;
		$this->targetPath = "{$this->tarPath}~";
		if ( !extension_loaded( 'phar' ) ) {
			trigger_error( $this->msg['error']['without-phar'], E_USER_ERROR );
		}
	}

	/**
	 * Do extract
	 * @return string
	 */
	public function doExtract() {
		try {
			$pharData = new \PharData( $this->tarPath );
			$pharData->extractTo( $this->targetPath );
			return $this->targetPath;
		} catch ( \UnexpectedValueException $e ) {
			echo $e;
		}
	}

	public function __destruct() {
		if ( file_exists( $this->targetPath ) ) {
			MWExtUpgrader::delDir( $this->targetPath );
		}
	}
}