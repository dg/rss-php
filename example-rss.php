<?php
header('Content-Type: text/html; charset=utf-8');

if (!ini_get('date.timezone')) {
	date_default_timezone_set('Europe/Prague');
}

require_once 'src/Feed.php';

$rss = Feed::loadRss('https://phpfashion.com/feed/rss');

?>

<h1><?php echo htmlspecialchars($rss->title) ?></h1>

<p><i><?php echo htmlspecialchars($rss->description) ?></i></p>

<?php foreach ($rss->item as $item): ?>
	<h2><a href="<?php echo htmlspecialchars($item->link) ?>"><?php echo htmlspecialchars($item->title) ?></a>
	<small><?php echo date('j.n.Y H:i', (int) $item->timestamp) ?></small></h2>

	<?php if (isset($item->{'content:encoded'})): ?>
		<div><?php echo $item->{'content:encoded'} ?></div>
	<?php else: ?>
		<p><?php echo htmlspecialchars($item->description) ?></p>
	<?php endif ?>
<?php endforeach ?>
