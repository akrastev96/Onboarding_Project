<?php
/**
 * The plugin functions file.
 *
 * This is used to define general functions, shortcodes etc.
 *
 * Important: Always use the `dx_` prefix for function names.
 *
 * @link       https://devrix.com
 * @since      1.0.0
 *
 * @package    Students
 * @subpackage Students/includes
 * @author     DevriX <contact@devrix.com>
 */

if ( ! function_exists( 'dx_get_image_alt' ) ) {
	/**
	 * Gets the alt text of an image.
	 *
	 * @param string $url The URL of the image.
	 * @param string $default A default alt to use if the image does not have one set.
	 */
	function dx_get_image_alt( $url, $default = '' ) {
		$image_id      = attachment_url_to_postid( $url );
		$alt_from_meta = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
		$image_alt     = $default;

		if ( 0 !== $image_id && ! empty( $alt_from_meta ) ) {
			$image_alt = $alt_from_meta;
		}

		return $image_alt;
	}
}
