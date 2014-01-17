RSS & Atom Feeds for PHP
========================

RSS & Atom Feeds for PHP is a very small and easy-to-use library for consuming an RSS and Atom feeds.

It requires PHP 5.0 or newer with CURL extension or enabled allow_url_fopen
and is licensed under the New BSD License. You can obtain the latest version from
our [GitHub repository](http://github.com/dg/rss-php) or install it via Composer:

	php composer.phar require dg/rss-php


Usage
-----

Download RSS feed from URL:

	$rss = Feed::loadRss($url);

The returned properties are SimpleXMLElement objects. Extracting
the information from the channel is easy:

	echo 'Title: ', $rss->title;
	echo 'Description: ', $rss->description;
	echo 'Link: ', $rss->link;

	foreach ($rss->item as $item) {
		echo 'Title: ', $item->title;
		echo 'Link: ', $item->link;
		echo 'Timestamp: ', $item->timestamp;
		echo 'Description ', $item->description;
		echo 'HTML encoded content: ', $item->{'content:encoded'};
	}

Download Atom feed from URL:

	$atom = Feed::loadAtom($url);



-----
(c) David Grudl, 2008 (http://davidgrudl.com)
