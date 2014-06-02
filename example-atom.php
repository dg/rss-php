<?php
header('Content-Type: text/html; charset=utf-8');

if (!ini_get('date.timezone')) {
	date_default_timezone_set('Europe/Prague');
}

require_once 'src/Feed.php';


$atom = Feed::loadAtom('http://php.vrana.cz/atom.php');

?>

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
