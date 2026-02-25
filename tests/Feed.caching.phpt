<?php declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test('feed is read from cache', function () {
	$dir = getTempDir();
	Feed::$cacheDir = $dir;
	Feed::$cacheExpire = '1 day';

	// pre-populate cache for a fake URL
	$url = 'http://example.com/cached';
	$cacheFile = $dir . '/feed.' . md5(serialize([$url, null, null])) . '.xml';
	copy(__DIR__ . '/fixtures/rss.xml', $cacheFile);

	$feed = Feed::loadRss($url);
	Assert::same('Test RSS Feed', (string) $feed->title);
});


test('expired cache is not used without HTTP fallback', function () {
	$dir = getTempDir();
	Feed::$cacheDir = $dir;
	Feed::$cacheExpire = 0;

	$url = 'http://invalid.example.com/feed';
	$cacheFile = $dir . '/feed.' . md5(serialize([$url, null, null])) . '.xml';
	copy(__DIR__ . '/fixtures/rss.xml', $cacheFile);

	// set mtime to the past so the cache is expired
	touch($cacheFile, time() - 3600);

	// with expired cache and unreachable URL, it falls back to stale cache
	$feed = Feed::loadRss($url);
	Assert::same('Test RSS Feed', (string) $feed->title);
});
