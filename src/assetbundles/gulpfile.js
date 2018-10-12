'use strict';

var gulp = require('gulp'),
    rename = require('gulp-rename'),
    uglify = require('gulp-uglify'),
    sass = require('gulp-sass');

gulp.task('js', function() {
    return gulp.src('src/**/*.js')
        .pipe(uglify())
        .pipe(rename({ extname: '.min.js' }))
        .pipe(gulp.dest('dist/js/'));
});

gulp.task('sass', function() {
    return gulp.src('src/**/*.scss')
        .pipe(sass({ outputStyle: 'compressed' }))
        .pipe(rename({ extname: '.min.css' }))
        .pipe(gulp.dest('dist/css/'));
});

gulp.task('watch', function(){
    gulp.watch('src/**/*.js', ['js']);
    gulp.watch('src/**/*.scss', ['sass']);
});

gulp.task('default', ['js','sass']);