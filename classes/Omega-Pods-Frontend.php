<?php
/**
 * @TODO What this does.
 *
 * @package   @TODO
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

class Omega_Pods_Frontend {


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
		//check if we already have the results cached & use it if we can.
		if ( get_transient( 'pods_omega_the_pods' ) === FALSE && OMEGA_PODS_DEV_MODE === FALSE ) {
			//start the output
			$the_pods = array();
			//get all pods of all post types
			$all_pods = pods_api()->load_pods();
			//loop through all pods adding only the post_type pods to the output
			foreach ( $all_pods as $pod ) {
				if ( $pod['type'] === 'post_type' ) {
					$the_pods[ $pod['name'] ] = $pod['name'];
				}
			}
			//cache the results
			set_transient( 'pods_omega_the_pods', $the_pods, DAY_IN_SECONDS );
		}
		else {
			//use the cached results
			$the_pods = get_transient( 'pods_omega_the_pods' );
		}
		return $the_pods;
	}

	/**
	 * Get all Pods with omega mode enabled
	 *
	 * @return array Info about each Pod with omega mode enabled.
	 *
	 * @since 0.0.1
	 */
	function the_omega_pods( ) {
		//check if we already have the results cached & use it if we can.
		if ( get_transient( 'pods_omega_the_omega_pods' ) === FALSE && OMEGA_PODS_DEV_MODE === FALSE ) {
			$the_pods = $this->the_pods();
			//loop through each to see if omega mode is enabled
			foreach ( $the_pods as $key => $the_pod ) {
				$pods = pods_api( $the_pod );
				//if omega mode is enabled add info about Pod to array
				if ( isset( $pods->pod_data[ 'options' ][ 'omega_enable' ] ) ) {
					if ( $pods->pod_data[ 'options' ][ 'omega_enable' ] == 1 ) {
						//check if omega_single and omega_archive are set
						//if isset add their values to array, else FALSE
						if ( isset( $pods->pod_data[ 'options' ][ 'omega_single' ] ) ) {
							$single = $pods->pod_data[ 'options' ][ 'omega_single' ];

						}
						else {
							$single = FALSE;
						}
						if ( isset( $pods->pod_data[ 'options' ][ 'omega_archive' ] ) ) {
							$archive = $pods->pod_data[ 'options' ][ 'omega_archive' ];
						}
						else {
							$archive = FALSE;
						}
						//set name of pod
						$name = $pods->pod_data[ 'name' ];
						//build output array
						$the_pods[ $name ] = array(
							'name'    => $name,
							'single'  => $single,
							'archive' => $archive,
						);
					} //endif
					else {
						//since omega_enable was set but wasn't enabled, remove from output array
						unset( $the_pods[ $key ] );
					}
				} //if isset
				else {
					//since omega_enable was never set, remove from output array
					$key = key( $the_pod );
					unset( $the_pods[ $key ] );
				}
			} //endforeach
			//cache the results
			set_transient( 'pods_omega_the_omega_pods', $the_pods, DAY_IN_SECONDS );
		}
		else {
			//use the cached results if we can
			$the_pods = get_transient( 'pods_omega_the_omega_pods' );
		}
		return $the_pods;
	}

	/**
	 * Outputs templates after the content as needed.
	 *
	 * @param $content Post content
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
		$omegas = $this->the_omega_pods( );
		//get current post's post type
		$current_post_type = get_post_type( $post->ID );
		//check if current post type is a key of the $omegas array ie If its a post type with omega mode enabled.
		if (array_key_exists( $current_post_type, $omegas ) ) {
			//build Pods object for current item
			$pods = pods( $current_post_type, $post->ID );
			//if omega_single was set try to use that template
			if ( $omega[ 'single' ] !== FALSE ) {
				//check if we are on a single post of the post type
				if ( is_singular( $current_post_type ) ) {
					//get the template
					$temp = $pods->template( $omega[ 'single' ] );
					//check if we got a valid template
					if ( !is_null( $temp ) ) {
						//if so append to the content
						$content = $content . $temp;
					}
				}
			}
			//if omega_archive was set try to use that template
			if ( $omega[ 'archive' ] !== FALSE ) {
				//check if we are on an archive of the post type
				if ( is_post_type_archive( $current_post_type ) ) {
					//get the template
					$temp = $pods->template( $omega[ 'archive' ] );
					//check if we got a valid template
					if ( !is_null( $temp ) ) {
						//if so append to the content
						$content = $content . $temp;
					}
				}
			}
		}


		return $content;
	}





} 