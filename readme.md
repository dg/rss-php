RSS & Atom Feeds for PHP
========================

[![Downloads this Month](https://img.shields.io/packagist/dm/dg/rss-php.svg)](https://packagist.org/packages/dg/rss-php)
[![Latest Stable Version](https://poser.pugx.org/dg/rss-php/v/stable)](https://github.com/dg/rss-php/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/dg/rss-php/blob/master/license.md)

RSS & Atom Feeds for PHP is a very small and easy-to-use library for consuming an RSS and Atom feeds.

It requires PHP 5.2.2 or newer with CURL extension or enabled allow_url_fopen
and is licensed under the New BSD License. You can obtain the latest version from
our [GitHub repository](https://github.com/dg/rss-php/releases) or install it via Composer:

```
php composer.phar require dg/rss-php
```

Usage
-----

Download RSS feed from URL:

```php
$rss = Feed::loadRss($url);
```

The returned properties are SimpleXMLElement objects. Extracting
the information from the channel is easy:

```php
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
```

Download Atom feed from URL:

```php
$atom = Feed::loadAtom($url);
```

You can also enable caching:

```php
Feed::$cacheDir = __DIR__ . '/tmp';
Feed::$cacheExpire = '5 hours';
```

-----
(c) David Grudl, 2008 (http://davidgrudl.com)
