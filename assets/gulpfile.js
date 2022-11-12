// Defining requirements
var gulp = require('gulp');
var plumber = require('gulp-plumber');
var sass = require('gulp-sass');
var rename = require('gulp-rename');
var ignore = require('gulp-ignore');
var sourcemaps = require('gulp-sourcemaps');
var cleanCSS = require('gulp-clean-css');
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