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

// tâche BrowserSync pour permettre la création d’un serveur
function browserSync(done) {
    plugins.browserSync.init({
        server: {
            baseDir: "./" //racine du serveur
        },
        port: 3000
    });
    done();
}


gulp.task('browserSync', function () {
    browserSync({
        server: {
            baseDir: './'
        },
    })
})

// BrowserSync reload
function browserSyncReload(done) {
    plugins.browserSync.reload();
    done();
}

// Bring third party dependencies from vendor into public directory
function modules() {
     // Bootstrap JS
     var bootstrapDatePickerJS = gulp.src('./vendor/bootstrap-daterangepicker/daterangepicker.js')
         .pipe(gulp.dest('./public/vendor/bootstrap-daterangepicker/js'));
    var bootstrapDatePickerCSS = gulp.src('./vendor/bootstrap-daterangepicker/daterangepicker.css')
        .pipe(gulp.dest('./public/vendor/bootstrap-daterangepicker/css'));
    // Bootstrap JS
    var bootstrapJS = gulp.src('./vendor/bootstrap/dist/js/*')
        .pipe(gulp.dest('./public/vendor/bootstrap/js'));
    // Bootstrap CSS
    var bootstrapCSS = gulp.src('./vendor/bootstrap/dist/css/*')
        .pipe(gulp.dest('./public/vendor/bootstrap/css'));
    // Bootstrap SCSS
    var bootstrapSCSS = gulp.src('./vendor/bootstrap/scss/**/*')
        .pipe(gulp.dest('./public/vendor/bootstrap/scss'));
    // Leaflet
    var leafletJS = gulp.src('./vendor/leaflet/*.js')
            .pipe(gulp.dest('./public/vendor/leaflet/'));
    // ChartJS
    var chartJS = gulp.src('./vendor/chart.js/dist/*.js')
        .pipe(gulp.dest('./public/vendor/chart.js'));
    var chartJSdatalabel = gulp.src('./vendor/chartjs-plugin-datalabels/dist/*.js')
        .pipe(gulp.dest('./public/vendor/chartjs-plugin-datalabels.js'));
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
            './vendor/datatables.net-responsive/js/*.js',
            './vendor/datatables.net-responsive/css/*.css',
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
    return plugins.mergeStream(bootstrapJS, bootstrapCSS, bootstrapSCSS, leafletJS, chartJS, semantic, dataTables, fontAwesome, jquery);
}

///// TASKS

//JS
/*
gulp.task('clean', async function () {
    plugins.del('./public/vendor/');
    plugins.del('./public/css/sentive-admin.css');
    plugins.del('./public/css/sentive-admin.min.css');
})*/

// Clean vendor
function clean() {
    return plugins.del(["./public/vendor/*", './public/css/sentive-admin.css', './public/css/sentive-admin.min.css']);
}

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
        .pipe(plugins.browserSync.stream());
}

// JS task
function js() {
    return gulp
        .src([
            './public/js/*.js',
            '!./public/js/*.min.js',
        ])
        .pipe(plugins.terser())
        .pipe(plugins.rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest('./public/js'))
}

//JS gulp-useref concatène tous les fichiers CSS ou JavaScript en un seul
gulp.task('useref', function () {
    var assets = plugins.useref.assets();

    return gulp.src('./App/Views/*.html')
        .pipe(assets)
        .pipe(assets.restore())
        .pipe(plugins.useref())
        .pipe(gulp.dest('./public/dist'))
});

// Watch files
function watchFiles() {
    //observer tous les fichiers Sass et lancer la tâche css à chaque fois qu’un fichier Sass est sauvegardé
    gulp.watch("./public/scss/**/*", css);
    gulp.watch("./vendor/bootstrap/**/*", css);
    gulp.watch(["./public/js/**/*", "!./public/js/**/*.min.js"], js);
    //Reloads the browser whenever HTML, JS or PHP files change
    gulp.watch(["./App/Views/**/*.html", "./public/js/**/*.js", "./**/*.php"], browserSyncReload);
}

// Define complex tasks
//gulp vendor copies dependencies from vendor to the public directory
const vendor = gulp.series(clean, modules);
//compiles SCSS files into CSS and minifies the compiled CSS and minifies the themes JS file
const build = gulp.series(vendor, gulp.parallel(css, js));
//const build = gulp.series(vendor, gulp.series(css));
//reloads and build when changes are made
const watch = gulp.series(build, watchFiles);

// Export tasks
exports.css = css;
exports.js = js;
exports.clean = clean;
exports.vendor = vendor;
exports.build = build;
exports.watch = watch;
exports.default = build;