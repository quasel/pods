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
	 * @todo Figure out how to only get Pods CPTs
	 * @todo What about extended post types?
	 *
	 * @return array All cpts.
	 * @since 0.0.1
	 */
	function the_pods() {
		$the_pods = get_post_types(array(
			'_builtin' => false,
			'public' => true
		));
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
		$the_pods = $this->the_pods();
		//start return array
		$the_omega_pods = array();
		//loop through each to see if omega mode is enabled
		foreach ( $the_pods as $the_pod ) {
			$pods = pods_api( $the_pod );
			//if omega mode is enabled add info about Pod to array
			if ( isset( $pods->pod_data[ 'options' ][ 'omega_enable' ]) ) {
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
					//build output array
					$the_omega_pods[] = array(
						'name'    => $pods->pod_data[ 'name' ],
						'single'  => $single,
						'archive' => $archive,
					);
				} //endif
			} //if isset
		} //endforeach
		return $the_omega_pods;
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
		global $post;
		//set $break to 0 will be used later to break the foreach
		$break = 0;
		//first use other methods in class to build array to loop through
		$omegas = $this->the_omega_pods( );
		//do the loop
		foreach ( $omegas as $omega ) {
				$cpt_name = $omega['name'];
				//check if current item is the post type we are on
				if ( get_post_type( $post->ID ) === $cpt_name ) {
					//set $break to 1 so loop will end after this iteration
					$break = 1;
					//build Pods object for current item
					$pods = pods( $cpt_name, $post->ID );
					//if omega_single was set try to use that template
					if ( $omega[ 'single' ] !== FALSE ) {
						//check if we are on a single post of the post type
						if ( is_singular( $cpt_name ) ) {
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
						if ( is_post_type_archive( $cpt_name ) ) {
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
			//end the loop if we are on the current post type
			if ( $break === 1 ) {
				break;
			}
		}
		return $content;
	}
} 