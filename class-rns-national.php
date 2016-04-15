<?php
/**
 * RNS_National.
 *
 * @package   RNS Classes National
 * @author    David Herrera <david.herrera@religionnews.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Religion News LLC
 */

/**
 * Plugin class.
 *
 * @package RNS Classes National
 * @author  David Herrera <david.herrera@religionnews.com>
 */
class RNS_National {

	protected $version = '1.5.2';

	protected $plugin_slug = 'rns-classes-national';

	protected static $instance = null;

	public $usergroups = array(
		array(
			'slug' => 'ef-usergroup-editors',
			'name' => 'Editors',
			'logins' => array(
				'kevineckstrom',
				'yonatshimron',
				'adellebanks',
				'sallymorrow',
				'marygladstone',
				'religionnews'
			),
		),
		array(
			'slug' => 'ef-usergroup-distributors',
			'name' => 'Distributors',
			'logins' => array(
				'ronribiat',
			),
		),
	);

	/**
	 * Query string slug to the feed for syndicating to hubs lives
	 *
	 * Tough, but not impossible, to guess because the feed is supposed
	 * to be private
	 *
	 * @since  1.1.0
	 *
	 * @var string
	 */
	private $hub_syndication_feed_slug = 'd5e2186e34e1cf33';

	public function __construct() {
		add_action( 'load-post-new.php', array( $this, 'check_on_usergroups' ), 999 );
		add_filter( 'rns_register_campaigns_to_posts', array( $this, 'add_introduction_field_to_campaigns' ) );
		add_action( 'init', array( $this, 'add_hub_syndication_feed' ) );
		add_action( 'pre_option_rss_use_excerpt', array( $this, 'syndicate_full_content_to_hubs' ) );

	    /**
	     * Limit Co-Authors Plus to querying taxonomies, not post authors
	     *
	     * @see  https://github.com/Automattic/Co-Authors-Plus/issues/111. "For performance reasons on large sites"
	     *
	     * @since 1.5.0
	     */
	    // if ( ! is_admin() ) {
	    add_filter( 'coauthors_plus_should_query_post_author', '__return_false' );
		// }

	    add_filter( 'wpseo_title', array( $this, 'wpseo_title' ) );

	    add_filter( 'rns_additional_info_fields', array( $this, 'additional_user_fields' ) );

	    add_action( 'admin_init', array( $this, 'remove_form_button' ), 9999 );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

	// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Gets the value of hub_syndication_feed_slug
	 *
	 * @return string
	 */
	public function get_hub_syndication_feed_slug() {
		return $this->hub_syndication_feed_slug;
	}

	/**
	 * Check whether a User Group exists
	 *
	 * @param string $slug The slug of the User Group to check
	 * @return bool
	 */
	public function usergroup_is_missing( $slug ) {
		global $edit_flow;
		$result = $edit_flow->user_groups->get_usergroup_by( 'slug', $slug );
		if ( ! $result || is_wp_error( $result ) )
			return true;

		return false;
	}

	/**
	 * Check whether a User Group has members
	 *
	 * @param string $slug The slug of the User Group to check
	 * @return bool
	 */
	public function usergroup_is_empty( $slug ) {
		global $edit_flow;
		$group = $edit_flow->user_groups->get_usergroup_by( 'slug', $slug );
		$members = $group->user_ids;
		if ( count( $members ) === 0 )
			return true;

		return false;
	}

	/**
	 * Wrapper for adding users to a User Group given the users and group
	 *
	 * @param array $group The slug of the User Group and an array of logins to add to it
	 * @return bool True on success, false on failure
	 */
	public function repopulate_usergroup( $group ) {
		global $edit_flow;
		$usergroup = $edit_flow->user_groups->get_usergroup_by( 'slug', $group['slug'] );
		$updated = $edit_flow->user_groups->add_users_to_usergroup( $group['logins'], $usergroup->term_id );

		return $updated;
	}

	/**
	 * Check that the core RNS User Groups exist, with the correct members
	 *
	 * @param array $groups An array of User Groups to check on.
	 *                      The array should contain an array for each group
	 *                      with keys for name, slug, and logins to add
	 * @return bool|WP_Error True on success, WP_Error on failure
	 */
	public function check_on_usergroups() {
		global $edit_flow;

		if ( ! $edit_flow ) return true;

		$groups = $this->usergroups;

		foreach ( $groups as $group ) {
			if ( $this->usergroup_is_missing( $group['slug'] ) ) {
				$args = array(
					'slug' => $group['slug'],
					'name' => $group['name']
				);
				$regenerated = $edit_flow->user_groups->add_usergroup( $args );
				if ( ! $regenerated ) {
					return new WP_Error( 'error', __( 'Error regenerating usergroup', $this->plugin_slug ) );
				} else {
					$repopulated = $this->repopulate_usergroup( $group );
					if ( ! $repopulated ) {
						return new WP_Error( 'error', __( 'Error repopulating usergroup', $this->plugin_slug ) );
					}
				}
			} elseif ( $this->usergroup_is_empty( $group['slug'] ) ) {
				$repopulated = $this->repopulate_usergroup( $group );
				if ( ! $repopulated ) {
					return new WP_Error( 'error', __( 'Error repopulating usergroup', $this->plugin_slug ) );
				}
			}
		}

		return true;
	}

	/**
	 * Add the 'Introduction' textarea to Posts-to-Campaigns connections
	 *
	 * @since  1.0.0
	 *
	 * @param array $args Arguments for creating the basic connection passed to p2p_register_connection_type()
	 * @return array The amended arguments
	 */
	public function add_introduction_field_to_campaigns( $args ) {
		$args['fields'] = array(
			'intro' => array(
				'title' => 'Introduction (optional, italicized)',
				'type' => 'textarea',
				'extra' => array(
					'rows' => '10',
					'cols' => '30'
				),
			)
		);
		return $args;
	}


	/**
	 * Add an RSS feed at http://example.com?feed=[slug]
	 *
	 * This feed is for syndicating RNS content to the hubs
	 *
	 * @since  1.1.0
	 */
	public function add_hub_syndication_feed() {
		add_feed( $this->get_hub_syndication_feed_slug(), array( $this, 'make_hub_syndication_feed' ) );
	}

	/**
	 * Run the hubs' feed query through the RSS2 template
	 *
	 * @since  1.1.0
	 */
	public function make_hub_syndication_feed() {
		load_template( ABSPATH . WPINC . '/feed-rss2.php' );
		wp_reset_query();
	}

	/**
	 * Allow the hubs' feed to contain the full text of stories
	 *
	 * @since  1.1.0
	 *
	 * @param string $option The option in the database
	 * @return string The updated option
	 */
	public function syndicate_full_content_to_hubs( $option ) {
		if ( is_feed( $this->get_hub_syndication_feed_slug() ) )
			$option = '0';

		return $option;
	}

	/**
	 * Display the name of an author when viewing their Author taxonomy archive
	 *
	 * Gigaom's CAP plugin generates a real taxonomy query when viewing an
	 * author archive, which means the default Yoast author settings do not
	 * apply. This adds the author name to the front of the title if CAP is
	 * active, the page is an Author term archive, and a user for the queried
	 * object is found
	 *
	 * @since 1.5.0
	 *
	 * @param  string $title The <title> tag to use after all of Yoast's processing
	 * @return string        The title, modified if needed
	 */
	public function wpseo_title( $title ) {
		global $coauthors_plus;

		if ( $coauthors_plus && is_tax( $coauthors_plus->coauthor_taxonomy ) ) {
			$queried = get_queried_object();
			$author = get_user_by( 'login', $queried->name );
			if ( $author ) {
				$title = $author->display_name . $title;
			}
		}

		return $title;
	}

	/**
	 * Add RNS-specific custom fields to a user's profile
	 *
	 * @since 1.4.0
	 *
	 * @return array The updated list of custom fields
	 */
	public function additional_user_fields( $fields ) {
		array_unshift( $fields, array(
			'name' => 'rns_position',
			'label' => 'Position',
			'description' => 'Use Title Case',
			'type' => 'text',
			'edit_on_profile' => true
		));

		array_unshift( $fields, array(
			'name' => 'rns_location',
			'label' => 'Location',
			'type' => 'text',
			'edit_on_profile' => true
		));

		return $fields;
	}

	/**
	 * Disable the "Add Form" button on edit pages.
	 *
	 * Prevents some slow queries from running.
	 *
	 * @since 1.5.2
	 */
	public function remove_form_button() {
		global $pagenow;
		// See GFForms::init() for this array of pages.
		$target_pages = array( 'post.php', 'page.php', 'page-new.php', 'post-new.php' );
		if ( $pagenow && in_array( $pagenow, $target_pages ) ) {
		    remove_action('admin_footer',  array('RGForms', 'add_mce_popup'));
		    remove_action('media_buttons', array('RGForms', 'add_form_button'), 20);
		}
	}

}
