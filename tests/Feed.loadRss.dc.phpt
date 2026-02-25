<?php declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test('RSS with Dublin Core dc:date to timestamp', function () {
	setupCache('http://example.com/dc', __DIR__ . '/fixtures/rss-dc.xml');
	$feed = Feed::loadRss('http://example.com/dc');

	$item = $feed->item[0];
	Assert::same('DC Article', (string) $item->title);
	Assert::same(strtotime('2024-01-15T10:30:00+00:00'), (int) $item->timestamp);
});


test('RSS with Dublin Core namespaced tags', function () {
	setupCache('http://example.com/dc', __DIR__ . '/fixtures/rss-dc.xml');
	$feed = Feed::loadRss('http://example.com/dc');

	$item = $feed->item[0];
	Assert::same('Test Author', (string) $item->{'dc:creator'});
});


test('RSS with content:encoded', function () {
	setupCache('http://example.com/dc', __DIR__ . '/fixtures/rss-dc.xml');
	$feed = Feed::loadRss('http://example.com/dc');

	$item = $feed->item[0];
	Assert::same('<p>Full HTML content</p>', (string) $item->{'content:encoded'});
});
