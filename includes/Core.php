<?php
/**
 * Core file
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

class MWExtUpgrader {
	/**
	 * @var array
	 */
	private $runtimeInfo;

	/**
	 * @var object Interactive object
	 */
	private $client;

	/**
	 * @var object MediaWikiHunter object
	 */
	private $mwHunter;

	/**
	 * Load class file
	 */
	public static function classLoader() {
		require_once APP_PATH . '/includes/Interactive.php';
		require_once APP_PATH . '/includes/MediaWikiHunter.php';
		require_once APP_PATH . '/includes/Download.php';
		require_once APP_PATH . '/includes/Downloader/IDownloader.php';
		require_once APP_PATH . '/includes/Downloader/DownloaderFactory.php';
		require_once APP_PATH . '/includes/Downloader/CurlDownloader.php';
		require_once APP_PATH . '/includes/Downloader/FopenDownloader.php';
		require_once APP_PATH . '/includes/ExtractTarball.php';
		require_once APP_PATH . '/includes/FilePermission.php';
	}

	public function __construct() {
		// For security, this script can only be run in cli mode
		if ( PHP_SAPI !== 'cli' ) {
			echo Interactive::$msg['error']['no-cli'];
			die( 1 );
		}
	}

	/**
	 * Get the number of directories in a directory
	 * @param string $dir
	 * @return int
	 */
	private function countDir($dir) {
		if ( is_dir( $dir ) ) {
			$dirResource = opendir( $dir );
			if ( $dirResource ) {
				$count = 0;
				while ( false !== ( $file = readdir( $dirResource ) ) ) {
					$filePath = $this->mwHunter->getExtensionPath( $file );
					if ( $file != "." && $file != ".." && is_dir( $filePath ) ) {
						$count = $count + 1;
					}
				}
				closedir( $dirResource );
				return $count;
			}
		}
	}

	/**
	 * Delete a directory
	 * @param string $dir
	 * @return bool
	 */
	public static function delDir($dir) {
		if ( !is_dir( $dir ) ) {
			return false;
		}
		if ( !is_readable( $dir ) || !is_writable( $dir ) ) {
			return false;
		}
		$files = array_diff( scandir( $dir ), array( '.', '..' ) ); 
		foreach ( $files as $file ) { 
			( is_dir( "$dir/$file" ) ) ? self::delDir("$dir/$file") : unlink( "$dir/$file" ); 
		}
		return rmdir( $dir ); 
	}

	/**
	 * Copy a directory to another place to go
	 * @param string $src Source directory path
	 * @param string $dst Target directory path
	 */
	public static function copyDir($src, $dst) {
		$dir = opendir( $src );
		if ( !is_resource( $dir ) ) {
			return false;
		}
		@mkdir( $dst );
		while( false !== ( $file = readdir( $dir ) ) ) {
			if ( ( $file != '.' ) && ( $file != '..' ) ) {
				if ( is_dir( $src . '/' . $file ) ) {
					self::copyDir( $src . '/' . $file, $dst . '/' . $file );
					continue;
				} else {
					$srcPath = $src . '/' . $file;
					$dstPath = $dst . '/' . $file;
					if ( is_readable( $srcPath ) && createFileAble( $dst ) ) {
						copy( $srcPath, $dstPath );
					} else {
						return false;
					}
				}
			}
		}
		closedir($dir);
	}

	/**
	 * Run script
	 */
	public function run() {
		// Prompt people that this script is not stable and can't be used for production
		// When the code is stable, the line must to be remove
		Interactive::shellOutput( 'Note that this release of code is not stable.'
			. ' Do not use for production.' , 'yellow');
		$this->prelude();
		$this->doUpgrade();
		$this->coda();
	}

	/**
	 * This is the stage of gathering information before starting the upgrade
	 */
	private function prelude() {
		echo Interactive::$msg['notice']['welcome'];
		$this->client = new Interactive();
		echo Interactive::$msg['type-yes'];
		$this->client->userInput( 'bool' );

		// Define the full path to the extension directory
		echo Interactive::$msg['notice']['type-extdir'];
		echo Interactive::$msg['type-dir'];
		$this->runtimeInfo['extdir'] = $this->client->userInput( 'text', 'checkdir' );

		// Checks if the temp directory can be read-write
		$GLOBALS['tempdir'] = sys_get_temp_dir();
		if ( !createFileAble( $GLOBALS['tempdir'] ) ) {
			Interactive::shellOutput( Interactive::$msg['warning']['systempdir-not-rw'] );
			echo Interactive::$msg['type-temp-dir'];
			$GLOBALS['tempdir'] = $this->client->userInput( 'checktempdir' );
		}

		// Get the MediaWiki version number
		$this->mwHunter = new MediaWikiHunter( $this->runtimeInfo['extdir'] );
		$this->runtimeInfo['mwVersion'] = $this->mwHunter->getMWVersion();

		// Push MediaWikiHunter::$mwVersionRange to global scope
		$GLOBALS['mwVersionRange'] = $this->mwHunter->mwVersionRange;

		// Verify the MediaWiki version number
		$this->client->setMWHunter( $this->mwHunter );
		if ( $this->runtimeInfo['mwVersion'] ) {
			echo "mwExtUpgrader detected an installed MediaWiki version is "
				. "{$this->runtimeInfo['mwVersion']}\n";
			echo Interactive::$msg['confirm-mwversion'];
		} else {
			echo 'mwExtUpgrader can not detect the version number of your installed'
				. " MediaWiki\n";
			echo Interactive::$msg['type-mwversion'];
		}
		$this->runtimeInfo['mwVersion'] = $this->client->userInput( 'text',
				'checkversion', $this->runtimeInfo['mwVersion'] );
	}

	/**
	 * Do upgrade
	 */
	private function doUpgrade() {
		$dirResource = opendir( $this->runtimeInfo['extdir'] );
		$countExt = $this->countDir( $this->runtimeInfo['extdir'] );
		if ( $countExt === 0 ) {
			Interactive::shellOutput( Interactive::$msg['without-ext'] , 'yellow' );
			Interactive::shellOutput( Interactive::$msg['bool-no-exit'] , 'yellow' );
			die ( 1 );
		}
		if ( $dirResource ) {
			echo "Will be upgraded {$countExt} extensions.\n";
			echo Interactive::$msg['line'];
			$i = 0;
			$countDone = 0;
			while ( false !== ( $fileName = readdir( $dirResource ) ) ) {
				$extPath = $this->mwHunter->getExtensionPath( $fileName );
				if ( $fileName != "." && $fileName != ".." &&
						is_dir( $extPath ) ) {
					$i = $i + 1;
					echo "({$i}/{$countExt})[{$fileName} extension]";
					$downloadURL =  $this->mwHunter->getExtDownloadURL( $fileName,
							$this->runtimeInfo['mwVersion'] );
					if ( $downloadURL === null ) {
						Interactive::shellOutput( ' Ignore!', 'yellow' );
						echo Interactive::$msg['line'];
						continue;
					}
					$downloader = new Download( $downloadURL, 'file' );
					$result = $downloader->doDownload();
					$tarName = $result['filename'] . '.tar.gz';
					copy( $result['filename'], $tarName );
					$extracter = new ExtractTarball( $tarName );
					$delResult = self::delDir( $extPath );
					if ( $delResult === false ) {
						Interactive::shellOutput( ' Failed to delete the directory of this extension,'
							. ' Ignore!', 'yellow' );
						echo Interactive::$msg['line'];
						continue;
					}
					$copyResult = self::copyDir( $extracter->doExtract(), $this->runtimeInfo['extdir'] );
					if ( $copyResult === false ) {
						Interactive::shellOutput( ' Copy failed, Ignore!', 'yellow' );
						echo Interactive::$msg['line'];
						continue;
					}

					// Reduce the amount of memory used
					unlink( $tarName );
					unset( $downloader );
					unset( $extracter );
					Interactive::shellOutput( ' Done.' , 'green');
					echo Interactive::$msg['line'];
					$countDone = $countDone + 1;
				}
			} // End while
			$this->runtimeInfo['countDone'] = $countDone;
		} else {
			trigger_error( 'Unkown Error', E_USER_ERROR );
		}
	}

	private function coda() {
		if ( $this->runtimeInfo['countDone'] === 1 ) {
			$extWord = 'extension';
		} else {
			$extWord = 'extensions';
		}
		Interactive::shellOutput( "Successfully upgraded {$this->runtimeInfo['countDone']} {$extWord}." , 'green');
		Interactive::shellOutput( "Some extensions may require running 'maintenance/update.php' script"
				. " to update the database schema." , 'yellow');
	}
}