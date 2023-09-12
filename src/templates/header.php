<?php if (!defined("__BASE__")) die("Access denied"); ?>
<header>
	<nav class="navbar navbar-expand-md">
		<div class="container-fluid"><button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
				<div class="navbar-dark"><span class="navbar-toggler-icon"></span></div>
			</button>
			<div class="collapse navbar-collapse" id="navbarSupportedContent">
				<ul class="navbar-nav me-auto mb-2 mb-md-0 w-100 justify-content-center">
					<?php foreach ($navigation as $navItem) : ?>
						<?php if (property_exists($navItem, "dropdownId")) : ?>
							<li class="nav-item dropdown">
								<a id="<?= $navItem->dropdownId ?>" href=" <?= $navItem->href ?>" class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown">
									<?= $navItem->text ?>
								</a>
								<ul class="dropdown-menu dropdown-menu-dark border border-secondary">
									<?php foreach ($navItem->children as $dropdownItem) : ?>
										<?php if (!property_exists($dropdownItem, "type")) : ?>
											<li>
												<a href="<?= $dropdownItem->href ?>" class="dropdown-item">
													<?= $dropdownItem->text ?>
												</a>
											</li>
										<?php elseif ($dropdownItem->type === "div") : ?>
											<li>
												<hr class="dropdown-divider">
											</li>
										<?php elseif ($dropdownItem->type === "text") : ?>
											<li>
												<p class="mb-0 px-3 py-1 font-monospace fs-link text-body">
													<?= $dropdownItem->text ?>
												</p>
											</li>
										<?php endif ?>
									<?php endforeach ?>
								</ul>
							</li>
						<?php else : ?>
							<li class="nav-item">
								<a href="<?= $navItem->href ?>" class="nav-link">
									<?= $navItem->text ?>
								</a>
							</li>
						<?php endif ?>
					<?php endforeach ?>
				</ul>
			</div>
		</div>
	</nav>
</header>