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

use Symfony\Component\Console\Output\OutputInterface;

class UpgradeTask {
	/**
	 * @var string Task name
	 */
	private $name;

	/**
	 * @var string Remote URL
	 */
	private $src;

	/**
	 * @var string Target path
	 */
	private $dst;

	public function __construct(string $name, string $src, string $dst) {
		$this->name = $name;
		$this->src = $src;
		$this->dst = $dst;
	}

	public function run(OutputInterface $output) {
		$filename = $this->makeTmp();
		$this->pullFromSrc($filename, $output);
		$this->extractTarball($filename, "{$filename}ex" );
		$this->deleteDst($this->dst);
		$res = $this->copyDir("{$filename}ex", dirname($this->dst));
		if ($res) {
			$this->deleteDst("{$filename}ex");
		} else {
			$output->writeln('<error>Failed to copy to dst from src</error>');
		}
	}

	/**
	 * Create a tempfile and register a hook that make sure delete it when script exit
	 */
	private function makeTmp() : string {
		$filename = tempnam(sys_get_temp_dir(), 'mwe');
		rename($filename, "$filename.tar.gz");
		$filename .= '.tar.gz';
		register_shutdown_function(function () use ($filename) {
			if (file_exists($filename)) {
				unlink($filename);
			}
		});
		return $filename;
	}

	/**
	 * Download the tarball of the remote file to local
	 * @param string $tmp
	 * @param OutputInterface $output
	 * @throws \RuntimeException
	 */
	private function pullFromSrc(string $tmp, OutputInterface $output) {
		$task = new DownloadTask($this->src, $tmp);
		retry:
		try {
			$task->download();
		} catch (\RuntimeException $e) {
			$output->writeln($e->getMessage());
			goto retry;
		}
	}

	/**
	 * Delete a directory
	 * @param string $dir
	 * @return bool
	 */
	private function deleteDst(string $dir) : bool {
		if ( !is_dir( $dir ) ) {
			return false;
		}
		if ( !is_readable( $dir ) || !is_writable( $dir ) ) {
			return false;
		}
		$files = array_diff( scandir( $dir ), array( '.', '..' ) );
		foreach ( $files as $file ) {
			( is_dir( "$dir/$file" ) ) ? $this->deleteDst("$dir/$file") : unlink( "$dir/$file" );
		}
		return rmdir( $dir );
	}

	private function extractTarball(string $tarballPath, string $targetPath) {
		$pharData = new \PharData($tarballPath);
		$pharData->extractTo($targetPath);
	}

	private function copyDir(string $src, string $dst) {
		$dir = opendir( $src );
		if ( !is_resource( $dir ) ) {
			return false;
		}
		if (!file_exists($dst)) {
			mkdir( $dst );
		}
		while( false !== ( $file = readdir( $dir ) ) ) {
			if ( ( $file != '.' ) && ( $file != '..' ) ) {
				if ( is_dir( $src . '/' . $file ) ) {
					$this->copyDir( $src . '/' . $file, $dst . '/' . $file );
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
		return true;
	}
}
