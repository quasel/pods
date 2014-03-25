<?php
/**
 * Front-end functionality for this plugin.
 *
 * @package   @Pods_PFAT
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link	  http://Pods.io
 * @copyright 2014 Josh Pollock
 */
class Pods_PFAT_Frontend {

	function __construct() {

		add_filter( 'the_content', array( $this, 'front' ) );

	}

	/**
	 * Get all registered custom post types
	 *
	 * @return array All cpts.
	 * @since 0.0.1
	 */
	function the_pods() {

		//use the cached results
		$the_pods = get_transient( 'pods_pfat_the_pods' );

		//check if we already have the results cached & use it if we can.
		if ( false === $the_pods || PODS_PFAD_DEV_MODE ) {
			//get all pods of all post types
			$the_pods = pods_api()->load_pods( array( 'type' => 'post_type', 'names' => true ) );

			//cache the results
			set_transient( 'pods_pfat_the_pods', $the_pods, PODS_PFAT_TRANSIENT_EXPIRE );
		}

		return $the_pods;

	}

	/**
	 * Get all Pods with auto template enable and its settings
	 *
	 * @return array With info about auto template settings per post type
	 *
	 * @since 0.0.1
	 */
	function auto_pods() {

		//use the cached results if we can
		$the_pods = get_transient( 'pods_pfat_pods' );

		//check if we already have the results cached & use it if we can.
		if ( false === $the_pods || PODS_PFAD_DEV_MODE ) {
			//get all pods of all post types
			$all_pods = pods_api()->load_pods( array( 'type' => 'post_type', 'names' => true ) );

			$the_pods = array();

			//loop through each to see if omega mode is enabled
			foreach ( $all_pods as $the_pod => $the_pod_label ) {
				$pods = pods_api( $the_pod );

				//if omega mode is enabled add info about Pod to array
				if ( 1 == pods_v( 'pfat_enable', $pods->pod_data[ 'options' ] ) ) {
					//check if pfat_single and pfat_archive are set
					$single = pods_v( 'pfat_single', $pods->pod_data[ 'options' ], false, true );
					$archive = pods_v( 'pfat_archive', $pods->pod_data[ 'options' ], false, true );

					//build output array
					$the_pods[ $the_pod ] = array(
						'name' => $the_pod,
						'single' => $single,
						'archive' => $archive
					);
				}
			} //endforeach

			//cache the results
			set_transient( 'pods_pfat_auto_pods', $the_pods, PODS_PFAT_TRANSIENT_EXPIRE );
		}

		return $the_pods;

	}

	/**
	 * Outputs templates after the content as needed.
	 *
	 * @param string $content Post content
	 *
	 * @uses 'the_content' filter
	 *
	 * @return string Post content with the template appended if appropriate.
	 *
	 * @since 0.0.1
	 */
	function front( $content ) {

		//get global post object
		global $post;

		//first use other methods in class to build array to search in/ use
		$omegas = $this->auto_pods();

		//get current post's post type
		$current_post_type = get_post_type( $post->ID );

		//check if current post type is a key of the $omegas array ie If its a post type with omega mode enabled.
		if ( isset( $omegas[ $current_post_type ] ) ) {
			//build Pods object for current item
			$pods = pods( $current_post_type, $post->ID );

			//get array for the current post type
			$omega = $omegas[ $current_post_type ];

			//if pfat_single was set try to use that template
			//check if we are on a single post of the post type
			if ( $omega[ 'single' ] && is_singular( $current_post_type ) ) {
				//get the template
				$template = $pods->template( $omega[ 'single' ] );

				//check if we got a valid template
				if ( !is_null( $template ) ) {
					//if so append to the content
					$content = $content . $template;
				}
			}
			//if pfat_archive was set try to use that template
			//check if we are on an archive of the post type
			elseif ( $omega[ 'archive' ] && is_post_type_archive( $current_post_type ) ) {
				//get the template
				$template = $pods->template( $omega[ 'archive' ] );

				//check if we got a valid template
				if ( !is_null( $template ) ) {
					//if so append to the content
					$content = $content . $template;
				}
			}
		}

		return $content;

	}

}