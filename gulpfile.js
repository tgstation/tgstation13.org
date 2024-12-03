/*
	TODO: build tasks for minified, (babeled?) and non-sourcemapped building
*/
var { series, parallel, src, dest, watch } = require("gulp");
var sass = require("gulp-sass")(require("sass"));
var sourcemaps = require("gulp-sourcemaps");
var del = require("del");

function clean() {
	return del(["./src/css", "./src/vendor"]);
}

let copyVendors = async () => {
	// Bootstrap (js)
	await src("./node_modules/bootstrap/dist/js/bootstrap.bundle.min.js").pipe(
		dest("./src/vendor")
	);
	// Bootstrap Icons
	await src("./node_modules/bootstrap-icons/font/bootstrap-icons.css").pipe(
		dest("./src/vendor/bootstrap-icons")
	);
	await src("./node_modules/bootstrap-icons/font/fonts/*").pipe(
		dest("./src/vendor/bootstrap-icons/fonts")
	);
	// jQuery
	await src("./node_modules/jquery/dist/jquery.min.js").pipe(
		dest("./src/vendor")
	);
};

function buildStyles() {
	return src("./src/scss/**/*.scss")
		.pipe(sourcemaps.init())
		.pipe(
			sass({
				includePaths: ["./node_modules/"],
			}).on("error", sass.logError)
		)
		.pipe(sourcemaps.write())
		.pipe(dest("./src/css"));
}

module.exports = {
	clean,
	copyVendors,
	buildStyles,
	watch: series(clean, copyVendors, () => {
		watch("./src/scss/**/*.scss", { ignoreInitial: false }, buildStyles);
	}),
	default: series(clean, parallel(copyVendors, buildStyles)),
};
