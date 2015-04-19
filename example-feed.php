<?php
header('Content-Type: text/html; charset=utf-8');
if (!ini_get('date.timezone')) {
	date_default_timezone_set('Europe/Prague');
}
require_once 'src/Feed.php';
$rss = Feed::loadFeed('http://feeds.bbci.co.uk/news/health/rss.xml');
$atom = Feed::loadFeed('http://www.theregister.co.uk/software/os/headlines.atom');
?>

<h1><?php echo htmlSpecialChars($rss->title) ?></h1>

<p><i><?php echo htmlSpecialChars($rss->description) ?></i></p>

<?php foreach ($rss->item as $item): ?>
	<h2><a href="<?php echo htmlSpecialChars($item->link) ?>"><?php echo htmlSpecialChars($item->title) ?></a>
	<small><?php echo date("j.n.Y H:i", (int) $item->timestamp) ?></small></h2>

	<?php if (isset($item->{'content:encoded'})): ?>
		<div><?php echo $item->{'content:encoded'} ?></div>
	<?php else: ?>
		<p><?php echo htmlSpecialChars($item->description) ?></p>
	<?php endif ?>
<?php endforeach ?>

<h1><?php echo htmlSpecialChars($atom->title) ?></h1>

<?php foreach ($atom->entry as $entry): ?>
	<h2><a href="<?php echo htmlSpecialChars($entry->link['href']) ?>"><?php echo htmlSpecialChars($entry->title) ?></a>
	<small><?php echo date("j.n.Y H:i", (int) $entry->timestamp) ?></small></h2>

	<?php if ($entry->content['type'] == 'html'): ?>
		<div><?php echo $entry->content ?></div>
	<?php else: ?>
		<p><?php echo htmlSpecialChars($entry->content) ?></p>
	<?php endif ?>
<?php endforeach ?>
