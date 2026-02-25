<?php declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test('toArray converts feed to array', function () {
	setupCache('http://example.com/rss', __DIR__ . '/fixtures/rss.xml');
	$feed = Feed::loadRss('http://example.com/rss');

	$arr = $feed->toArray();
	Assert::type('array', $arr);
	Assert::same('Test RSS Feed', $arr['title']);
	Assert::same('A test RSS feed', $arr['description']);
});


test('toArray includes items', function () {
	setupCache('http://example.com/rss', __DIR__ . '/fixtures/rss.xml');
	$feed = Feed::loadRss('http://example.com/rss');

	$arr = $feed->toArray();
	Assert::type('array', $arr['item']);
	Assert::count(2, $arr['item']);
});
