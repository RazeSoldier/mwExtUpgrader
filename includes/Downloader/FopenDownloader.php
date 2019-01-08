<?php
/**
 * Download by fopen wrappers
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

namespace RazeSoldier\MWExtUpgrader\Downloader;

class FopenDownloader implements IDownloader {
	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var string
	 */
	private $mode;

	/**
	 * @var resource Stream of remote resource
	 */
	private $remoteStream;

	/**
	 * @var resource Stream of local resource
	 */
	private $localStream;

	/**
	 * @var string
	 */
	private $tempDir;

	/**
	 * @var string Tmp file name
	 */
	private $tmpName;

	public function __construct($url, $mode, $tempDir) {
		$this->url = $url;
		$this->mode = $mode;
		$this->tempDir = $tempDir;
		if ( $this->mode === 'file' ) {
			$this->tmpName = tempnam( $this->tempDir, 'mwext' );
			$this->localStream = fopen( $this->tmpName, 'wb' );
		}
		$this->remoteStream = fopen( $url, 'rb' );
	}

	public function doDownload() {
		if ( !$this->remoteStream ) {
			trigger_error( "Download failed: Can't get something from {$this->url}", E_USER_WARNING );
			return;
		}
		if ( $this->mode === 'text' ) {
			$returnValue = stream_get_contents( $this->remoteStream );
		} elseif ( $this->mode === 'file' ) {
			$result = fwrite( $this->localStream, stream_get_contents( $this->remoteStream ) );
			$returnValue = [
				'result' => $result,
				'filename' => $this->tmpName
			];
		}
		return $returnValue;
	}

	public function __destruct() {
		if ( is_resource( $this->remoteStream ) ) {
			fclose( $this->remoteStream );
		}
		if ( is_resource( $this->localStream ) ) {
			fclose( $this->localStream );
			if ( file_exists( $this->tmpName ) ) {
				unlink( $this->tmpName );
			}
		}
	}
}