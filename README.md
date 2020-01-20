# Sentive App

## Download and Installation
To begin using this code:
-   Clone the repo: `git clone https://github.com/oliviermanette/sentinelui.git`
-   [Fork, Clone, or Download on GitHub](https://github.com/oliviermanette/sentinelui)

## Usage

After installation, run the following commands :
- `npm install`.
- `bower install`
- `gulp`

### Gulp Tasks

-   `gulp` the default task that builds everything
- `gulp build` the default task that builds everything
-   `gulp watch`  live reloads when changes are made in CSS or JS
-   `gulp css` compiles SCSS files into CSS and minifies the compiled CSS
-   `gulp js` minifies the themes JS file
-   `gulp vendor` copies dependencies from node_modules to the vendor directory

You can view the `gulpfile.js` to see which tasks are included with the dev environment.

### Config

Add to APP folder, `Config.php`


```php

<?php
namespace App;
/**
* Application configuration
*
* PHP version 7.0
*/

class Config
{
  /**
  * Database host
  * @var string
  */
  const DB_HOST = '*';
  /**
  * Database name
  * @var string
  */
  const DB_NAME = '*';
  /**
  * Database user
  * @var string
  */
  const DB_USER = '*';
  /**
  * Database password
  * @var string
  */
  const DB_PASSWORD = '*';
  /**
  * Show or hide error messages on screen
  * @var boolean
  */
  const SHOW_ERRORS = true;

  /**
  * Secret key for hashing
  * @var boolean
  */
  const SECRET_KEY = '*';

  /**
  * API KEY Sendgrid
  * @var boolean
  */
  const SENDGRID_API_KEY = '*';

  /**
  * API KEY opencagedata geocode
  * @var boolean
  */
  const GEOCODER_API_KEY = '*';
}

```  


bower.json for front end package
composer.json for backend package
gulpfile.json for automation task
