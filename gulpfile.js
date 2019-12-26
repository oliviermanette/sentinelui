//contient la liste des tâches à réaliser
//Le fichier gulpfile.js s'occupe de gérer les tâches à réaliser, leurs options, leurs sources et destination

// Requis
var gulp = require('gulp');

// Include plugins
var plugins = require('gulp-load-plugins')(); // tous les plugins de package.json

// Variables de chemins
var source = ''; // dossier de travail
var destination = ''; // dossier à livrer

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
    return merge(bootstrapJS, bootstrapSCSS, chartJS, dataTables, semantic, fontAwesome, jquery);
}

///// TASKS

// CSS task
function css() {
    return gulp
        .src("./public/scss/**/*.scss")
        .pipe(plumber())
        .pipe(sass({
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