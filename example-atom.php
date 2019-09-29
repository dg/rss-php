<?php
header('Content-Type: text/html; charset=utf-8');

if (!ini_get('date.timezone')) {
	date_default_timezone_set('Europe/Prague');
}

require_once 'src/Feed.php';


$atom = Feed::loadAtom('https://php.vrana.cz/atom.php');

?>

<h1><?php echo htmlspecialchars($atom->title) ?></h1>

<?php foreach ($atom->entry as $entry): ?>
	<h2><a href="<?php echo htmlspecialchars($entry->link['href']) ?>"><?php echo htmlspecialchars($entry->title) ?></a>
	<small><?php echo date('j.n.Y H:i', (int) $entry->timestamp) ?></small></h2>

	<?php if ($entry->content['type'] == 'html'): ?>
		<div><?php echo $entry->content ?></div>
	<?php else: ?>
		<p><?php echo htmlspecialchars($entry->content) ?></p>
	<?php endif ?>
<?php endforeach ?>
