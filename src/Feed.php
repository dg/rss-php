<?php

/**
 * RSS for PHP - small and easy-to-use library for consuming an RSS Feed
 *
 * @copyright  Copyright (c) 2008 David Grudl
 * @license    New BSD License
 * @version    1.5
 */
class Feed
{
	/** @var int */
	public static $cacheExpire = '1 day';

	/** @var string */
	public static $cacheDir;

	/** @var string */
	public static $userAgent = 'FeedFetcher-Google';

	/** @var array */
	public static $supportedJsonfeedVersions = [
		'https://jsonfeed.org/version/1',
		'https://jsonfeed.org/version/1.1'
	];

	/** @var SimpleXMLElement */
	protected $xml;


	/**
	 * Loads RSS or Atom feed.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return Feed
	 * @throws FeedException
	 */
	public static function load($url, $user = null, $pass = null)
	{
		$xml = self::loadXml($url, $user, $pass);
		if ($xml->channel) {
			return self::fromRss($xml);
		} else {
			return self::fromAtom($xml);
		}
	}


	/**
	 * Loads RSS feed.
	 * @param  string  RSS feed URL
	 * @param  string  optional user name
	 * @param  string  optional password
	 * @return Feed
	 * @throws FeedException
	 */
	public static function loadRss($url, $user = null, $pass = null)
	{
		return self::fromRss(self::loadXml($url, $user, $pass));
	}


	/**
	 * Loads Atom feed.
	 * @param  string  Atom feed URL
	 * @param  string  optional user name
	 * @param  string  optional password
	 * @return Feed
	 * @throws FeedException
	 */
	public static function loadAtom($url, $user = null, $pass = null)
	{
		return self::fromAtom(self::loadXml($url, $user, $pass));
	}


	/**
	 * Loads JSON feed.
	 * @param  string  JSON feed URL
	 * @param  string  optional user name
	 * @param  string  optional password
	 * @return Feed
	 * @throws FeedException
	 */
	public static function loadJsonfeed($url, $user = null, $pass = null)
	{
		return self::fromJson(self::loadJson($url, $user, $pass));
	}


	private static function fromRss(SimpleXMLElement $xml)
	{
		if (!$xml->channel) {
			throw new FeedException('Invalid feed.');
		}

		self::adjustNamespaces($xml);

		foreach ($xml->channel->item as $item) {
			// converts namespaces to dotted tags
			self::adjustNamespaces($item);

			// generate 'url' & 'timestamp' tags
			$item->url = (string) $item->link;
			if (isset($item->{'dc:date'})) {
				$item->timestamp = strtotime($item->{'dc:date'});
			} elseif (isset($item->pubDate)) {
				$item->timestamp = strtotime($item->pubDate);
			}
		}
		$feed = new self;
		$feed->xml = $xml->channel;
		return $feed;
	}


	private static function fromAtom(SimpleXMLElement $xml)
	{
		if (!in_array('http://www.w3.org/2005/Atom', $xml->getDocNamespaces(), true)
			&& !in_array('http://purl.org/atom/ns#', $xml->getDocNamespaces(), true)
		) {
			throw new FeedException('Invalid feed.');
		}

		// generate 'url' & 'timestamp' tags
		foreach ($xml->entry as $entry) {
			$entry->url = (string) $entry->link['href'];
			$entry->timestamp = strtotime($entry->updated);
		}
		$feed = new self;
		$feed->xml = $xml;
		return $feed;
	}


	private static function fromJson(object $json)
	{
		if (!in_array($json->version, self::$supportedJsonfeedVersions, true)) {
			throw new FeedException('Invalid feed.');
		}

		// generate 'url' & 'timestamp' tags
		foreach ($json->items as $item) {
			$item->timestamp = strtotime($item->date_published);

			if (empty($item->content_text)) {
				$item->summary = (string) strip_tags($item->content_html);
			}
			if (empty($item->content_html)) {
				$item->summary = (string) strip_tags($item->content_text);
			}
			if (empty($item->summary)) {
				$item->summary = (string) $item->content_text;
			}
		}
		$feed = new self;
		$feed->xml = $json;
		return $feed;
	}


	/**
	 * Returns property value. Do not call directly.
	 * @param  string  tag name
	 * @return SimpleXMLElement
	 */
	public function __get($name)
	{
		return $this->xml->{$name};
	}


	/**
	 * Sets value of a property. Do not call directly.
	 * @param  string  property name
	 * @param  mixed   property value
	 * @return void
	 */
	public function __set($name, $value)
	{
		throw new Exception("Cannot assign to a read-only property '$name'.");
	}


	/**
	 * Converts a SimpleXMLElement into an array.
	 * @param  SimpleXMLElement
	 * @return array
	 */
	public function toArray(SimpleXMLElement $xml = null)
	{
		if ($xml === null) {
			$xml = $this->xml;
		}

		if (!$xml->children()) {
			return (string) $xml;
		}

		$arr = [];
		foreach ($xml->children() as $tag => $child) {
			if (count($xml->$tag) === 1) {
				$arr[$tag] = $this->toArray($child);
			} else {
				$arr[$tag][] = $this->toArray($child);
			}
		}

		return $arr;
	}


	/**
	 * Load XML from cache or HTTP.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return SimpleXMLElement
	 * @throws FeedException
	 */
	private static function loadXml($url, $user, $pass)
	{
		$e = self::$cacheExpire;
		$cacheFile = self::$cacheDir . '/feed.' . md5(serialize(func_get_args())) . '.xml';

		if (self::$cacheDir
			&& (time() - @filemtime($cacheFile) <= (is_string($e) ? strtotime($e) - time() : $e))
			&& $data = @file_get_contents($cacheFile)
		) {
			// ok
		} elseif ($data = trim(self::httpRequest($url, $user, $pass))) {
			if (self::$cacheDir) {
				file_put_contents($cacheFile, $data);
			}
		} elseif (self::$cacheDir && $data = @file_get_contents($cacheFile)) {
			// ok
		} else {
			throw new FeedException('Cannot load feed.');
		}

		return new SimpleXMLElement($data, LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_NOCDATA);
	}


	/**
	 * Load JSON from cache or HTTP.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return object
	 * @throws FeedException
	 */
	private static function loadJson($url, $user, $pass)
	{
		$e = self::$cacheExpire;
		$cacheFile = self::$cacheDir . '/feed.' . md5(serialize(func_get_args())) . '.xml';

		if (self::$cacheDir
			&& (time() - @filemtime($cacheFile) <= (is_string($e) ? strtotime($e) - time() : $e))
			&& $data = @file_get_contents($cacheFile)
		) {
			// ok
		} elseif ($data = trim(self::httpRequest($url, $user, $pass))) {
			if (self::$cacheDir) {
				file_put_contents($cacheFile, $data);
			}
		} elseif (self::$cacheDir && $data = @file_get_contents($cacheFile)) {
			// ok
		} else {
			throw new FeedException('Cannot load feed.');
		}

		$json = json_decode($data);
		if(!$json){
			throw new FeedException('Cannot parse feed.');
		}

		return $json;
	}


	/**
	 * Process HTTP request.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return string|false
	 * @throws FeedException
	 */
	private static function httpRequest($url, $user, $pass)
	{
		if (extension_loaded('curl')) {
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			if ($user !== null || $pass !== null) {
				curl_setopt($curl, CURLOPT_USERPWD, "$user:$pass");
			}
			curl_setopt($curl, CURLOPT_USERAGENT, self::$userAgent); // some feeds require a user agent
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_TIMEOUT, 20);
			curl_setopt($curl, CURLOPT_ENCODING, '');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // no echo, just return result
			if (!ini_get('open_basedir')) {
				curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // sometime is useful :)
			}
			$result = curl_exec($curl);
			return curl_errno($curl) === 0 && curl_getinfo($curl, CURLINFO_HTTP_CODE) === 200
				? $result
				: false;

		} else {
			$context = null;
			if ($user !== null && $pass !== null) {
				$options = [
					'http' => [
						'method' => 'GET',
						'header' => 'Authorization: Basic ' . base64_encode($user . ':' . $pass) . "\r\n",
					],
				];
				$context = stream_context_create($options);
			}

			return file_get_contents($url, false, $context);
		}
	}


	/**
	 * Generates better accessible namespaced tags.
	 * @param  SimpleXMLElement
	 * @return void
	 */
	private static function adjustNamespaces($el)
	{
		foreach ($el->getNamespaces(true) as $prefix => $ns) {
			$children = $el->children($ns);
			foreach ($children as $tag => $content) {
				$el->{$prefix . ':' . $tag} = $content;
			}
		}
	}
}



/**
 * An exception generated by Feed.
 */
class FeedException extends Exception
{
}
