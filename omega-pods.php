<?php
/*
Plugin Name: Omega Pods
Plugin URI: http://pods.io
Description: Creates automatic output of Pods custom post types via Pods Templates.
Version: 0.0.1
Author: Pods Framework Team
Author URI: http://pods.io/about/
Text Domain: omega-pods
License: GPL v2 or later
*/

/**
 * Copyright (c) YEAR Your Name (email: Email). All rights reserved.
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
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Omega_Pods class
 *
 * @class Omega_Pods The class that holds the entire Omega_Pods plugin
 *
 * @since 0.0.1
 */
class Omega_Pods {

	/**
	 * Constructor for the Omega_Pods class
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
		 * Scripts/ Styles
		 */
		// Loads frontend scripts and styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Loads admin scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );


		/**
		 * These hooks make the Pods Omega Magic Happen
		 */
		//Add option tab for post types
		add_filter( 'pods_admin_setup_edit_tabs_post_type', array( $this, 'omega_tab' ), 11, 3 );

		//Add options to that tab
		add_filter( 'pods_admin_setup_edit_options_post_type', array( $this, 'omega_options' ), 12, 2 );

		//Include and init front-end class
		add_action( 'plugins_loaded', array(  $this, 'omega' ) );

		add_action( 'update_option', array( $this, 'reset'), 21, 3 );
		
	}

	/**
	 * Initializes the Omega_Pods() class
	 *
	 * Checks for an existing Omega_Pods() instance
	 * and if it doesn't find one, creates it.
	 *
	 * @since 0.0.1
	 */
	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new Omega_Pods();
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
		load_plugin_textdomain( 'omega-pods', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Enqueue front-end scripts
	 *
	 * Allows plugin assets to be loaded.
	 *
	 * @since 0.0.1
	 */
	public function enqueue_scripts() {

		/**
		 * All styles goes here
		 */
		wp_enqueue_style( 'omega-pods-styles', plugins_url( 'css/front-end.css', __FILE__ ) );

		/**
		 * All scripts goes here
		 */
		wp_enqueue_script( 'omega-pods-scripts', plugins_url( 'js/front-end.js', __FILE__ ), array( ), false, true );


		/**
		 * Example for setting up text strings from Javascript files for localization
		 *
		 * Uncomment line below and replace with proper localization variables.
		 */
		// $translation_array = array( 'some_string' => __( 'Some string to translate', 'omega-pods' ), 'a_value' => '10' );
		// wp_localize_script( 'omega-pods-scripts', 'podsExtend', $translation_array ) );

	}

	/**
	 * Enqueue admin scripts
	 *
	 * Allows plugin assets to be loaded.
	 *
	 * @since 0.0.1
	 */
	public function admin_enqueue_scripts() {

		/**
		 * All admin styles goes here
		 */
		wp_enqueue_style( 'omega-pods-admin-styles', plugins_url( 'css/admin.css', __FILE__ ) );

		/**
		 * All admin scripts goes here
		 */
		wp_enqueue_script( 'omega-pods-admin-scripts', plugins_url( 'js/admin.js', __FILE__ ), array( ), false, true );


	}

	/**
	 * Add an Omega Pods option tab.
	 *
	 * @param $tabs
	 * @param $pod
	 * @param $addtl_args
	 *
	 * @return mixed
	 *
	 * @since 0.0.1
	 */
	function omega_tab( $tabs, $pod, $addtl_args ) {
		$tabs[ 'omega-pods' ] = __( 'Omega Pods Options', 'omega-pods' );
		return $tabs;
	}

	/**
	 * Adds options for this plugin under the omega tab.
	 *
	 * @param $options
	 * @param $pod
	 *
	 * @return mixed
	 *
	 * @since 0.0.1
	 */
	function omega_options( $options, $pod  ) {
		$options[ 'omega-pods' ] = array(
			'omega_enable' => array(
				'label' => __( 'Enable Automatic Pods Templates for this Pod?', 'pods' ),
				'help' => __( 'When enabled you can specify the names of Pods templates to be used to display items in this Pod in the front-end.', 'pods' ),
				'type' => 'boolean',
				'default' => false,
				'dependency' => true,
				'boolean_yes_label' => ''
			),
			'omega_single' => array(
				'label' => __( 'Single item view template', 'pods' ),
				'help' => __( 'Name of Pods template to use for single item view', 'pods' ),
				'type' => 'text',
				'default' => false,
				'depends-on' => array( 'omega_enable' => true )
			),
			'omega_archive' => array(
				'label' => __( 'Archive view template', 'pods' ),
				'help' => __( 'Name of Pods template to use for use in this Pods archive pages.', 'pods' ),
				'type' => 'text',
				'default' => false,
				'depends-on' => array( 'omega_enable' => true )
			),
		);
		return $options;
	}

	/**
	 * Include/ init the front end class on the front end only
	 *
	 * @return Omega_Pods_Frontend
	 *
	 * @since 0.0.1
	 */
	function omega() {
		if ( !is_admin() ) {
			include_once( 'classes/Omega-Pods-Frontend.php' );
			$omega = new Omega_Pods_Frontend();
			return $omega;
		}
	}

	/**
	 * Reset the transients for front-end class when Pods are saved.
	 *
	 * @TODO What hook does this go on?
	 *       
	 * @since 0.0.1
	 */
	function reset(  $option, $old_value, $value ) {
		if ( $option === '_transient_pods_flush_rewrites' ) {
			delete_transient( 'pods_omega_the_omega_pods' );
			delete_transient( 'pods_omega_the_pods' );
		}
	}



} // Omega_Pods

$pods_extend = Omega_Pods::init();