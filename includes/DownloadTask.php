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

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class DownloadTask {
	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var string
	 */
	private $savePath;

	/**
	 * @var HttpClient
	 */
	private $httpClient;

	public function __construct(string $url, string $savePath) {
		$this->url = $url;
		if (!is_writable($savePath)) {
			throw new \RuntimeException("Failed to write $savePath");
		}
		$this->savePath = $savePath;
		$this->httpClient = Services::getInstance()->getHttpClient();
	}

	public function download() {
		$resp = $this->makeNormalRequest();
		$expectedSize = $resp->getHeaders(false)['content-length'][0];
		$file = fopen($this->savePath, 'w+b');

		$this->doTransport($resp, $file);

		$actualSize = filesize($this->savePath);
		if ($actualSize != $expectedSize) {
			throw new \RuntimeException("<error>Expected size: $expectedSize, but got: $actualSize.</error>");
		}
		fclose($file);
	}

	private function makeNormalRequest() : ResponseInterface {
		return $this->httpClient->request('GET', $this->url, [
			'buffer' => false,
		]);
	}

	private function makeContinueRequest(int $offset) : ResponseInterface {
		return $this->httpClient->request('GET', $this->url, [
			'buffer' => false,
			'headers' => [
				'Ranges' => "$offset-",
			],
		]);
	}

	/**
	 * @param ResponseInterface $response
	 * @param resource $file
	 */
	private function doTransport(ResponseInterface $response, $file) {
		while (true) {
			$chunks = $this->httpClient->stream($response);
			try {
				foreach ($chunks as $chunk) {
					$offset = $chunk->getOffset();
					fwrite($file, $chunk->getContent());
				}
				break;
			} catch (TransportExceptionInterface $e) {
				$response = $this->makeContinueRequest($offset);
			}
		}
	}
}