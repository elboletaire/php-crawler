<?php
/**
 * @author Oscar Casajuana a.k.a. elboletaire <elboletaire {at} underave {dot} net>
 */
namespace Elboletaire\Crawler;

/*
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 */

class Crawler
{
	private $depth = 2;
	private $url;
	private $results = array();
	private $same_host = false;
	private $host;

	public function setDepth($depth) { $this->depth = $depth; }
	public function setHost($host) { $this->host = $host; }
	public function getResults() { return $this->results; }
	public function setSameHost($same_host) { $this->same_host = $same_host; }

	public function setUrl($url)
	{
		$this->url = $url;
		$this->setHost($this->getHostFromUrl($url));
	}

	public function __construct($url = null, $depth = null, $same_host = false)
	{
		if (!empty($url)) {
			$this->setUrl($url);
		}
		if (isset($depth) && !is_null($depth)) {
			$this->setDepth($depth);
		}
		$this->setSameHost($same_host);
	}

	public function crawl()
	{
		if (empty($this->url)) {
			throw new \Exception('URL must be set');
		}
		$this->_crawl($this->url, $this->depth);
		
		// sort links alphabetically
		usort($this->results, function($a, $b) {
			if ($a['url'] == $b['url']) return 0;
			return (strtolower($a['url']) > strtolower($b['url'])) ? 1 : -1;
		});
		return $this->results;
	}

	private function _crawl($url, $depth)
	{
		static $seen = array();

		if (empty($url)) return;

		if (!$url = $this->buildUrl($this->url, $url)) {
			return;
		}

		if ($depth === 0 || isset($this->results[$url])) {
			return;
		}

		$dom = new \DOMDocument('1.0');
		@$dom->loadHTMLFile($url);

		$this->results[$url] = array(
			'url' => $url,
			'depth' => $depth,
			// 'content' => $dom->saveHTML()
		);
		
		// saving links to find difference later
		$crawled = $seen;

		$anchors = $dom->getElementsByTagName('a');
		foreach ($anchors as $element)
		{
			if (!$href = $this->buildUrl($url, $element->getAttribute('href'))) {
				continue;
			}
			
			if (!in_array($href, $seen)) {
				$seen[] = $href;
			}
		}
		
		// set array difference from links already marked to crawl
		$crawl = array_diff($seen, $crawled);
			
		// check if there are links to crawl
		if (!empty($crawl)) {
			// crawl links
			array_map(array($this, '_crawl'), $crawl, array_fill(0, count($crawl), $depth - 1));
		}

		return $url;
	}

	private function buildUrl($url, $href)
	{
		$url = trim($url);
		$href = trim($href);
		if (0 !== strpos($href, 'http'))
		{
			if (0 === strpos($href, 'javascript:') || 0 === strpos($href, '#'))
			{
				return false;
			}
			$path = '/' . ltrim($href, '/');
			if (extension_loaded('http'))
			{
				$new_href = http_build_url($url, array('path' => $path), HTTP_URL_REPLACE, $parts);
			}
			else
			{
				$parts = parse_url($url);
				$new_href = $this->buildUrlFromParts($parts);
				$new_href .= $path;
			}
			// Relative urls... (like ./viewforum.php)
			if (0 === strpos($href, './') && !empty($parts['path']))
			{
				// If the path isn't really a path (doesn't end with slash)...
				if (!preg_match('@/$@', $parts['path'])) {
					$path_parts = explode('/', $parts['path']);
					array_pop($path_parts);
					$parts['path'] = implode('/', $path_parts) . '/';
				}

				$new_href = $this->buildUrlFromParts($parts) . $parts['path'] . ltrim($href, './');
			}
			$href = $new_href;
		}
		if ($this->same_host && $this->host != $this->getHostFromUrl($href)) {
			return false;
		}
		return $href;
	}

	private function buildUrlFromParts($parts)
	{
		$new_href = $parts['scheme'] . '://';
		if (isset($parts['user']) && isset($parts['pass'])) {
			$new_href .= $parts['user'] . ':' . $parts['pass'] . '@';
		}
		$new_href .= $parts['host'];
		if (isset($parts['port'])) {
			$new_href .= ':' . $parts['port'];
		}
		return $new_href;
	}

	private function getHostFromUrl($url)
	{
		$parts = parse_url($url);
		preg_match("@([^/.]+)\.([^.]{2,6}(?:\.[^.]{2,3})?)$@", $parts['host'], $host);
		return array_shift($host);
	}
}
