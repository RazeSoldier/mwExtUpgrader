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

use Symfony\Component\HttpClient\HttpClient as SymfonyClient;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

/**
 * Package Symfony\Component\HttpClient\HttpClient
 * @package RazeSoldier\MWExtUpgrader
 */
class HttpClient
{
	/**
	 * @var SymfonyClient
	 */
	private $client;

	/**
	 * @var self
	 */
	private static $instance;

	private function __construct() {
		$this->client = SymfonyClient::create();
	}

	public static function getInstance() : self {
		if (self::$instance === null) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * @param string $method
	 * @param string $url
	 * @param array $option
	 * @return \Symfony\Contracts\HttpClient\ResponseInterface
	 * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
	 */
	public function request(string $method, string $url, array $option = []) {
		return $this->client->request($method, $url, $option);
	}

	public function stream(ResponseInterface $response, float $timeout = null) : ResponseStreamInterface {
		return $this->client->stream($response, $timeout);
	}
}
