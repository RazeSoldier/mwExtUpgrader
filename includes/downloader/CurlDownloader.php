<?php
/**
 * Download by curl extension
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

namespace MWExtUpgrader\Downloader;

class CurlDownloader {
	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var string
	 */
	private $mode;

	/**
	 * @var resource
	 */
	private $curlResource;

	/**
	 * @var resource|null
	 */
	private $fileResource;

	/**
	 * @var string
	 */
	private $tempDir;

	/**
	 * @var string Tmp file name
	 */
	private $tmpName;

	/**
	 * @var string|bool Download result
	 */
	private $result;

	public function __construct($url, $mode, $tempDir) {
		$this->url = $url;
		$this->mode = $mode;
		$this->tempDir = $tempDir;
		$this->curlResource = curl_init( $this->url );
		if ( $this->mode === 'file' ) {
			$this->tmpName = tempnam( $this->tempDir, 'mwext' );
			$this->fileResource = fopen( $this->tmpName, 'wb' );
		}
	}

	/**
	 * Set some option for a cURL transfer
	 */
	private function setCurlOpt() {
		if ( $this->mode === 'text' ) {
			curl_setopt( $this->curlResource, CURLOPT_RETURNTRANSFER, true );
		} elseif ( $this->mode === 'file' ) {
			# Support big file download
			curl_setopt( $this->curlResource, CURLOPT_FILE, $this->fileResource );
		}
		curl_setopt( $this->curlResource, CURLOPT_AUTOREFERER, true );
		// Stop cURL from verifying the peer's certificate
		curl_setopt( $this->curlResource, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $this->curlResource, CURLOPT_FOLLOWLOCATION, true );
	}

	public function doDownload() {
		$this->setCurlOpt();
		$this->result = curl_exec( $this->curlResource );
		if ( !$this->result ) {
			trigger_error( "Download failed: Can't get something from {$this->url}", E_USER_WARNING );
		}
		if ( $this->mode === 'text' ) {
			$returnValue = $this->result;
		} elseif ( $this->mode === 'file' ) {
			$returnValue = [
				'result' => $this->result,
				'filename' => $this->tmpName
			];
		}
		return $returnValue;
	}

	public function __destruct() {
		if ( is_resource( $this->curlResource ) ) {
			curl_close( $this->curlResource );
		}
		if ( is_resource( $this->fileResource ) ) {
			fclose( $this->fileResource );
			if ( file_exists( $this->tmpName ) ) {
				unlink( $this->tmpName );
			}
		}
	}
}