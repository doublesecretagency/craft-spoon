const gulp = require('gulp'),
    rename = require('gulp-rename'),
    uglify = require('gulp-uglify'),
    sass = require('gulp-sass');

function jsTask() {
    return gulp.src('src/**/*.js')
        .pipe(uglify())
        .pipe(rename({ extname: '.min.js' }))
        .pipe(gulp.dest('dist/js/'));
}

function sassTask() {
    return gulp.src('src/**/*.scss')
        .pipe(sass({ outputStyle: 'compressed' }))
        .pipe(rename({ extname: '.min.css' }))
        .pipe(gulp.dest('dist/css/'));
}

function watch() {
    gulp.watch('src/**/*.js', ['jsTask']);
    gulp.watch('src/**/*.scss', ['sassTask']);
}

exports.default = gulp.series(jsTask, sassTask);