# tgstation13 website

This repository includes the landing page and tgdb, a web interface for game admins.

## Landing page

The landing page must be built in order to get its static parts compiled.

### Docker

- Clone, Build docker image: `docker build -t tgstation .`
- Or download it instead `docker pull ghcr.io/tgstation.org/tgstation-web:master`

- Start container: `docker-compose up`
  The webserver is available at ports 80 and 443

### Manual with npm

- Clone
- Install dependencies: `npm i`
- Build site: `npm run build` or `npm run dev` to auto-build on file changes
  This does not start a webserver

See `./src/pug/config.json` for configuration of alerts and navbar.

### Technologies

- [Pug](https://pughtml.com/) - Pre-processor for HTML. Helps splitting large documents into smaller parts as well as easy alerts / navbar editing. See `./src/pug`.
- [sass](https://sass-lang.com/) - Pre-processor for CSS
- [Bootstrap 5](https://getbootstrap.com/) - CSS framework. Installed as sass, in order to modify it with ease. See `./src/scss`.
-

## tgdb

It's a magic box, only MrStonedOne knows what it does and how it works.
