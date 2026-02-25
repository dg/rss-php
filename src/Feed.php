<?php declare(strict_types=1);

/**
 * RSS for PHP - small and easy-to-use library for consuming an RSS Feed
 */
class Feed
{
	public static string|int $cacheExpire = '1 day';
	public static ?string $cacheDir = null;
	public static string $userAgent = 'FeedFetcher-Google';
	protected SimpleXMLElement $xml;


	/**
	 * Loads RSS or Atom feed.
	 * @throws FeedException
	 */
	public static function load(string $url, ?string $user = null, ?string $pass = null): static
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
	 * @throws FeedException
	 */
	public static function loadRss(string $url, ?string $user = null, ?string $pass = null): static
	{
		return self::fromRss(self::loadXml($url, $user, $pass));
	}


	/**
	 * Loads Atom feed.
	 * @throws FeedException
	 */
	public static function loadAtom(string $url, ?string $user = null, ?string $pass = null): static
	{
		return self::fromAtom(self::loadXml($url, $user, $pass));
	}


	private static function fromRss(SimpleXMLElement $xml): static
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
				$item->timestamp = strtotime((string) $item->{'dc:date'});
			} elseif (isset($item->pubDate)) {
				$item->timestamp = strtotime((string) $item->pubDate);
			}
		}
		$feed = new static;
		$feed->xml = $xml->channel;
		return $feed;
	}


	private static function fromAtom(SimpleXMLElement $xml): static
	{
		$ns = $xml->getDocNamespaces() ?: [];
		if (!in_array('http://www.w3.org/2005/Atom', $ns, strict: true)
			&& !in_array('http://purl.org/atom/ns#', $ns, strict: true)
		) {
			throw new FeedException('Invalid feed.');
		}

		// generate 'url' & 'timestamp' tags
		foreach ($xml->entry as $entry) {
			$entry->url = (string) $entry->link['href'];
			$entry->timestamp = strtotime((string) $entry->updated);
		}
		$feed = new static;
		$feed->xml = $xml;
		return $feed;
	}


	/**
	 * Returns property value. Do not call directly.
	 */
	public function __get(string $name): mixed
	{
		return $this->xml->{$name};
	}


	/**
	 * Sets value of a property. Do not call directly.
	 */
	public function __set(string $name, mixed $value): void
	{
		throw new Exception("Cannot assign to a read-only property '$name'.");
	}


	/**
	 * Converts a SimpleXMLElement into an array.
	 */
	public function toArray(?SimpleXMLElement $xml = null): string|array
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
	 * @throws FeedException
	 */
	private static function loadXml(string $url, ?string $user, ?string $pass): SimpleXMLElement
	{
		$e = self::$cacheExpire;
		$cacheFile = self::$cacheDir . '/feed.' . md5(serialize(func_get_args())) . '.xml';

		if (self::$cacheDir
			&& (time() - @filemtime($cacheFile) <= (is_string($e) ? strtotime($e) - time() : $e))
			&& $data = @file_get_contents($cacheFile)
		) {
			// ok
		} elseif (($data = self::httpRequest($url, $user, $pass)) !== false && $data = trim($data)) {
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
	 * Process HTTP request.
	 */
	private static function httpRequest(string $url, ?string $user, ?string $pass): string|false
	{
		if (extension_loaded('curl')) {
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			if ($user !== null || $pass !== null) {
				curl_setopt($curl, CURLOPT_USERPWD, "$user:$pass");
			}
			curl_setopt($curl, CURLOPT_USERAGENT, self::$userAgent); // some feeds require a user agent
			curl_setopt($curl, CURLOPT_HEADER, value: false);
			curl_setopt($curl, CURLOPT_TIMEOUT, 20);
			curl_setopt($curl, CURLOPT_ENCODING, '');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, value: true); // no echo, just return result
			if (!ini_get('open_basedir')) {
				curl_setopt($curl, CURLOPT_FOLLOWLOCATION, value: true); // sometime is useful :)
			}
			$result = curl_exec($curl);
			return curl_errno($curl) === 0 && curl_getinfo($curl, CURLINFO_HTTP_CODE) === 200 && is_string($result)
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
	 */
	private static function adjustNamespaces(SimpleXMLElement $el): void
	{
		foreach ($el->getNamespaces(true) as $prefix => $ns) {
			if ($prefix === '') {
				continue;
			}
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
