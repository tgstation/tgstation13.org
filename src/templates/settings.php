<?php if (!defined("__BASE__")) die("Access denied"); ?>
<button class="btn position-absolute top-0 end-0 mt-2 me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSettings" aria-controls="offcanvasSettings">
	<svg class="bi bi-gear" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewbox="0 0 16 16">
		<path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"></path>
		<path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z"></path>
	</svg>
</button>
<div class="offcanvas offcanvas-end" id="offcanvasSettings" tabindex="-1" aria-labelledby="offcanvasSettingsLabel">
	<div class="offcanvas-header">
		<h5 class="text-center" id="offcanvasSettingsLabel">
			Settings
		</h5>
		<button class="btn-close btn-close-white text-reset me-1" type="button" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>
	<div class="offcanvas-body position-relative">
		<p class="mb-1" id="toggleBgAnimationOverriddenPRM">
			This setting is overwritten by your accessibility settings (<a class="link-inline" href="https://developer.mozilla.org/en-US/docs/Web/CSS/@media/prefers-reduced-motion" target="_blank">prefers-reduced-motion</a>).
		</p>
		<div class="form-check form-switch">
			<input class="form-check-input" id="toggleBgAnimation" type="checkbox" checked="" />
			<label class="form-check-label" for="toggleBgAnimation">Background animation</label>
		</div>
		<div class="form-check form-switch">
			<input class="form-check-input" id="bgStyle" type="checkbox" checked="" />
			<label class="form-check-label" for="bgStyle">Classic background</label>
		</div>
		<hr />
		<p class="mb-1">
			Your preferences are saved between sessions in <a class="link-inline" href="https://javascript.info/localstorage" target="_blank">Local Storage</a>. This is similar to cookies.
		</p>
		<button class="btn btn-sm btn-secondary" id="resetLocalStorage" type="button">
			Delete saved data
		</button>
		<p class="position-absolute bottom-0 start-0 w-100 text-center text-muted">
			<a class="link-inline text-reset" href="https://github.com/tgstation/tgstation13.org">GitHub Repository</a>
			|
			<a class="link-inline text-reset" href="https://github.com/mozi-h/">Redesign by mozi_h</a>
		</p>
	</div>
</div>