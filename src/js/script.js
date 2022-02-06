// Script is loaded defered, no need to window.addEventListener("load", ...)

var dropdownElementList = [].slice.call(
	document.querySelectorAll(".dropdown-toggle")
);
var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
	return new bootstrap.Dropdown(dropdownToggleEl);
});

/* ------------------------ Closed Elements Handling ------------------------ */

closedElementsHandler = {
	closedElementsList: null,
	initialize: function () {
		// Populate this.closedElementsList from localStorage
		this.closedElementsList = this.getLocalStorage();

		// Close all elements on the closedElementsList
		{
			var localStorageNeedsUpdate = false;
			this.closedElementsList.forEach((closedElement, closedElementIndex) => {
				var elem = document.getElementById(closedElement);
				if (elem) elem.remove();
				else {
					// Element could not be found. This should not happen, remove it from the list of closed elements
					this.closedElementsList.splice(closedElementIndex, 1);
					localStorageNeedsUpdate = true;
				}
			});
			if (localStorageNeedsUpdate) closedElementsHandler.updateLocalStorage();
		}
	},
	getLocalStorage: function () {
		// Get the list of closed elements from local Storage
		var localStorageData = localStorage.getItem("closedElements");
		if (localStorageData) return localStorageData.split(";");
		return [];
	},
	updateLocalStorage: function () {
		localStorage.setItem("closedElements", this.closedElementsList.join(";"));
	},
	addElement: function (elementId) {
		// Check if element exists
		var element = document.getElementById(elementId);
		if (!element) return false;

		// Add to list; save
		this.closedElementsList.push(elementId);
		this.updateLocalStorage();

		// Remove element now
		element.remove();
	},
	removeElement: function (elementId) {
		var elementIndex = this.closedElementsList.indexOf(elementId);
		if (elementIndex === -1) return false;
		this.closedElementsList.splice(elementIndex, 1);
		this.updateLocalStorage();
	},
	reset() {
		this.closedElementsList = [];
		localStorage.removeItem("closedElements");
	},
};
closedElementsHandler.initialize();

// Bind closedElementsHandler.addElement to dismiss buttons
var closeElementList = document.querySelectorAll(
	".custom-close[data-close-target]"
);
closeElementList.forEach((closeElement) => {
	closeElement.addEventListener("click", function (event) {
		closedElementsHandler.addElement(this.dataset.closeTarget);
	});
});

/* -------------------------------- Settings -------------------------------- */
settingsHandler = {};

// Manual background animation toggle
// #toggleBgAnimation
settingsHandler.bgAnimation = {
	bgAnimationContainer: document.getElementById("bgAnimation"),
	bgAnimationToggler: document.getElementById("toggleBgAnimation"),
	set: function (enable) {
		if (enable) {
			// Resume bg animation
			this.bgAnimationContainer.classList.remove("pause");

			// Save status (remove, as it defaults to on)
			localStorage.removeItem("bgAnimation");
		} else {
			// Pause bg animation
			this.bgAnimationContainer.classList.add("pause");

			// Save status
			localStorage.setItem("bgAnimation", "false");
		}
	},
	initialize: function () {
		var storedStatus = localStorage.getItem("bgAnimation");
		// Default to on
		if (storedStatus === null) storedStatus = true;
		// If status is stored, only activate if it is exactly "true" (localStorage only stores strings)
		else storedStatus = storedStatus === "true";

		this.bgAnimationToggler.checked = storedStatus;
		this.set(storedStatus);

		// Bind settingsHandler.bgAnimation.set to its settings checkbox toggler
		this.bgAnimationToggler.addEventListener("change", function (event) {
			settingsHandler.bgAnimation.set(this.checked);
		});
	},
	reset: function () {
		this.set(true);
		localStorage.removeItem("bgAnimation");
	},
};
settingsHandler.bgAnimation.initialize();

// Auto-join server on next round (Shift-Click)
autoJoinServer = {
	initialize: function () {
		document.querySelectorAll(".server-banner").forEach((banner) => {
			banner.addEventListener("click", this.clickHandler);
		});

		// In the future, this should be tied-in with the gamebanner updates and not run on an interval
		setInterval(this.check.bind(this), 12000);
	},
	clickHandler: function (event) {
		// Shift-click a server
		if (!event.shiftKey) return;

		event.preventDefault();

		// Request notification permission if available and not yet decided
		if ("Notification" in window && Notification.permission === "default") {
			Notification.requestPermission().then((permission) => {
				// Warn the user about the delay now, instead of in the notifications, if they denied those
				// Users whose browsers don't support Notification do not get a warning about the delay
				if (permission === "denied") {
					alert(
						"Notice that joining is delayed by 10 seconds as joining too early is unreliable"
					);
				}
			});
		}

		let sound;
		// Toggle "watch" class, for styling and tracking
		if (this.classList.toggle("watch")) {
			sound = "./sound/sound_machines_terminal_prompt_confirm.ogg";
		} else {
			sound = "./sound/sound_machines_terminal_error.ogg";
		}

		// Play on / off sound
		sound = new Audio(sound);
		sound.volume = 0.2;
		sound.play();
	},
	check: function () {
		// Is there a watched server in roundend or lobby status?
		let serverToJoin = document.querySelector(
			".server-banner.watch .statuslobby"
		);
		if (!serverToJoin) return;

		this.reset();

		// Play sound
		let sound = new Audio("./sound/sound_effects_ghost2.ogg");
		sound.volume = 0.2;
		sound.play();

		// Notify user via Notification or a fallback alert
		let serverName = serverToJoin
			.querySelector(".gamebanneraddrline") // Scrape from address as the banner name changes
			.innerText.match(/^.*?(?=\.)/)[0];
		serverName = serverName.charAt(0).toUpperCase() + serverName.slice(1); // Capitalize first letter

		if ("Notification" in window && Notification.permission === "granted") {
			// Notify the user about the join delay
			new Notification(`Joining ${serverName}`, {
				icon: "img/favicon.png",
				body: `New game found. Connecting in 10 seconds!`,
			});
		}

		// Join game in 10 seconds (joining is unreliable too early)
		setTimeout(() => {
			serverToJoin.parentElement.click();
		}, 10000);
	},
	reset: function () {
		// Remove the "watch" class from all server banners
		document.querySelectorAll(".server-banner.watch").forEach((banner) => {
			banner.classList.remove("watch");
		});
	},
};
autoJoinServer.initialize();

// resetLocalStorage
document
	.getElementById("resetLocalStorage")
	.addEventListener("click", function (event) {
		closedElementsHandler.reset();
		settingsHandler.bgAnimation.reset();

		this.disabled = true;
		this.classList.remove("btn-secondary");
		this.classList.add("btn-success");
		setTimeout(() => {
			window.location.reload(false);
		}, 50);
	});
