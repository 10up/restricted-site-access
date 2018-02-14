<?php
/**
 * Bootstrap the tests.
 */

require_once '/Users/adamsilverstein/repositories/restricted-site-access/vendor/autoload.php';
WP_Mock::bootstrap();

function plugin_basename() { return true; }
function get_site_option() { return array(); }
function is_multisite() { return false; }
function register_uninstall_hook() { return false; }
require_once '/Users/adamsilverstein/repositories/restricted-site-access/restricted_site_access.php';
