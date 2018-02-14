<?php
/**
 * Bootstrap the tests.
 */

require_once 'vendor/autoload.php';
WP_Mock::bootstrap();

function plugin_basename() { return true; }
function get_site_option() { return array(); }
function is_multisite() { return false; }
function register_uninstall_hook() { return false; }
require_once 'restricted_site_access.php';
