# tgstation13 website

This repository includes the landing page and tgdb, a web interface for game admins.

## Landing page

The landing page must be built in order to get its static parts compiled. This is done via [npm](https://www.npmjs.com/). Clone, run `npm i` and then either `npm run build` or `npm run dev` to build once or watch the source and re-build respectively.

You can also use docker to build and launch a webserver:
```
docker build -t tgstation .
docker run -d -p 80:80 -p 443:443 --name tgstation-webserver tgstation
```

See `src\pug\config.pug` for configuration of alerts and navbar.

## tgdb

It's a magic box, only MrStonedOne knows what it does and how it works.

### Technologies

- [Pug](https://pughtml.com/) - Pre-processor for HTML. Helps splitting large documents into smaller parts as well as easy alerts / navbar editing. See `src\pug`.
- [sass](https://sass-lang.com/) - Pre-processor for CSS
- [Bootstrap 5](https://getbootstrap.com/) - CSS framework. Installed as sass, in order to modify it with ease. See `src\scss`.
