<?php if (!defined("__BASE__")) die("Access denied"); ?>
<section id="alerts">
	<noscript>
		<div class="alert alert-danger border border-danger mb-n3" role="alert" style="margin-top: 2rem;">
			<strong>Warning:</strong> JavaScript is disabled in your browser – some functions will not
			work.<br />
			<a class="btn btn-sm btn-success mt-2 link-inline" href="https://enable-javascript.com/" target="_blank">
				Enabling JavaScript
			</a>
		</div>
	</noscript>
	<?php foreach ($alerts as $alert) : ?>
		<div id="<?= $alert->dismissibleId ?? "" ?>" class="alert alert-<?= $alert->type ?> border border-<?= $alert->type ?> position-relative px-4" role="alert">
			<?php if (property_exists($alert, "icon")) : ?>
				<i class="bi bi-<?= $alert->icon ?> fs-5 top-50"></i>
			<?php endif ?>
			<?= $alert->text ?>
			<?php if (property_exists($alert, "dismissibleId")) : ?>
				<button id="dismiss" class="btn-close custom-close top-50" type="button" aria-label="Close" data-close-target=<?= $alert->dismissibleId ?>></button>
			<?php endif ?>
		</div>
	<?php endforeach ?>
</section>