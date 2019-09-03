const gulp = require('gulp');
const sass = require('gulp-sass');
const concat = require('gulp-concat');
const cleanCSS = require('gulp-clean-css');
const rename = require('gulp-rename');
const jshint = require('gulp-jshint');
const pump = require('pump');
const uglify = require('gulp-uglify');
const imagemin = require('gulp-imagemin');

const plumber = require('gulp-plumber');
const gutil = require('gulp-util');

let onError = function (err) {
    console.log('An error occurred:', gutil.colors.magenta(err.message));
    gutil.beep();
    this.emit('end');
};

// Compile SCSS files and minify CSS files
gulp.task('sass', function() {
    return gulp.src('src/scss/main.scss')
    .pipe(plumber({ errorHandler: onError }))
    .pipe(sass({outputStyle: 'expanded'}))
    .pipe(gulp.dest('src/css'))
    .pipe(cleanCSS())
    .pipe(gulp.dest('web/css'))
});

// Concat and uglify JavaScript files
gulp.task('concat', function() {
    return gulp.src('src/js/*.js')
    .pipe(plumber({ errorHandler: onError }))
    .pipe(jshint())
    .pipe(jshint.reporter('default'))
    .pipe(concat('script.js'))
    .pipe(gulp.dest('web/js'));
});

gulp.task('compress', function(cb) {
    pump([
        gulp.src('web/js/script.js'),
        uglify(),
        rename({suffix: '.min'}),
        gulp.dest('web/js')
    ], cb);
});

// Optimize images
gulp.task('images', function() {
    return gulp.src('src/images/*.*')
    .pipe(imagemin({optimizationLevel: 7, progressive: true}))
    .pipe(gulp.dest('web/images'));
});

// Watch task
gulp.task('watch', function() {
    gulp.watch('src/scss/**/*.scss', ['sass']);
    gulp.watch('src/js/*.js', ['concat']);
    gulp.watch('web/js/*.js', ['compress']);
    gulp.watch('src/images/*.*', ['images']);
})

// Default task
gulp.task('default', ['sass', 'concat', 'compress', 'images', 'watch']);
