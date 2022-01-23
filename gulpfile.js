/*
	TODO: build tasks for minified, (babeled?) and non-sourcemapped building
*/
var { series, parallel, src, dest, watch } = require("gulp");
const pug = require("gulp-pug-3");
// var babel = require("gulp-babel");
var sass = require("gulp-sass")(require("sass"));
var sourcemaps = require("gulp-sourcemaps");
var del = require("del");
var uglify = require("gulp-uglify");

function clean() {
	return del("./dist");
}

let copyVendors = async () => {
	// Bootstrap (js)
	await src("./node_modules/bootstrap/dist/js/**").pipe(
		dest("./dist/vendor/bootstrap")
	);
	// Bootstrap Icons
	await src("./node_modules/bootstrap-icons/font/**").pipe(
		dest("./dist/vendor/bootstrap-icons")
	);
	// jQuery
	await src("./node_modules/jquery/dist/**").pipe(dest("./dist/vendor/jquery"));
};

function copyPublic() {
	return src("./src/public/**").pipe(dest("./dist"));
}

function buildHTML() {
	return src("./src/pug/index.pug")
		.pipe(sourcemaps.init())
		.pipe(pug())
		.pipe(sourcemaps.write())
		.pipe(dest("./dist"));
}

function buildStyles() {
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

function uglifyJS() {
	return src(["./dist/**/*.js", "!./dist/vendor"])
		.pipe(uglify())
		.pipe(dest("./dist/js"));
}

module.exports = {
	clean,
	copyVendors,
	copyPublic,
	buildHTML,
	buildStyles,
	watch: series(clean, copyVendors, () => {
		watch("./src/public/**", { ignoreInitial: false }, copyPublic);
		watch("./src/pug/**/*.pug", { ignoreInitial: false }, buildHTML);
		watch("./src/scss/**/*.scss", { ignoreInitial: false }, buildStyles);
	}),
	default: series(
		clean,
		parallel(copyPublic, copyVendors, buildHTML, buildStyles),
		uglifyJS
	),
};
