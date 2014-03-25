<?php
/*
Plugin Name: Pods Frontier Auto Template
Plugin URI: http://pods.io
Description: Creates automatic output of Pods custom post types via Pods Templates.
Version: 0.0.1
Author: Pods Framework Team
Author URI: http://pods.io/about/
Text Domain: pods-pfat
License: GPL v2 or later
*/

/**
 * Copyright (c) 2014 Josh Pollock (email: Josh@JoshPress.net). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

//add a dev mode for this
if ( !defined( 'PODS_PFAD_DEV_MODE' ) ) {
	define( 'PODS_PFAD_DEV_MODE', false );
}

//constant for the transient expiration time
if ( !defined( 'PODS_PFAT_TRANSIENT_EXPIRE' ) ) {
	define( 'PODS_PFAT_TRANSIENT_EXPIRE', DAY_IN_SECONDS );
}

/**
 * Pods_Omega class
 *
 * @class Pods_Omega The class that holds the entire Pods_Omega plugin
 *
 * @since 0.0.1
 */
class Pods_PFAT {

	/**
	 * Constructor for the Pods_Omega class
	 *
	 * Sets up all the appropriate hooks and actions
	 * within the plugin.
	 *
	 * @since 0.0.1
	 */
	public function __construct() {

		/**
		 * Plugin Setup
		 */
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// Localize our plugin
		add_action( 'init', array( $this, 'localization_setup' ) );

		/**
		 * These hooks make the Pods Omega Magic Happen
		 */
		//Add option tab for post types
		add_filter( 'pods_admin_setup_edit_tabs_post_type', array( $this, 'tab' ), 11, 3 );

		//Add options to that tab
		add_filter( 'pods_admin_setup_edit_options_post_type', array( $this, 'options' ), 12, 2 );

		//Include and init front-end class
		add_action( 'plugins_loaded', array( $this, 'front_end' ) );

		//Delete transients when Pods settings are updated.
		add_action( 'update_option', array( $this, 'reset' ), 21, 3 );

	}

	/**
	 * Initializes the Pods_Omega() class
	 *
	 * Checks for an existing Pods_Omega() instance
	 * and if it doesn't find one, creates it.
	 *
	 * @since 0.0.1
	 */
	public static function init() {

		static $instance = false;

		if ( !$instance ) {
			$instance = new Pods_PFAT();
		}

		return $instance;

	}

	/**
	 * Placeholder for activation function
	 *
	 * @since 0.0.1
	 */
	public function activate() {

	}

	/**
	 * Placeholder for deactivation function
	 *
	 * @since 0.0.1
	 */
	public function deactivate() {

	}

	/**
	 * Initialize plugin for localization
	 *
	 * @since 0.0.1
	 */
	public function localization_setup() {

		load_plugin_textdomain( 'pods-pfat', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	}

	/**
	 * Add an Frontier Auto Display option tab.
	 *
	 * @param array $tabs
	 * @param array $pod
	 * @param array $addtl_args
	 *
	 * @return array
	 *
	 * @since 0.0.1
	 */
	function tab( $tabs, $pod, $addtl_args ) {

		$tabs[ 'pods-pfat' ] = __( 'Frontier Auto Display Options', 'pods-pfat' );

		return $tabs;

	}

	/**
	 * Adds options for this plugin under the Frontier Auto Template tab.
	 *
	 * @param array $options
	 * @param array $pod
	 *
	 * @return array
	 *
	 * @since 0.0.1
	 */
	function options( $options, $pod ) {

		$options[ 'pods-pfat' ] = array(
			'pfat_enable' => array(
				'label' => __( 'Enable Automatic Pods Templates for this Pod?', 'pods-pfat' ),
				'help' => __( 'When enabled you can specify the names of Pods templates to be used to display items in this Pod in the front-end.', 'pods-pfat' ),
				'type' => 'boolean',
				'default' => false,
				'dependency' => true,
				'boolean_yes_label' => ''
			),
			'pfat_single' => array(
				'label' => __( 'Single item view template', 'pods-pfat' ),
				'help' => __( 'Name of Pods template to use for single item view', 'pods-pfat' ),
				'type' => 'text',
				'default' => false,
				'depends-on' => array( 'pfat_enable' => true )
			),
			'pfat_archive' => array(
				'label' => __( 'Archive view template', 'pods-pfat' ),
				'help' => __( 'Name of Pods template to use for use in this Pods archive pages.', 'pods-pfat' ),
				'type' => 'text',
				'default' => false,
				'depends-on' => array( 'pfat_enable' => true )
			),
		);

		return $options;

	}

	/**
	 * Include/ init the front end class on the front end only
	 *
	 * @return Pods_PFAT_Frontend
	 *
	 * @since 0.0.1
	 */
	function front_end() {

		if ( PODS_PFAD_DEV_MODE ) {
			$this->delete_transients();
		}

		if ( !is_admin() ) {
			include_once( 'classes/front-end.php' );

			$GLOBALS[ 'Pods_PFAT_Frontend' ] = new Pods_PFAT_Frontend();
		}

	}

	/**
	 * Reset the transients for front-end class when Pods are saved.
	 *
	 * @uses update_option hook
	 *
	 * @param string $option
	 * @param mixed $old_value
	 * @param mixed $value
	 *
	 * @since 0.0.1
	 */
	function reset( $option, $old_value, $value ) {

		if ( $option === '_transient_pods_flush_rewrites' ) {
			$this->delete_transients();
		}

	}

	/**
	 * Delete the transients set by this plugin
	 *
	 * @since 0.0.1
	 */
	function delete_transients() {

		delete_transient( 'pods_pfat_pods' );
		delete_transient( 'pods_pfat_the_pods' );

	}

} // Pods_Omega

$GLOBALS[ 'Pods_PFAT' ] = Pods_PFAT::init();