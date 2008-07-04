<?php

/**
 * RSS for PHP - small and easy-to-use library for consuming an RSS Feed
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2008 David Grudl
 * @license    New BSD License
 * @link       http://phpfashion.com/
 * @version    1.0
 */
class RssFeed
{
	/** @var SimpleXMLElement */
	private $xml;


	/**
	 * Creates object using your credentials.
	 * @param  string  RSS feed URL
	 * @param  string  optional user name
	 * @param  string  optional password
	 * @throws Exception
	 */
	public function __construct($url, $user = NULL, $pass = NULL)
	{
		$feed = $this->httpRequest($url, $user, $pass);
		if ($feed === FALSE) {
			throw new Exception('Cannot load channel.');
		}

		$this->xml = new SimpleXMLElement($feed);
		if (!$this->xml || !$this->xml->channel) {
			throw new Exception('Invalid channel.');
		}

		$this->adjustNamespaces($this->xml->channel);

		foreach ($this->xml->channel->item as $item) {
			// converts namespaces to dotted tags
			$this->adjustNamespaces($item);

			// generate 'timestamp' tag
			if (isset($item->{'dc:date'})) {
				$item->timestamp = strtotime($item->{'dc:date'});
			} elseif (isset($item->pubDate)) {
				$item->timestamp = strtotime($item->pubDate);
			}
		}
	}



	/**
	 * Returns property value. Do not call directly.
	 * @param  string  tag name
	 * @return SimpleXMLElement
	 */
	protected function __get($name)
	{
		return $this->xml->channel->{$name};
	}



	/**
	 * Sets value of a property. Do not call directly.
	 * @param  string  property name
	 * @param  mixed   property value
	 * @return void
	 */
	protected function __set($name, $value)
	{
		throw new Exception("Cannot assign to a read-only property '$name'.");
	}



	/**
	 * Process HTTP request.
	 * @param  string  URL
	 * @param  string  user name
	 * @param  string  password
	 * @return string|FALSE
	 */
	private function httpRequest($url, $user, $pass)
	{
		if ($user === NULL && $pass === NULL && ini_get('allow_url_fopen')) {
			return file_get_contents($url);
		}

		if (!extension_loaded('curl')) {
			throw new Exception('PHP extension CURL is not loaded.');
		}

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		if ($user !== NULL || $pass !== NULL) {
			curl_setopt($curl, CURLOPT_USERPWD, "$user:$pass");
		}
		curl_setopt($curl, CURLOPT_HEADER, FALSE);
		curl_setopt($curl, CURLOPT_TIMEOUT, 20);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); // no echo, just return result
		$result = curl_exec($curl);
		// debug: echo curl_errno($curl), ', ', curl_error($curl), htmlspecialchars($result);
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if (curl_errno($curl) !== 0 || $code !== 200) {
			return FALSE;
		} else {
			return $result;
		}
	}



	/**
	 * Generates better accessible namespaced tags.
	 * @param  SimpleXMLElement
	 * @return void
	 */
	private function adjustNamespaces($el)
	{
		foreach ($el->getNamespaces(TRUE) as $prefix => $ns) {
			$children = $el->children($ns);
			foreach ($children as $tag => $content) {
				$el->{$prefix . ':' . $tag} = $content;
			}
		}
	}

}
