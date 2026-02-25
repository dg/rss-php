<?php declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test('auto-detect RSS feed', function () {
	setupCache('http://example.com/rss', __DIR__ . '/fixtures/rss.xml');
	$feed = Feed::load('http://example.com/rss');

	Assert::same('Test RSS Feed', (string) $feed->title);
});


test('auto-detect Atom feed', function () {
	setupCache('http://example.com/atom', __DIR__ . '/fixtures/atom.xml');
	$feed = Feed::load('http://example.com/atom');

	Assert::same('Test Atom Feed', (string) $feed->title);
});
