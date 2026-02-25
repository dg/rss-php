<?php declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test('loadRss throws on Atom feed', function () {
	setupCache('http://example.com/atom', __DIR__ . '/fixtures/atom.xml');

	Assert::exception(
		fn() => Feed::loadRss('http://example.com/atom'),
		FeedException::class,
		'Invalid feed.',
	);
});


test('loadAtom throws on RSS feed', function () {
	setupCache('http://example.com/rss', __DIR__ . '/fixtures/rss.xml');

	Assert::exception(
		fn() => Feed::loadAtom('http://example.com/rss'),
		FeedException::class,
		'Invalid feed.',
	);
});


test('loadAtom throws on invalid XML', function () {
	setupCache('http://example.com/invalid', __DIR__ . '/fixtures/invalid.xml');

	Assert::exception(
		fn() => Feed::loadAtom('http://example.com/invalid'),
		FeedException::class,
		'Invalid feed.',
	);
});


test('__set throws exception', function () {
	setupCache('http://example.com/rss', __DIR__ . '/fixtures/rss.xml');
	$feed = Feed::loadRss('http://example.com/rss');

	Assert::exception(
		fn() => $feed->foo = 'bar',
		Exception::class,
		"Cannot assign to a read-only property 'foo'.",
	);
});
