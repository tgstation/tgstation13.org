# How to configure

You can define a custom configuration `config.json` and either overwrite the existing one or edit the environment variable `PUG_CONFIG` (default: `"./src/pug/config.json"`)

```JSONC
// ./src/pug/config.json
{
	"alerts": [
		{
			"type": "warning", // contextual class https://getbootstrap.com/docs/5.1/components/alerts/#examples
			"text": "This is an important announcement", // Body text
			"icon": "exclamation-circle", // icon from https://icons.getbootstrap.com/ (optional)
			// If it should be dismissible, this must be a unique ID. If permanent, remove property
			"dismissibleId": "test-announcement"
		}
	],
	"navigation": [
		{
			// This is a simple link
			"href": "#",
			"text": "Top-level link"
		},
		{
			// A dropdown menu that opens on click
			"text": "Dropdown Menu Header",
			"href": "#", // only reachable via Mouse 3 (open in new tab) or when Js is blocked
			"dropdownId": "navbarDropdownExample", // must be unique
			"children": [
				// You can use three different types of child elements
				{ "href": "#", "text": "Link" }, // Normal Link child
				{ "type": "div" }, // Separator
				{ "type": "text", "text": "Just some Text" } // Plain text, no link
			]
		}
	]
}
```
