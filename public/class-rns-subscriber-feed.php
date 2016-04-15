<?php

namespace RNS;
/**
 * An RSS feed available to subscribers.
 */
class Subscriber_Feed {

	/**
	 * Instance of this class.
	 *
	 * @since 1.6.0
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Slug of the subscriber-content RSS feed.
	 * 
	 * @since  1.6.0
	 * @var  string
	 */
	private $rss_slug = 'subscribers';
	
	/**
	 * Initialize the class.
	 *
	 * @since 1.6.0
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'add_subscribers_feed' ) );
		add_filter( 'posts_where' , array( $this, 'exclude_exclusive_posts_from_subscriber_feed' ) );
		add_action( 'pre_option_rss_use_excerpt', array( $this, 'syndicate_full_content_to_subscribers' ) );
	}
	
	/**
	 * Return an instance of this class.
	 * 
	 * @since 1.6.0
	 * @return object
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Create a feed at `?feed=[rss_slug]`
	 */
	public function add_subscribers_feed() {
		add_feed( $this->rss_slug, array( $this, 'make_subscribers_feed' ) );
	}

	/**
	 * Exclude exclusive posts from the subscribers' RSS feed
	 *
	 * Hooks into `posts_where`, which provides SQL querying
	 * capability not easily available in `pre_get_posts`
	 *
	 * @see  http://wordpress.stackexchange.com/a/6447
	 * @param string $where The existing `posts_where` clause
	 * @return string The possibly amended `posts_where` clause
	 */
	public function exclude_exclusive_posts_from_subscriber_feed( $where ) {
		if ( is_feed( $this->rss_slug ) && is_main_query() ) {
			global $wpdb;
			$where .= " AND $wpdb->posts.ID NOT IN ( SELECT DISTINCT post_id FROM $wpdb->postmeta WHERE meta_key = '_rns_not_for_republication' AND meta_value > '' )";
		}
		return $where;
	}

	/**
	 * Run the subscribers' feed query through the RSS2 template
	 */
	public function make_subscribers_feed() {
		load_template( ABSPATH . WPINC . '/feed-rss2.php' );
		wp_reset_query();
	}

	/**
	 * Allow the Subscribers feed to contain the full text of stories
	 *
	 * @since  1.1.0
	 *
	 * @param string $option The option in the database
	 * @return string The updated option
	 */
	public function syndicate_full_content_to_subscribers( $option ) {
		if ( is_feed( $this->rss_slug ) ) {
			$option = '0';
		}

		return $option;
	}

}
