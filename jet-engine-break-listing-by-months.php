<?php
/**
 * Plugin Name: JetEngine - break listing by months
 * Plugin URI:  
 * Description: Separate JetEngine listing by months
 * Version:     1.0.0
 * Author:      Crocoblock
 * Author URI:  https://crocoblock.com/
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

// set meta field to break by, if field is not set will be break by post date
define( 'JET_ENGINE_BREAK_BY_FIELD', false );

define( 'JET_ENGINE_BREAK_BY_QUERY_ID', 'break_months' );

class Jet_Engine_Break_Listing_By_Months {

	public function __construct() {
		add_action( 'jet-engine/listing/before-grid-item', array( $this, 'handle_item' ), 10, 2 );
	}

	public function handle_item( $post, $listing ) {

		if ( empty( $listing->query_vars['request']['query_id'] ) ) {
			return;
		}

		$query = \Jet_Engine\Query_Builder\Manager::instance()->get_query_by_id( $listing->query_vars['request']['query_id'] );

		if ( ! $query ) {
			return;
		}

		if ( ! $query->query_id || JET_ENGINE_BREAK_BY_QUERY_ID !== $query->query_id ) {
			return;
		}

		$index = jet_engine()->listings->data->get_index();

		if ( 0 === $index ) {
			$this->render_month( $post );
		} else {
			
			$items     = $query->get_items();
			$prev_post = $items[ $index - 1 ];

			$prev_time    = $this->get_post_timestamp( $prev_post );
			$current_time = $this->get_post_timestamp( $post );

			if ( $prev_time && $current_time && date_i18n( 'F, Y', $prev_time ) !== date_i18n( 'F, Y', $current_time ) ) {
				$this->render_month( $post );
			}

		}

	}

	public function get_post_timestamp( $post ) {

		if ( JET_ENGINE_BREAK_BY_FIELD ) {
			$date = get_post_meta( $post->ID, JET_ENGINE_BREAK_BY_FIELD, true );
		} else {
			$date = $post->post_date;
		}

		if ( ! $date ) {
			return;
		}

		if ( Jet_Engine_Tools::is_valid_timestamp( $date ) ) {
			return $date;
		} else {
			return strtotime( $date );
		}

	}

	public function render_month( $post ) {

		$timestamp = $this->get_post_timestamp( $post );

		if ( ! $timestamp ) {
			return;
		}

		echo '<h4 class="jet-engine-break-listing" style="width:100%; flex: 0 0 100%;">';
		echo date_i18n( 'F, Y', $timestamp );
		echo '</h4>';

	}

}

new Jet_Engine_Break_Listing_By_Months();
