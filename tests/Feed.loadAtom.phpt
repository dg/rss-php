<?php declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test('Atom feed properties', function () {
	setupCache('http://example.com/atom', __DIR__ . '/fixtures/atom.xml');
	$feed = Feed::loadAtom('http://example.com/atom');

	Assert::same('Test Atom Feed', (string) $feed->title);
});


test('Atom feed entries', function () {
	setupCache('http://example.com/atom', __DIR__ . '/fixtures/atom.xml');
	$feed = Feed::loadAtom('http://example.com/atom');

	$entries = [];
	foreach ($feed->entry as $entry) {
		$entries[] = $entry;
	}

	Assert::count(2, $entries);
	Assert::same('First Entry', (string) $entries[0]->title);
	Assert::same('https://example.com/entry1', (string) $entries[0]->url);
	Assert::same('Content of first entry', (string) $entries[0]->content);
});


test('Atom feed updated to timestamp', function () {
	setupCache('http://example.com/atom', __DIR__ . '/fixtures/atom.xml');
	$feed = Feed::loadAtom('http://example.com/atom');

	$entry = $feed->entry[0];
	Assert::same(strtotime('2024-01-01T12:00:00Z'), (int) $entry->timestamp);
});
