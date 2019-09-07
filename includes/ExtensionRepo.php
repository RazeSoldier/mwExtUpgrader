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

/**
 * Class ExtensionRepo
 * @package RazeSoldier\MWExtUpgrader
 */
class ExtensionRepo {
	public const REMOTE_POINT = 'https://www.mediawiki.org/w/api.php?action=query&list=extdistbranches&format=json&formatversion=2';

	public function getDownloadLink(array $inputs) : array {
		if ($inputs === []) {
			throw new \InvalidArgumentException('$extNames is an empty array.');
		}

		$links = [];
		foreach (array_chunk($inputs, 49, true) as $items) {
			$links = array_merge_recursive($links, $this->pullDownloadLink($items));
		}

		return $links;
	}

	private function pullDownloadLink(array $inputs) : array {
		$exts = [];
		$skins = [];
		if (current($inputs) instanceof UpgradeTarget) {
			/** @var UpgradeTarget[] $inputs */
			foreach ($inputs as $input) {
				if ($input->getType() === UpgradeTarget::TYPE_EXT) {
					$exts[] = $input->getName();
				} else {
					$skins[] = $input->getName();
				}
			}
		} else {
			foreach ($inputs as $name => $type) {
				if ($type === UpgradeTarget::TYPE_EXT) {
					$exts[] = $name;
				} else {
					$skins[] = $name;
				}
			}
		}

		$url = self::REMOTE_POINT;
		if ($exts !== []) {
			$url .= '&edbexts=';
			foreach ($exts as $ext) {
				$url .= "$ext|";
			}
		}
		if ($skins !== []) {
			$url .= '&edbskins=';
			foreach ($skins as $skin) {
				$url .= "$skin|";
			}
		}


		$httpClient = Services::getInstance()->getHttpClient();
		$resp = $httpClient->request('GET', $url);
		$res = $resp->toArray()['query']['extdistbranches'];
		$return = [];
		if (isset($res['extensions'])) {
			$return = $return + $res['extensions'];
		}
		if (isset($res['skins'])) {
			$return = $return + $res['skins'];
		}
		return $return;
	}

	/**
	 * Get the version range supported by ExtensionDistributor on MediaWiki.org
	 * @return array A indexed array, the version numbers sorted in ascending order
	 */
	public function getSupportVersionRange() : array {
		$links = $this->getDownloadLink(['ExtensionDistributor' => UpgradeTarget::TYPE_EXT])['ExtensionDistributor'];
		$branchs = array_keys($links);
		$branchRange =  array_diff($branchs, ['master', 'source']);
		array_walk($branchRange, function (&$value, $key) {
			$branch = MWBranch::parseBranchName($value);
			$value = $branch->toVersion()->__toString();
		});
		usort($branchRange, function ($a, $b) : int {
			return version_compare($a, $b);
		});
		return $branchRange;
	}
}
