<?php
header('Content-Type: text/html; charset=utf-8');

if (!ini_get('date.timezone')) {
	date_default_timezone_set('Europe/Prague');
}

require_once 'src/Feed.php';

$json = Feed::loadJsonfeed('https://www.jsonfeed.org/feed.json');

?>

<h1><?php echo htmlspecialchars($json->title) ?></h1>

<p><i><?php if(isset($json->description)){ echo htmlspecialchars($json->description); } ?></i></p>

<?php foreach ($json->items as $item): ?>
	<h2><a href="<?php echo htmlspecialchars($item->url) ?>"><?php echo htmlspecialchars($item->title) ?></a>
	<small><?php echo date('j.n.Y H:i', (int) $item->timestamp) ?></small></h2>

	<?php if (isset($item->content_html)): ?>
		<div><?php echo $item->content_html ?></div>
	<?php else: ?>
		<p><?php echo htmlspecialchars($item->summary) ?></p>
	<?php endif ?>
<?php endforeach ?>
