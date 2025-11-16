// Defining requirements
var gulp = require('gulp');
var plumber = require('gulp-plumber');
var sass = require('gulp-sass');
var rename = require('gulp-rename');
var ignore = require('gulp-ignore');
var sourcemaps = require('gulp-sourcemaps');
var cleanCSS = require('gulp-clean-css');
var uglify = require('gulp-uglify');
var concat = require('gulp-concat');
var watch = require('gulp-watch');
var del = require('del');

/*
 * Scss compile only. Theme style.
 */
gulp.task('styles', function () {

    del(['/css/min/*']);

    gulp.src('./css/**/*.css')
        .pipe(sourcemaps.init({
            loadMaps: true
        }))
        .pipe(cleanCSS({
            compatibility: '*'
        }))
        .pipe(
            plumber({
                errorHandler: function (err) {
                    console.log(err);
                    this.emit('end');
                }
            })
        )
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('./css/min'));

    return gulp.src('./css/**/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(sourcemaps.init({
            loadMaps: true
        }))
        .pipe(cleanCSS({
            compatibility: '*'
        }))
        .pipe(
                plumber({
                    errorHandler: function (err) {
                        console.log(err);
                        this.emit('end');
                    }
                })
            )
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('./css/min'));
});

/*
 * JS minification
 */
gulp.task('scripts', function() {
    return gulp.src('./js/**/*.js')
        .pipe(sourcemaps.init({
            loadMaps: true
        }))
        .pipe(plumber({
            errorHandler: function (err) {
                console.log(err);
                this.emit('end');
            }
        }))
        .pipe(uglify())
        .pipe(rename({ suffix: '.min' }))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('./js/min'));
});

/*
 * Watch task
 */
gulp.task('watch', function() {
    gulp.watch('./css/**/*.scss', gulp.series('styles'));
    gulp.watch('./css/**/*.css', gulp.series('styles'));
    gulp.watch('./js/**/*.js', gulp.series('scripts'));
});

/*
 * Build task
 */
gulp.task('build', gulp.series('styles', 'scripts'));

/*
 * Default task
 */
gulp.task('default', gulp.series('build'));