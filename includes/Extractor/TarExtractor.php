<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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

namespace RazeSoldier\MWExtUpgrader\Extractor;

use Symfony\Component\Process\Exception\ProcessSignaledException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Use 'tar' command to extract .tar.gz file
 * @package RazeSoldier\MWExtUpgrader\Extractor
 */
class TarExtractor implements Extractor
{
	/**
	 * @var string
	 */
	private $executablePath;

	public function __construct(string $executablePath)
	{
		$this->executablePath = $executablePath;
	}

	/**
	 * @param string $tarballFilePath
	 * @param string $dstPath
	 * @throws ExtractException
	 */
	public function extract(string $tarballFilePath, string $dstPath)
	{
		if (!is_dir($dstPath)) {
			mkdir($dstPath);
		}

		$process = new Process([$this->executablePath, '-xzf', $tarballFilePath, '-C', $dstPath]);
		try {
			$exitCode = $process->run();
		} catch (ProcessSignaledException | RuntimeException | ProcessTimedOutException $e) {
			throw new ExtractException($e->getMessage(), 0, $e);
		}
		if ($exitCode !== 0) {
			throw new ExtractException("Failed to extract $tarballFilePath to $dstPath (error message: {$process->getErrorOutput()})");
		}
	}
}
