# tgstation13 website

This repository includes the landing page and tgdb, a web interface for game admins.

## Landing page

The landing page has vendors and compiled SCSS shipped via git. You only need Node.js, if you want to modify SCSS (or vendors for some reason).

### Deploy

Copy `src` to your PHP server. Configure as desired (see below).

### Docker

Start container: `docker-compose up`
The webserver is available at port 80

### Manual with npm

- Clone
- Install dependencies: `npm i`
- `npm run build` or `npm run dev` to rebuild on file changes
  This does not start a webserver

### How to configure

You can define a custom [configuration](./src/config.json). Either overwrite the existing one or edit the environment variable `CONFIG` (default: `"/config.json"`) to point towards a different config. JSON files containing the word config in their name are gitignored (except for the default one).

```jsonc
// ./src/config.json
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

### Technologies

- [PHP](https://php.net/) - Server-side scripting language. Used for templating.
- [sass](https://sass-lang.com/) - Pre-processor for CSS
- [Bootstrap 5](https://getbootstrap.com/) - CSS framework. Installed as sass, in order to modify it with ease. See `./src/scss`.
-

## tgdb

It's a magic box, only MrStonedOne knows what it does and how it works.

## LICENSE

This project is licensed under AGPL-3.0 see [LICENSE](./LICENSE) for more details.

The sound assets are from [tgstation/tgstation](https://github.com/tgstation/tgstation) and are licensed under [Creative Commons 3.0 BY-SA](https://creativecommons.org/licenses/by-sa/3.0/).
