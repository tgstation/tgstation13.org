<?php
if (!defined("__BASE__")) die("Access denied");
/* Format of a screenshot-array-entry
[
	"img" => "1.png", Image file path relative to to ./img/carousel/
	"caption" => "A xenomorph infection", Displayed caption with the image on larger screens. Accepts HTML.
],
*/
$screenshots = [
	[
		"img" => "1.png",
		"caption" => "A xenomorph infection",
	],
	[
		"img" => "2.png",
		"caption" => "Miners setting out to get minerals",
	],
	[
		"img" => "3.png",
		"caption" => "A normal shift in Chemistry",
	],
];

?>
<section id="introText">
	<div class="card bg-dark-850 border mt-5">
		<div class="card-body pb-2">
			<div class="custom-title">
				<div class="triangle-before">&nbsp;</div>
				<h4 class="title">What is Space Station 13</h4>
				<div class="triangle-after">&nbsp;</div>
			</div><button class="btn-close custom-close" id="dismiss" type="button" aria-label="Close" data-close-target="introText"></button>
			<p class="mt-2 mb-0">
				Space Station 13 is a paranoia-laden round-based roleplaying game set against the backdrop of a nonsensical, metal death trap masquerading as a space station, with charming spritework designed to represent the sci-fi setting and its dangerous undertones. Have fun, and survive!
			</p>
			<div id="screenshotCarousel" class="carousel slide my-2" data-bs-ride="carousel">
				<div class="carousel-inner">
					<?php foreach ($screenshots as $index => $screenshot) : ?>
						<div class="carousel-item<?= $index === 0 ? " active" : "" ?>">
							<img src="./img/carousel/<?= $screenshot["img"] ?>" class="d-block w-100" loading="lazy">
							<div class="carousel-caption d-none d-md-flex p-0 flex-row justify-content-center">
								<div class="bg-dark-850 px-3 py-1">
									<?= $screenshot["caption"] ?>
								</div>
							</div>
						</div>
					<?php endforeach ?>
				</div>
				<button class="carousel-control-prev" type="button" data-bs-target="#screenshotCarousel" data-bs-slide="prev">
					<span class="carousel-control-prev-icon" aria-hidden="true"></span>
					<span class="visually-hidden">Previous</span>
				</button>
				<button class="carousel-control-next" type="button" data-bs-target="#screenshotCarousel" data-bs-slide="next">
					<span class="carousel-control-next-icon" aria-hidden="true"></span>
					<span class="visually-hidden">Next</span>
				</button>
			</div>
			<div class="d-flex justify-content-center">
				<a href="https://tgstation13.org/wiki/Starter_guide" target="_blank">Starter Guide</a>
				<a class="ms-4" href="https://secure.byond.com/download/?invite=Tgstation" target="_blank">Get BYOND</a>
			</div>
		</div>
	</div>
</section>