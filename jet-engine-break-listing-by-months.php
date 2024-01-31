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

class Jet_Engine_Break_Listing_By_Months {

	public function __construct() {

		add_action( 'init', array( $this, 'setup' ) );
		add_action( 'jet-engine/listing/before-grid-item', array( $this, 'handle_item' ), 10, 2 );
		add_filter( 'jet-engine-break-month/prev-post', array( $this, 'posts_query_prev_post' ), 10, 3 );
		add_filter( 'jet-engine-break-month/render-first', array( $this, 'render_first' ), 10 );

	}

	/**
	 * These constants could be defined from functions.php file of your active theme
	 * @return [type] [description]
	 */
	public function setup() {

		if ( ! defined( 'JET_ENGINE_BREAK_BY_FIELD' ) ) {
			// set meta field to break by, if field is not set will be break by post date
			define( 'JET_ENGINE_BREAK_BY_FIELD', false );
		}

		if ( ! defined( 'JET_ENGINE_BREAK_BY_PROP' ) ) {
			// set object property to get date from. Set this to use with non-posts queries
			define( 'JET_ENGINE_BREAK_BY_PROP', false );
		}
		
		if ( ! defined( 'JET_ENGINE_BREAK_BY_QUERY_ID' ) ) {
			// set query ID to break by. Same query ID need to be set also for Listing and filter wisgets if you using this in combination with JSF
			define( 'JET_ENGINE_BREAK_BY_QUERY_ID', 'break_months' );
		}

		if ( ! defined( 'JET_ENGINE_BREAK_MONTH_OPEN_HTML' ) ) {
			// set opening html tag(s) for month name
			define( 'JET_ENGINE_BREAK_MONTH_OPEN_HTML', '<h4 class="jet-engine-break-listing" style="width:100%; flex: 0 0 100%; grid-column: 1 / -1;">' );
		}

		if ( ! defined( 'JET_ENGINE_BREAK_MONTH_CLOSE_HTML' ) ) {
			// set closing html tag(s) for month name
			define( 'JET_ENGINE_BREAK_MONTH_CLOSE_HTML', '</h4>' );
		}

		if ( ! defined( 'JET_ENGINE_BREAK_MONTH_FORMAT' ) ) {
			// set format of the month to show
			define( 'JET_ENGINE_BREAK_MONTH_FORMAT', 'F, Y' );
		}

		if ( ! defined( 'JET_ENGINE_BREAK_MONTH_COMPARE_FORMAT' ) ) {
			// Set date format to compare dates to break.
			// Could be used to change break type. For example, default format 'F, Y' - is for months
			// Others variants 'Y' - break by years, 'd, F, Y' - break by days
			define( 'JET_ENGINE_BREAK_MONTH_COMPARE_FORMAT', 'F, Y' );
		}

	}

	public function posts_query_prev_post( $post, $query, $listing ) {

		if ( $post || $query->query_type !== 'posts' ) {
			return $post;
		}
		
		$page = $query->get_current_items_page();
		
		$args = $query->get_query_args();
		
		$args['paged'] = $page - 1;
		
		$posts_query = new \WP_Query( $args );
		
		$posts = $posts_query->get_posts();

		$post = $posts[ array_key_last( $posts ) ] ?? null;
		
		return $post;

	}

	public function render_first( $render ) {

		//do not render first header on JetEngine Load More
		if ( ! empty( $_REQUEST['handler'] ) && $_REQUEST['handler'] === 'listing_load_more' ) {
			return false;
		}

		//do not render first header on JetSmartFilters Load More pagination
		if ( ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] === 'jet_smart_filters' && ! empty( $_REQUEST['props']['pages'] ) ) {
			return false;
		}

		return $render;

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

		if ( apply_filters( 'jet-engine-break-month/render-first', 0 === $index ) ) {
			$this->render_month( $post );
		} else {
			
			$items     = $query->get_items();
			$prev_post = apply_filters( 'jet-engine-break-month/prev-post', $items[ $index - 1 ] ?? null, $query, $listing );

			$prev_time    = $this->get_post_timestamp( $prev_post );
			$current_time = $this->get_post_timestamp( $post );

			if ( $prev_time
				&& $current_time
				&& date_i18n( JET_ENGINE_BREAK_MONTH_COMPARE_FORMAT, $prev_time ) !== date_i18n( JET_ENGINE_BREAK_MONTH_COMPARE_FORMAT, $current_time ) 
			) {
				$this->render_month( $post );
			}

		}

	}

	public function get_post_timestamp( $post ) {

		if ( JET_ENGINE_BREAK_BY_FIELD ) {
			$date = get_post_meta( $post->ID, JET_ENGINE_BREAK_BY_FIELD, true );
		} elseif ( JET_ENGINE_BREAK_BY_PROP ) {
			$date = isset( $post->{JET_ENGINE_BREAK_BY_PROP} ) ? $post->{JET_ENGINE_BREAK_BY_PROP} : false;
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

		echo JET_ENGINE_BREAK_MONTH_OPEN_HTML;
		echo date_i18n( JET_ENGINE_BREAK_MONTH_FORMAT, $timestamp );
		echo JET_ENGINE_BREAK_MONTH_CLOSE_HTML;

	}

}

new Jet_Engine_Break_Listing_By_Months();
