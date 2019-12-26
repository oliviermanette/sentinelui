//https://la-cascade.io/gulp-pour-les-debutants/
//contient la liste des tâches à réaliser
//Le fichier gulpfile.js s'occupe de gérer les tâches à réaliser, leurs options, leurs sources et destination

// Requis
var gulp = require('gulp');

// Include plugins
var plugins = require('gulp-load-plugins')({
    lazy: true,
    pattern: '*'
}); // tous les plugins de package.json

// Variables de chemins
var source = ''; // dossier de travail
var destination = ''; // dossier à livrer

// Clean vendor
/*function clean() {
    return del(["./vendor/"]);
}*/

// Bring third party dependencies from vendor into public directory
function modules() {
    // Bootstrap JS
    var bootstrapJS = gulp.src('./vendor/bootstrap/dist/js/*')
        .pipe(gulp.dest('./public/bootstrap/js'));
    // Bootstrap SCSS
    var bootstrapSCSS = gulp.src('./vendor/bootstrap/scss/**/*')
        .pipe(gulp.dest('./public/bootstrap/scss'));
    // ChartJS
    var chartJS = gulp.src('./vendor/chart.js/dist/*.js')
        .pipe(gulp.dest('./public/chart.js'));
    // Semantic UI 
    var semantic = gulp.src([
            './vendor/semantic/dis/*.js',
            './vendor/semantic/css/*.css'
        ])
        .pipe(gulp.dest('./public/semantic'));
    // dataTables
    var dataTables = gulp.src([
            './vendor/datatables.net/js/*.js',
            './vendor/datatables.net-dt/js/*.js',
            './vendor/datatables.net-dt/css/*.css'
        ])
        .pipe(gulp.dest('./public/datatables'));
    // Font Awesome
    var fontAwesome = gulp.src('./vendor/components-font-awesome/**/*')
        .pipe(gulp.dest('./public'));
    // jQuery
    var jquery = gulp.src([
            './vendor/jquery/dist/*',
            '!./vendor/jquery/dist/core.js'
        ])
        .pipe(gulp.dest('./public/jquery'));
    return plugins.mergeStream(bootstrapJS, bootstrapSCSS, chartJS, semantic, dataTables, fontAwesome, jquery);
}


///// TASKS

// CSS task
function css() {
    return gulp
        .src("./public/scss/**/*.scss") //cible tous les fichiers ayant une extension .scss dans le dossier racine et dans n’importe quel dossier enfant
        .pipe(plumber())
        .pipe(sass({ //// Converts Sass to CSS with gulp-sas
            outputStyle: "nested",
            includePaths: "./vendor",
        }))
        .on("error", sass.logError)
        .pipe(autoprefixer({
            cascade: false
        }))
        .pipe(gulp.dest("./public/css"))
        .pipe(rename({
            suffix: ".min"
        }))
        .pipe(cleanCSS())
        .pipe(gulp.dest("./public/css"))
}

// JS task
function js() {
    return gulp
        .src([
            './public/js/*.js',
            '!./public/js/*.min.js',
        ])
        .pipe(uglify())
        .pipe(rename({
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
//const vendor = gulp.series(clean, modules);
const vendor = gulp.series(modules);
//const build = gulp.series(vendor, gulp.parallel(css, js));

// Export tasks
exports.css = css;
exports.js = js;
//exports.clean = clean;
exports.vendor = vendor;
//exports.build = build;
//exports.watch = watch;
//exports.default = build;