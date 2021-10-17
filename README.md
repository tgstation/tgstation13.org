# tgstation13 website

This repository includes the landing page and tgdb, a web interface for game admins.

## tgdb

It's a magic box, and only MrStonedOne knows what it does and how it works.

## landing page

The landing page must be build in order to get its static parts compiled. This is done via [npm](https://www.npmjs.com/). Clone, run `npm i` and then either `npm run build` or `npm run dev` to build or watch the source respectively.

### Technologies

- [Pug](https://pughtml.com/) - Pre-processor for HTML. Helps splitting large documents into smaller parts as well as easy navbar / alerts editing. See `src\pug`.
- [sass](https://sass-lang.com/) - Pre-processor for CSS
- [Bootstrap 5](https://getbootstrap.com/) - CSS framework. We install it as sass, in order to modify it with ease. See `src\scss`.
