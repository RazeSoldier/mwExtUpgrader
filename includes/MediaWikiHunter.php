<?php
/**
 * Execute MediaWiki-related actions
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

class MediaWikiHunter {
	/**
	 * @var string The MediaWiki installation directory
	 */
	private $mwIP;

	/**
	 * @var stirng MediaWiki wiki api
	 */
	private $mwWikiApi = 'https://www.mediawiki.org/w/api.php?action=query&list=extdistbranches&format=json';

	/**
	 * @var array The API of mediawiki.org permissible version range
	 */
	private $mwVersionRange;

	/**
	 * @param string $extdir MediaWiki extension directory
	 */
	public function __construct($extdir) {
		$this->mwIP = dirname($extdir);
		$this->mwVersionRange = $this->getMWVersionRange();
	}

	/**
	 * Convert the version number entered into a branch name of like REL1_30
	 * @param string $mwVersion
	 * @return string A branch name of like REL1_30
	 */
	private function convertMWVersion($mwVersion) {
		$mw1_27 = 1.27;
		$mw1_29 = 1.29;
		$mw1_30 = 1.30;
		$mw1_31 = 1.31;
		if ( $mwVersion >= $mw1_27 && $mwVersion < $mw1_29 ) {
			return 'REL1_27';
		}
		if ( $mwVersion >= $mw1_29 && $mwVersion < $mw1_30 ) {
			return 'REL1_29';
		}
		if ( $mwVersion >= $mw1_30 && $mwVersion < $mw1_31 ) {
			return 'REL1_30';
		}
		return 'master';
	}

	/**
	 * Convert a branch name of like REL1_30 into version number
	 * @param string $mwVersion
	 * @return string
	 */
	private function convertMWVersionToInt($mwVersion) {
		$replace = str_replace( 'REL', null, $mwVersion );
		return str_replace( '_', '.', $replace );
	}

	/**
	 * Get MediaWiki current version range from the API
	 * @return array
	 */
	private function getMWVersionRange() {
		$downloader = new Download( $this->mwWikiApi . '&edbexts=ExtensionDistributor', 'text' );
		$downloadResult = $downloader->doDownload();
		if ( !$downloadResult ) {
			trigger_error( 'The script can\'t get MediaWiki current version range from the API'
					, E_USER_ERROR );
		}
		$jsonArray = json_decode( $downloader->doDownload(), true );
		unset( $downloader );

		$arr = array();
		foreach ( $jsonArray['query']['extdistbranches']['extensions']['ExtensionDistributor'] as $key => $value) {
			$prefix = 'REL';
			if ( strpos( $key, $prefix ) !== false ) {
				$arr[] = $this->convertMWVersionToInt( $key );
			}
		}

		sort( $arr );
		$arrlength = count( $arr );
		return [
			'minVersion' => $arr[0],
			'maxVersion' => $arr[$arrlength-1],
			'betaVersion' => $arr[$arrlength-1] + 0.01
		];
	}

	/**
	 * Capture the MediaWiki version number from DefaultSettings.php
	 * @return string|false MediaWiki version number
	 */
	public function getMWVersion() {
		$filePath = $this->mwIP . '/includes/DefaultSettings.php';
		if ( !is_readable( $filePath ) ) {
			return false;
		}
		$fileText = file_get_contents( $filePath );
		// First match
		$pattern[1] = '/\$wgVersion = (.*);/';
		preg_match( $pattern[1], $fileText, $matches );
		// Second match
		$pattern[2] = '/1[\.0-9a-zA-Z-]*/';
		preg_match( $pattern[2], $matches[0], $result );

		return $result[0];
	}

	/**
	 * Get absolute path of the extension
	 * @param string $extName
	 * @return string Absolute path of the extension
	 */
	public function getExtensionPath($extName) {
		return realpath( $this->mwIP . '/extensions/' . $extName );
	}

	/**
	 * Get the download URL of the extension
	 * @param type $extName
	 * @param type $mwVersion
	 * @return string
	 */
	public function getExtDownloadURL($extName, $mwVersion) {
		$branchName = $this->convertMWVersion( $mwVersion );
		$downloader = new Download( $this->mwWikiApi . "&edbexts={$extName}", 'text' );
		$jsonArray = json_decode( $downloader->doDownload(), true );
		unset( $downloader );
		@$url = $jsonArray['query']['extdistbranches']['extensions'][$extName][$branchName];
		if ( isset( $url ) ) {
			return $url;
		}
	}
}