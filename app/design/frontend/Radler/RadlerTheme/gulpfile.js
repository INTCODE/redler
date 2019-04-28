var gulp = require('gulp'),
sass = require('gulp-sass'),
cleanCSS = require('gulp-clean-css'),
inlineCss = require('gulp-inline-css'),
autoprefixer = require('gulp-autoprefixer'),
inlineCss = require('gulp-inline-css');

sass.compiler = require('node-sass');

gulp.task('sass', function () {
  return gulp.src('./scss/main.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(gulp.dest('./web/css'));
});

 

gulp.task('minify-css', () => {
    return gulp.src('./web/css/*.css')
      .pipe(cleanCSS({compatibility: 'ie8', inline:'local'}))
      .pipe(gulp.dest('./web/css'));
  });

gulp.task('prefixer', () =>
  gulp.src('web/css/custom.css')
      .pipe(autoprefixer({
          browsers: ['last 2 versions'],
          cascade: false
      }))
      .pipe(gulp.dest('./web/css/minified'))
);

 
gulp.task('watch', function () {
  gulp.watch('./scss/*.scss', gulp.series('sass'));
//   gulp.watch('./web/css/*.css', gulp.series('prefixer'));
//   gulp.watch('./scss/*.scss', gulp.series('minify-css'));
});
