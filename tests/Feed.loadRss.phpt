<?php declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test('RSS feed properties', function () {
	setupCache('http://example.com/rss', __DIR__ . '/fixtures/rss.xml');
	$feed = Feed::loadRss('http://example.com/rss');

	Assert::same('Test RSS Feed', (string) $feed->title);
	Assert::same('A test RSS feed', (string) $feed->description);
});


test('RSS feed items', function () {
	setupCache('http://example.com/rss', __DIR__ . '/fixtures/rss.xml');
	$feed = Feed::loadRss('http://example.com/rss');

	$items = [];
	foreach ($feed->item as $item) {
		$items[] = $item;
	}

	Assert::count(2, $items);
	Assert::same('First Article', (string) $items[0]->title);
	Assert::same('https://example.com/first', (string) $items[0]->url);
	Assert::same('Description of first article', (string) $items[0]->description);
});


test('RSS feed pubDate to timestamp', function () {
	setupCache('http://example.com/rss', __DIR__ . '/fixtures/rss.xml');
	$feed = Feed::loadRss('http://example.com/rss');

	$item = $feed->item[0];
	Assert::same(strtotime('Mon, 01 Jan 2024 12:00:00 +0000'), (int) $item->timestamp);
});
