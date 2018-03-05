<?php
/**
 * Bootstrap the tests, mocking some functions that are expected.
 */
function _x( $a ) { return $a; }
function __( $a ) { return $a; }
function add_action() { return true; }
function plugin_basename() { return true; }
function get_site_option() { return array(); }
function is_multisite() { return false; }
function register_uninstall_hook() { return false; }
require_once 'restricted_site_access.php';
