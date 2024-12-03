<?php
define('__BASE__', __DIR__);

$configPath = getenv("CONFIG") ? getenv("CONFIG") : "/config.json";
$configPath = __BASE__ . $configPath;
if (!file_exists($configPath)) {
	die("Config <samp>" . $configPath . "</samp> does not exist");
}
$config = file_get_contents($configPath);
$config = json_decode($config);

require_once __BASE__ . "/templates/renderer.php";

?>
<!DOCTYPE html>
<?= render("head") ?>

<body id="flat-bg" class="">
	<div id="space-bg-container" class="">
		<div class="bg-space space-1"></div>
		<div class="bg-space space-2"></div>
		<div class="bg-space space-2-blue"></div>
		<div class="bg-space space-2-black"></div>
		<div class="bg-space space-2-red"></div>
		<div class="bg-space space-2-yellow"></div>
		<div class="bg-space space-3"></div>
	</div>
	<div>
		<?= render("header", ["navigation" => $config->navigation]) ?>
		<main class="container mb-1">
			<?= render("settings") ?>
			<div class="text-center">
				<img class="img-fluid text-center" id="navbarLogo" src="img/logo.png" loading="lazy" alt="/tg/station 13">
			</div>
			<?= render("sections/alerts", ["alerts" => $config->alerts]) ?>
			<?= render("sections/userbanner") ?>
			<?= render("sections/introText") ?>
			<?= render("sections/servers") ?>
			<div class="mb-3"></div>
		</main>
	</div>
</body>
