/*
	TODO: build tasks for minified, (babeled?) and non-sourcemapped building
*/
var { series, src, dest, watch } = require("gulp");
const pug = require("gulp-pug-3");
// var babel = require("gulp-babel");
var sass = require("gulp-sass")(require("sass"));
var sourcemaps = require("gulp-sourcemaps");
var del = require("del");

function clean() {
	return del("./dist");
}

function copyPublic() {
	return src("./src/public/**").pipe(dest("./dist"));
}

function buildHTML() {
	return src("./src/pug/index.pug")
		.pipe(sourcemaps.init())
		.pipe(
			pug({
				// Your options.
			})
		)
		.pipe(sourcemaps.write())
		.pipe(dest("./dist"));
}

function buildStylesDev() {
	return src("./src/scss/**/*.scss")
		.pipe(sourcemaps.init())
		.pipe(
			sass({
				includePaths: ["./node_modules/"],
			}).on("error", sass.logError)
		)
		.pipe(sourcemaps.write())
		.pipe(dest("./dist/css"));
}

module.exports = {
	clean,
	copyPublic,
	buildHTML,
	buildStylesDev,
	watch: function () {
		clean();
		watch("./src/public/**", { ignoreInitial: false }, copyPublic);
		watch("./src/pug/**/*.pug", { ignoreInitial: false }, buildHTML);
		watch("./src/scss/**/*.scss", { ignoreInitial: false }, buildStylesDev);
	},
	default: series(clean, copyPublic, buildStylesDev),
};
