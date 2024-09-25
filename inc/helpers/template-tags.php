<?php
/**
 * Custom template tags for the theme.
 *
 * @package GravityformsFlutterwaveAddons
 */
if( ! function_exists( 'is_FwpActive' ) ) {
  function is_FwpActive( $opt ) {
    if( ! defined( 'GRAVITYFORMS_FLUTTERWAVE_ADDONS_OPTIONS' ) ) {return false;}
    return ( isset( GRAVITYFORMS_FLUTTERWAVE_ADDONS_OPTIONS[ $opt ] ) && GRAVITYFORMS_FLUTTERWAVE_ADDONS_OPTIONS[ $opt ] == 'on' );
  }
}
if( ! function_exists( 'get_FwpOption' ) ) {
  function get_FwpOption( $opt, $def = false ) {
    if( ! defined( 'GRAVITYFORMS_FLUTTERWAVE_ADDONS_OPTIONS' ) ) {return false;}
    return isset( GRAVITYFORMS_FLUTTERWAVE_ADDONS_OPTIONS[ $opt ] ) ? GRAVITYFORMS_FLUTTERWAVE_ADDONS_OPTIONS[ $opt ] : $def;
  }
}