//https://la-cascade.io/gulp-pour-les-debutants/
//contient la liste des tâches à réaliser
//Le fichier gulpfile.js s'occupe de gérer les tâches à réaliser, leurs options, leurs sources et destination

// Requis
var gulp = require('gulp');

// On récupère tous les plugins du package.json
var plugins = require('gulp-load-plugins')({
    lazy: true,
    pattern: '*'
});

// Bring third party dependencies from vendor into public directory
function modules() {
    // Bootstrap JS
    var bootstrapJS = gulp.src('./vendor/bootstrap/dist/js/*')
        .pipe(gulp.dest('./public/vendor/bootstrap/js'));
    // Bootstrap CSS
    var bootstrapCSS = gulp.src('./vendor/bootstrap/dist/css/*')
            .pipe(gulp.dest('./public/vendor/bootstrap/css'));
    // Bootstrap SCSS
    var bootstrapSCSS = gulp.src('./vendor/bootstrap/scss/**/*')
        .pipe(gulp.dest('./public/vendor/bootstrap/scss'));
    // ChartJS
    var chartJS = gulp.src('./vendor/chart.js/dist/*.js')
        .pipe(gulp.dest('./public/vendor/chart.js'));
    // Semantic UI 
    var semantic = gulp.src([
            './vendor/semantic/dist/*.js',
            './vendor/semantic/dist/*.css'
        ])
        .pipe(gulp.dest('./public/vendor/semantic'));
    // dataTables
    var dataTables = gulp.src([
            './vendor/datatables.net/js/*.js',
            './vendor/datatables.net-dt/js/*.js',
            './vendor/datatables.net-dt/css/*.css'
        ])
        .pipe(gulp.dest('./public/vendor/datatables'));
    // Font Awesome
    var fontAwesome = gulp.src('./vendor/components-font-awesome/**/*')
        .pipe(gulp.dest('./public/vendor/font-awesome'));
    // jQuery
    var jquery = gulp.src([
            './vendor/jquery/dist/*',
            '!./vendor/jquery/dist/core.js'
        ])
        .pipe(gulp.dest('./public/vendor/jquery'));
    // jQuery validation
    var jqueryValidation = gulp.src([
            './vendor/jquery-validation/dist/*.js',
        ])
        .pipe(gulp.dest('./public/vendor/jquery-validation'));
    return plugins.mergeStream(bootstrapJS, bootstrapCSS, bootstrapSCSS, chartJS, semantic, dataTables, fontAwesome, jquery);
}

///// TASKS

// CSS task
function css() {
    return gulp
        .src("./public/scss/**/*.scss") //cible tous les fichiers ayant une extension .scss dans le dossier racine et dans n’importe quel dossier enfant
        .pipe(plugins.plumber())
        .pipe(plugins.sass({ //// Converts Sass to CSS with gulp-sas
            outputStyle: "nested",
            includePaths: "./vendor",
        }))
        .on("error", plugins.sass.logError)
        .pipe(plugins.autoprefixer({
            cascade: false
        }))
        .pipe(gulp.dest("./public/css"))
        .pipe(plugins.rename({
            suffix: ".min"
        }))
        .pipe(plugins.cleanCss())
        .pipe(gulp.dest("./public/css"))
}

// JS task
function js() {
    return gulp
        .src([
            './public/js/*.js',
            '!./public/js/*.min.js',
        ])
        .pipe(plugins.uglify())
        .pipe(plugins.rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest('./public/js'))
}

// Watch files
function watchFiles() {
    //observer tous les fichiers Sass et lancer la tâche css à chaque fois qu’un fichier Sass est sauvegardé
    gulp.watch("./public/scss/**/*", css);
    gulp.watch(["./public/js/**/*", "!./public/js/**/*.min.js"], js);
}

// Define complex tasks
//gulp vendor copies dependencies from vendor to the public directory
const vendor = gulp.series(modules);
//compiles SCSS files into CSS and minifies the compiled CSS and minifies the themes JS file
//const build = gulp.series(vendor, gulp.parallel(css, js));
const build = gulp.series(vendor, gulp.series(css));
//reloads and build when changes are made
const watch = gulp.series(build, watchFiles);

// Export tasks
exports.css = css;
exports.js = js;
exports.vendor = vendor;
exports.build = build;
exports.watch = watch;
exports.default = build;