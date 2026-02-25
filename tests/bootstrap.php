<?php declare(strict_types=1);

if (@!include __DIR__ . '/../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer install`';
	exit(1);
}

Tester\Environment::setup();
Tester\Environment::setupFunctions();


function getTempDir(): string
{
	$dir = __DIR__ . '/tmp/' . getmypid();

	if (empty($GLOBALS['\lock'])) {
		$GLOBALS['\lock'] = $lock = fopen(__DIR__ . '/lock', 'w');
		if (rand(0, 100)) {
			flock($lock, LOCK_SH);
			@mkdir(dirname($dir));
		} elseif (flock($lock, LOCK_EX)) {
			Tester\Helpers::purge(dirname($dir));
		}

		@mkdir($dir);
	}

	return $dir;
}


/**
 * Pre-populates cache so Feed::load*() reads from fixture file instead of HTTP.
 */
function setupCache(string $url, string $fixtureFile, ?string $user = null, ?string $pass = null): void
{
	$dir = getTempDir();
	Feed::$cacheDir = $dir;
	Feed::$cacheExpire = '1 day';
	$cacheFile = $dir . '/feed.' . md5(serialize([$url, $user, $pass])) . '.xml';
	copy($fixtureFile, $cacheFile);
}
