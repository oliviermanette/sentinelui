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
  const DB_HOST = '';
  /**
  * Database name
  * @var string
  */
  const DB_NAME = '';
  /**
  * Database user
  * @var string
  */
  const DB_USER = '';
  /**
  * Database password
  * @var string
  */
  const DB_PASSWORD = '';
  /**
  * Show or hide error messages on screen
  * @var boolean
  */
  const SHOW_ERRORS = true;

  /**
  * Secret key for hashing
  * @var boolean
  */
  const SECRET_KEY = '';

  /**
  * API KEY Sendgrid
  * @var boolean
  */
  const SENDGRID_API_KEY = '';

  /**
  * API KEY opencagedata geocode
  * @var boolean
  */
  const GEOCODER_API_KEY = '';
}
