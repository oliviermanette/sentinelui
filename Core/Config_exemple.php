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
  const DB_HOST = '92.243.19.37';
  /**
   * Database name
   * @var string
   */
  const DB_NAME = 'sentinel_test';
  /**
   * Database user
   * @var string
   */
  const DB_USER = 'admin';
  /**
   * Database password
   * @var string
   */
  const DB_PASSWORD = 'eoL4p0w3r';
  /**
   * Show or hide error messages on screen
   * @var boolean
   */
  const SHOW_ERRORS = true;

  /**
   * Secret key for hashing
   * @var boolean
   */
  const SECRET_KEY = 'UoIjfyG4CQc1cVjKjQ0B30kmHPreWD4w';

  /**
   * API KEY Sendgrid
   * @var boolean
   */
  const SENDGRID_API_KEY = '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';

  /**
   * API KEY opencagedata geocode
   * @var boolean
   */
  const GEOCODER_API_KEY = '5d7ac6990e384fa2b565b40ebed54cd1';


  /**
   * API KEY objenious
   * @var boolean
   */
  const OBJENIOUS_API_KEY = 'F4qOJBjtdWpegP+JLNvyQmAD1Q/kbIKvzGadwaosqq4XJ/g6+3ypQVRQbEl0/VI1p6n4tKTPxqsj/72QKGbm9g==';
  const WEATHER_API_KEY = '65937fadd944026a55689f26e08f3d80';
  const WEATHERSTACK_API_KEY = 'f602d009dcafdb47000c98bab4037aba';
  const WEATHERMETEO_STAT_API_KEY = 'atPuIx0k';
  const WEATHERDARK_SKY_API_KEY = 'dab904736376060e6787d2d4c86a5e47';
  const WEATHER_VISUALCROSSING_API_KEY = '052NMBEM22M3WRCGVFD7209PV';
}
