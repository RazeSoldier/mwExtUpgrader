<?php
/**
 * Download action
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

use RazeSoldier\MWExtUpgrader\Downloader\DownloaderFactory;

class Download {
	/**
	 * @var string Remote link
	 */
	private $url;

	/**
	 * @var string Download mode Acceptable values:
	 *  - 'text' doDownload() will return string
	 *  - 'file' (default value)
	 */
	private $mode;

	/**
	 * @var string
	 */
	private $tempDir;

	/**
	 * @var \RazeSoldier\MWExtUpgrader\Downloader\IDownloader
	 */
	private $downloader;

	private function getDownloader() {
		return DownloaderFactory::make($this->url, $this->mode, $this->tempDir);
	}

	public function __construct($url, $mode = 'file') {
		$this->url = $url;
		$this->mode = $mode;
		$this->tempDir = $GLOBALS['tempdir'];
		$this->downloader = $this->getDownloader();
	}

	public function doDownload() {
		return $this->downloader->doDownload();
	}
}