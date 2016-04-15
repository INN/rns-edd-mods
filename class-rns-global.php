<?php
/**
 * RNS_Global.
 *
 * @package   RNS Classes Global
 * @author    David Herrera <david.herrera@religionnews.com>
 * @license   GPL-2.0+
 * @copyright 2013 Religion News LLC
 */

/**
 * Plugin class.
 *
 * @package RNS Classes Global
 * @author  David Herrera <david.herrera@religionnews.com>
 */
class RNS_Global {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	protected $version = '1.9.1';

	/**
	 * Unique identifier for your plugin.
	 *
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'rns_global';

	/**
	 * Getter for $plugin_slug
	 *
	 * @since 1.5.2
	 *
	 * @return string
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

  /**
   * Plugin prefix for, e.g., IDs
   * @since  1.0.0
   * @var  string
   */
  protected $prefix = 'rns_';

  /**
   * Plugin prefix for custom fields
   * @since  1.0.0
   * @var  string
   */
  public $meta_prefix = '_rns_';

	/**
	 * The ID of the national site in the production network
	 *
	 * @since  1.1.0
	 *
	 * @var string
	 */
	private $national_blog_id = '1';

	/**
	 * Getter for national_blog_id
	 *
	 * @since  1.1.0
	 *
	 * @return string
	 */
	public function get_national_blog_id() {
		return $this->national_blog_id;
	}

  /**
   * IDs of hubs in the production network
   * @since 1.0.0
   * @var array
   */
  public $hub_ids = array( 14, 17, 19, 21, 26 );

  /**
   * Slug for Additional Comment Notifications functionality
   * @since  1.1.0
   * @var  string
   */
  public $acn_slug = 'acn';

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0.0
	 */
	public function __construct() {

    // Prefix slugs
    $this->acn_slug = $this->prefix . $this->acn_slug;

		// Load plugin text domain
		// add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Add the options page and menu items.
		// add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Load admin style sheet and JavaScript.
		// add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		// add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Load public-facing style sheet and JavaScript.
		// add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		// add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Define custom functionality. Read more about actions and filters: http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		// add_action( 'TODO', array( $this, 'action_method_name' ) );
		// add_filter( 'TODO', array( $this, 'filter_method_name' ) );

    /* Content oversight */
    //add_filter( 'cmb_meta_boxes', array( $this, 'content_oversight_metabox' ) );
    //add_filter( 'the_content', array( $this, 'content_oversight_messages' ), 999 );
    //add_filter( 'the_excerpt', array( $this, 'content_oversight_messages' ), 999 );

    /* Post utilities */
   // add_filter( 'cmb_meta_boxes', array( $this, 'post_utilities_metabox' ) );

    /* Comments with many links */
    //add_filter( 'comment_form_field_comment', array( $this, 'comments_with_many_links' ), 30 );

    /**
     * Per Debra, links in comments should not be clickable
     *
     * @see  wp-includes/default-filters.php
     * @see  make_clickable() in wp-includes/formatting.php
     */
    //remove_filter( 'comment_text', 'make_clickable', 9 );

    /* Search-by-author */
    // add_filter( 'posts_search', array( $this, 'db_filter_authors_search' ) );

    /* Condense "My Sites" list for super admins */
    //add_action( 'admin_footer', array( $this, 'condense_my_sites_list' ) );

    /* Distribute authors to hubs */
    //add_action( 'transition_post_status', array( $this, 'add_authors_to_hubs' ), 90, 3 );

    /* Monitor users added to blogs */
    // add_action( 'add_user_to_blog', array( $this, 'add_user_to_blog_alert' ), 100, 3 );

    /* CC editors or administrators on new-comment notifications */
    //add_action( 'admin_init', array( $this, 'acn_init' ) );
    //add_filter( 'comment_notification_headers', array( $this, 'filter_comment_notification_headers' ) );

    /* Register P2P connection for Campaigns */
    //add_action( 'p2p_init', array( $this, 'p2p_register_campaigns_to_posts' ) );

    /* Disable Guest Author functionality */
    //add_filter( 'coauthors_guest_authors_enabled', '__return_false' );

    /* Remove WordPress SEO fields from user profiles */
    //add_action( 'show_user_profile', array( $this, 'remove_wpseo_profile_fields' ), 1 );
    //add_action( 'edit_user_profile', array( $this, 'remove_wpseo_profile_fields' ), 1 );

    /* Tweetable "via" handle */
    //add_filter( 'option_tweetable', array( $this, 'tweetable_username_fallback' ), 30 );

    /* Additional WordPress SEO tags */
    //add_action( 'wpseo_head', array( $this, 'pinterest_article_author_tag' ), 100 );

    //	add_action( 'admin_footer', array( $this, 'hide_category_picker' ) );

    //	add_action( 'pre_option_akismet_discard_month', array( $this, 'akismet_discard_month' ) );

	//	add_filter( 'user_contactmethods', array( $this, 'contactmethods' ), 20 );

	//	add_action( 'admin_init', array( $this, 'additional_info_metabox' ) );
	//	add_filter( 'rns_additional_info_fields', array( $this, 'long_description_field' ) );

	//	add_action( 'admin_init', array( $this, 'primary_blog_id_metabox' ) );

	//	add_filter( 'rns_wpseo_location_archive_title', array( $this, 'wpseo_location_archive_title' ), 30, 2 );

		/* Do not send notifications of comments awaiting approval */
	//	add_filter( 'pre_option_moderation_notify', '__return_zero' );

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
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == $this->plugin_screen_hook_suffix ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'css/admin.css', __FILE__ ), array(), $this->version );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == $this->plugin_screen_hook_suffix ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), $this->version );
		}

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'css/public.css', __FILE__ ), array(), $this->version );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'js/public.js', __FILE__ ), array( 'jquery' ), $this->version );
	}

	/**
	 * Register the administration menu for this plugin
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		$progeny = get_option( 'rns_progeny_options' );
		add_menu_page(
		  __( 'Religion News LLC', $this->plugin_slug ),
		  __( 'Religion News LLC', $this->plugin_slug ),
		  'manage_options',
		  $this->plugin_slug,
		  array( $this, 'display_plugin_admin_page' ),
		  $progeny['url_to_theme'] . '/favicon.ico'
		);
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Wrapper to add a set of capabilities to a role
	 *
	 * This method does not ensure that a role has the default WordPress
	 * capabilities or that it does not have more capabilities than it should.
	 * It only ensures that a role has minimum set of capabilities
	 *
	 * @since  1.3.0
	 *
	 * @param array $caps Capabilities to add
	 * @param string $target_role The role to add them to
	 *
	 * @return  If $caps is not an array or if the role was not found
	 */
	public function set_caps( $caps, $target_role ) {
	    if ( ! is_array( $caps ) ) {
	    	return;
	    }

	    $role = get_role( $target_role );
	    if ( ! $role ) {
	    	return;
	    }

	    foreach( $caps as $cap ) {
	      $role->add_cap( $cap );
	    }
	}

    /**
     * Expose meta fields for emergency editing functions
     *
     * Shows fields only to users with delete_others_posts capabilities, which
     * is not granted to Authors by default
     *
     * @since  1.0.0
     *
     * @param array $meta_boxes The existing CMB metaboxes
     * @return array The updated array of metaboxes
     */
	public function content_oversight_metabox( array $meta_boxes ) {
		if ( ! current_user_can( 'delete_others_posts' ) ) {
			return $meta_boxes;
		}

		$meta_boxes[] = array(
			'id'         => $this->prefix . 'content_oversight_metabox',
			'title'      => 'Content Oversight',
			'pages'      => array( 'post' ), // Post type
			'context'    => 'normal',
			'priority'   => 'low',
			'show_names' => true, // Show field names on the left
			'fields'     => array(
				array(
					'name' => 'Comments Hidden',
					'desc' => '',
					'id'   => $this->meta_prefix . 'comments_hidden',
					'type' => 'checkbox',
					),
				array(
					'name' => 'Post Under Review',
					'desc' => '',
					'id' => $this->meta_prefix . 'under_review',
					'type' => 'checkbox',
					),
				array(
					'name' => 'Post Taken Down',
					'desc' => '',
					'id' => $this->meta_prefix . 'taken_down',
					'type' => 'checkbox'
					),
				array(
					'name' => "Editor's Note",
					'desc' => 'Enter a message to display in place of "taken down" or "under review" post content',
					'id' => $this->meta_prefix . 'oversight_editors_note',
					'type' => 'textarea_small'
					),
				),
			);
		return $meta_boxes;
	}

  /**
   * Is 'Comments Hidden' active on this post?
   *
   * @return bool
   */
  public function comments_are_hidden() {
    return 'on' === get_post_meta( get_the_ID(), '_rns_comments_hidden', true );
  }

  /**
   * Is 'Post Under Review' active on this post?
   *
   * @return  bool
   */
  public function post_is_under_review() {
    return 'on' === get_post_meta( get_the_ID(), '_rns_under_review', true );
  }

  /**
   * Is 'Post Taken Down' active on this post?
   *
   * @return  bool
   */
  public function post_is_taken_down() {
    return 'on' === get_post_meta( get_the_ID(), '_rns_taken_down', true );
  }

  /**
   * Get the "Editor's Note" meta field from the Content Oversight box
   *
   * @return string|null The content, or nothing
   */
  public function content_oversight_editors_note() {
    return get_post_meta( get_the_ID(), '_rns_oversight_editors_note', true );
  }

  /**
   * Filter the_content() or the_excerpt() for oversight functions
   *
   * If content has been marked as 'under review' or 'taken down,'
   * replaces the content or excerpt with the accompanying editor's
   * note, if one exists, or with a generic message
   *
   * @param string $content The default text
   * @return string The default or amended text
   */
  public function content_oversight_messages( $content ) {
    $note = $this->content_oversight_editors_note();

    if ( $this->post_is_under_review() )
      $content = sprintf( '<p><em>%s</em></p>', $note ? $note : 'This post is under review by the editors.' );
    if ( $this->post_is_taken_down() )
      $content = sprintf( '<p><em>%s</em></p>', $note ? $note : 'This post has been taken down by the editors.' );

    return $content;
  }

  /**
   * Create a metabox with utilities available to all sites and blogs
   *
   * @param array $meta_boxes Existing metaboxes registered with CMB
   * @return array The metaboxes array with the Utilties, or the original array if all the fields are filtered out
   */
  public function post_utilities_metabox( array $meta_boxes ) {
    $fields = array();
    $fields[] = array(
      'name' => 'Twitter Headline',
      'desc' => 'Enter a custom headline to use with the Tweet button in the sharebar. #hashtags and @handles work.',
      'id' => $this->meta_prefix . 'twitter_headline',
      'type' => 'text'
    );
    $fields[] = array(
      'name' => 'Story Hashtag (include "#")',
      'desc' => 'Suggest a hashtag to use for discussing this story.',
      'id'   => $this->meta_prefix . 'twitter_hashtag',
      'type' => 'text',
    );
    $fields = apply_filters( 'rns_post_utilities_fields', $fields );

    if ( empty( $fields ) )
      return $meta_boxes;

    $meta_boxes[] = array(
      'id'         => 'rns_post_utilities',
      'title'      => 'Utilities',
      'pages'      => array( 'post' ), // Post type
      'context'    => 'normal',
      'priority'   => 'default',
      'show_names' => true, // Show field names on the left
      'fields'     => $fields,
    );

    return $meta_boxes;
  }

  /**
   * Add a 'many comments' warning after the 'Comment' form field
   *
   * @param string $field The 'Comment' field HTML
   * @return string The field HTML with the extra paragraph
   */
  public function comments_with_many_links( $field ) {
    return $field . sprintf( '<p>%s</p>', 'Comments with many links may be automatically held for moderation.' );
  }

  /**
   * Include posts from authors in the search results where
   * either their display name or user login matches the query string
   *
   * @author danielbachhuber
   * @see http://danielbachhuber.com/2012/02/07/include-posts-by-matching-authors-in-your-search-results/
   */
  function db_filter_authors_search( $posts_search ) {
    // Don't modify the query at all if we're not on the search template
    // or if the LIKE is empty
    if ( !is_search() || empty( $posts_search ) )
      return $posts_search;

    global $wpdb;
    // Get all of the users of the blog and see if the search query matches either
    // the display name or the user login
    add_filter( 'pre_user_query', array( $this, 'db_filter_user_query' ) );
    $search = sanitize_text_field( get_query_var( 's' ) );
    $args = array(
      'count_total' => false,
      'search' => sprintf( '*%s*', $search ),
      'search_fields' => array(
        'display_name',
        'user_login',
        ),
      'fields' => 'ID',
      );
    $matching_users = get_users( $args );
    remove_filter( 'pre_user_query', array( $this, 'db_filter_user_query' ) );
    // Don't modify the query if there aren't any matching users
    if ( empty( $matching_users ) )
      return $posts_search;
    // Take a slightly different approach than core where we want all of the posts from these authors
    $posts_search = str_replace( ')))', ")) OR ( {$wpdb->posts}.post_author IN (" . implode( ',', array_map( 'absint', $matching_users ) ) . ')))', $posts_search );
    error_log( $posts_search );
    return $posts_search;
  }

  /**
   * Modify get_users() to search display_name instead of user_nicename
   */
  function db_filter_user_query( &$user_query ) {
    if ( is_object( $user_query ) )
      $user_query->query_where = str_replace( 'user_nicename LIKE', 'display_name LIKE', $user_query->query_where );
    return $user_query;
  }

  /**
   * Reduce the height of each item under "My Sites" for Super Admins
   */
  public function condense_my_sites_list() {
    if ( current_user_can( 'manage_network' ) ) {
      ?>
      <style>
        #wp-admin-bar-my-sites-list > .menupop .ab-item {
          font-size: 12px !important;
          line-height: 20px !important;
          height: 20px !important;
        }
        #wpadminbar .quicklinks li div.blavatar {
          background: none;
          height: 0;
          width: 0;
          margin: 0;
          /* Icons in MP6 */
          font-size: 0px !important;
        }
      </style>
      <?php
    }
  }

  /**
   * Adds authors of articles to the hubs
   *
   * @param string $new_status
   * @param string $old_status
   * @param object $post Post data
   */
  public function add_authors_to_hubs( $new_status, $old_status, $post ) {

    /* Ensure this post is being published and not updated */
    if ( 'publish' != $new_status || 'publish' == $old_status )
      return;

    if ( ! function_exists( 'get_coauthors' ) ) {
      return;
    }

    /* Get the authors */
    $coauthors = get_coauthors( $post->ID );
    if ( ! $coauthors )
      return;

    foreach ( $coauthors as $author ) {
      $authors[] = $author->ID;
    }

    /* Add the authors to each hub if they're missing */
    foreach ( $this->hub_ids as $hub ) {
      foreach ( $authors as $id ) {
        if(function_exists('is_user_member_of_blog') && function_exists('add_user_to_blog')){
            if ( ! is_user_member_of_blog( $id, $hub ) )
              add_user_to_blog( $hub, $id, 'contributor' );
          }
      }
    }

  }

  /**
   * Alert Dave when a user is added to a blog
   *
   * Part of debugging changing primary blog IDs
   *
   * @since 1.6.0
   *
   * @return void
   * @author David Herrera
   */
  public function add_user_to_blog_alert( $user_id, $role, $blog_id ) {
  	$to = 'herrera.dl@gmail.com';
  	$subject = 'add_user_to_blog() alert';
  	$primary_blog_id_now = get_user_meta( $user_id, 'primary_blog', true );
  	$message = "User ID {$user_id} added to blog ID {$blog_id}. User's primary blog now {$primary_blog_id_now}. Edit: http://www.religionnews.com/wp-admin/network/user-edit.php?user_id={$user_id}";
  	wp_mail( $to, $subject, $message );
  }

  /**
   * Register the settings and fields for Additional Comment Notifications
   *
   * @since  1.1.0
   */
  public function acn_init() {
    register_setting(
      $option_group = $this->plugin_slug,
      $option_name = $this->acn_slug,
      $sanitization_callback = array( $this, 'acn_address_validation' )
    );

    add_settings_section(
      $section_id = $this->acn_slug,
      $section_title = 'Additional Comment Notifications',
      '',
      $section_page = $this->plugin_slug
    );

    add_settings_field(
      $field_slug = $this->acn_slug . '_addresses',
      $field_title = 'Email addresses',
      array( $this, 'rns_acn_address_input' ),
      $field_page = $this->plugin_slug,
      $field_section = $this->acn_slug
    );
  }

  /**
   * Display the input field for ACN email addresses
   *
   * @since  1.1.0
   *
   * @todo Move this to the Global settings API
   */
  public function rns_acn_address_input() {
    $options = get_option( $this->acn_slug );
    $value = $options['addresses'];
    echo "<input id='addresses' name='{$this->acn_slug}[addresses]' type='text' size='50' value='{$value}' />";
  }

  /**
   * Validate ACN options
   *
   * @since  1.1.0
   *
   * @param array $input The values submitted on the options page
   * @return array The values, if any, after validation
   */
  public function acn_address_validation( $input ) {

    $valid = array();

    /**
     * Strip whitespace from addresses before comma-delimited explosion
     *
     * @link  http://stackoverflow.com/a/1279798
     */
    $input['addresses'] = preg_replace( '/\s+/', '', $input['addresses'] );
    $emails = explode( ',', $input['addresses'] );
    foreach( $emails as $email ) {
      if ( is_email( $email ) ) {
        $valid_addresses[] = $email;
      } else {
        add_settings_error(
          $setting = $this->acn_slug . '_address',
          $code = $this->acn_slug . '_address_error',
          $message = 'Error: ' . $email . ' is not a valid email address',
          $type = 'error'
        );
      }
    }
    if ( ! empty( $valid_addresses ) )
      $valid['addresses'] = implode( ', ', $valid_addresses );

    return $valid;
  }

  /**
   * Get the addresses to CC on comment notifications
   *
   * Contains several checks to ensure the options and addresses exist
   *
   * @since  1.1.0
   *
   * @return string|bool The addresses from the options field, or false if none exist
   */
  public function get_acn_recipients() {
    $options = get_option( $this->acn_slug );

    if ( ! $options )
      return false;
    if ( ! array_key_exists( 'addresses', $options ) )
      return false;
    if ( ! $addresses = $options['addresses'] )
      return false;

    return apply_filters( 'get_acn_recipients', $addresses );
  }

  /**
   * CC email addresses specified by ACN to new-comment notifications
   *
   * @since  1.1.0
   *
   * @link  http://skyphe.org/2011/02/24/how-to-add-additional-e-mail-addresses-to-wordpress-comment-notifications/
   *
   * @param string $message_headers The existing headers to the notification email
   * @return string The headers, possibly with the CC line
   */
  public function filter_comment_notification_headers( $message_headers ) {

    $acn_recipients = apply_filters( 'acn_recipients', $this->get_acn_recipients() );

    if ( $acn_recipients )
      $message_headers .= "\n" . 'CC: ' . $acn_recipients;

    return $message_headers;
  }

  /**
   * Add a Posts-to-Campaigns connection on all sites
   *
   * But only if the Campaign post type is available
   *
   * @since  1.0.0
   */
	public function p2p_register_campaigns_to_posts() {
		if ( post_type_exists( 'rns_campaign' ) ) {
		    $args = array(
		      'name' => 'rns_campaigns',
		      'from' => array( 'post', 'rns_network_post' ),
		      'to' => 'rns_campaign',
		      'admin_box' => array(
		        'show' => 'to',
		        'context' => 'advanced'
		      ),
		      'sortable' => true,
		    );
		    $args = apply_filters( 'rns_register_campaigns_to_posts', $args );
		    p2p_register_connection_type( $args );
		}
  }

  /**
   * Extract a Twitter handle from a URL stored in RNS Progeny
   *
   * @since  1.1.0
   *
   * @param string $handle The original handle passed by the sharethis.php template
   * @return The new Twitter handle, or the original handle if the extraction fails
   */
  function get_twitter_handle_from_url( $handle ) {
    $progeny = get_option( 'rns_progeny_options' );
    if ( ! $progeny || ! array_key_exists( 'twitter_url', $progeny ) )
      return $handle;

    $twitter = substr( strrchr( untrailingslashit( $progeny['twitter_url'] ), '/' ), 1 );
    if ( ! $twitter )
      return $handle;

    return $twitter;
  }

  /**
   * Hide the SEO inputs on the user profile page
   *
   * @link http://wordpress.org/support/topic/plugin-wordpress-seo-by-yoast-remove-wordpress-seo-settings-area-from-user-profile-page#post-4253468
   */
  public function remove_wpseo_profile_fields() {
    global $wpseo_admin;
    remove_action( 'show_user_profile', array( $wpseo_admin, 'user_profile' ) );
    remove_action( 'edit_user_profile', array( $wpseo_admin, 'user_profile' ) );
  }

  /**
   * Fall back to the Twitter handle from Progeny as Tweetable "via" text
   *
   * Specifying "via" in the shortcode still overrides this
   *
   * @uses  $this->get_twitter_handle_from_url() To try to get the handle from Progeny if the Tweetable option is empty
   *
   * @param array $option The 'tweetable' options value
   * @return array
   */
  public function tweetable_username_fallback( $option ) {
    if ( empty( $option['username'] ) ) {
      $handle = $this->get_twitter_handle_from_url( '' );
      if ( $handle )
        $option['username'] = $handle;
    }
    return $option;
  }

  /**
   * Output the author names with other Open Graph data
   *
   * @see  WPSEO_OpenGraph::article_author_facebook()
   *
   * @return If this is not a single post or if the author has a
   *                 Facebook URL, so as to not override WordPress SEO
   */
  public function pinterest_article_author_tag() {
    if ( ! is_singular() )
      return;

    global $post;

    if ( get_the_author_meta( 'facebook', $post->post_author ) )
      return;

    if ( function_exists( 'coauthors' ) ) {
      $author = coauthors( null, null, null, null, false );
    } else {
      $author = get_the_author();
    }

    $author = apply_filters( 'rns_pinterest_article_author', $author );

    if ( is_string( $author ) && $author !== '' )
      echo '<meta property="article:author" content="' . esc_attr( $author ) . "\"/>\n";
  }


  	/**
  	 * Hide the "Add New [term]" box from almost everyone
  	 *
  	 * This hack allows us to add the manage_categories cap to
  	 * Editors without also exposing them to the option of creating
  	 * new categories from the post-edit screen
  	 *
  	 * @since 1.2.0
  	 *
  	 * @return null If the current user is a Network Manager
  	 */
	public function hide_category_picker() {
		if ( current_user_can( 'manage_network' ) )
			return;

		$screen = get_current_screen();
		if ( 'post' == $screen->id )
			echo '<script id="rns-hide-category-picker">jQuery(".wp-hidden-children").hide();</script>';
	}

	/**
	 * Have Akismet always delete spam submitted on posts more than a month old
	 *
	 * @see get_option()
	 *
	 * @since 1.4.0
	 *
	 * @return string
	 */
	public function akismet_discard_month() {
		return 'true';
	}

	/**
	 * Add and remove sites to the Contact Info profile section
	 *
	 * @since 1.7.0
	 *
	 * @param  array $contactmethods The existing contact methods
	 * @return array                 The updated contact methods
	 */
	public function contactmethods( $contactmethods ) {
		$contactmethods['facebook'] = 'Facebook URL';
		$contactmethods['linkedin'] = 'LinkedIn URL';
		$contactmethods['tumblr'] = 'Tumblr URL';
		$contactmethods['pinterest'] = 'Pinterest username';
		$contactmethods['instagram'] = 'Instagram username';
		$contactmethods['skype'] = 'Skype username';
		$contactmethods['youtube'] = 'YouTube URL';
		$contactmethods['github'] = 'GitHub username';

		if ( current_user_can( 'edit_users' ) ) {
			$contactmethods['_rns_private_email'] = '(Private) Email address';
			$contactmethods['_rns_private_cellphone'] = '(Private) Cell phone';
			$contactmethods['_rns_private_homephone'] = '(Private) Home phone';
		}

		if ( isset( $contactmethods['yim'] ) ) {
			unset( $contactmethods['yim'] );
		}

		if ( isset( $contactmethods['jabber'] ) ) {
			unset( $contactmethods['jabber'] );
		}

		return $contactmethods;
	}

	/**
	 * Display an "Additional Info" section of custom fields on user profiles
	 *
	 * This hook applies globally, and other plugins or classes can add fields as needed
	 *
	 * Requires Developers Custom Fields
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	public function additional_info_metabox() {
		if ( ! function_exists( 'slt_cf_register_box' ) ) {
			return;
		}

		$fields = array();
		$fields = apply_filters( 'rns_additional_info_fields', $fields );
		if ( empty( $fields ) ) {
			return;
		}

		$box = array(
		  'type' => 'user',
		  'id' => 'rns_additional_user_fields',
		  'title' => 'Additional Info',
		  'fields' => $fields,
		);

		slt_cf_register_box( $box );
	}

	/**
	 * Add a "Long Description" field to user profiles globally
	 *
	 * @since 1.7.0
	 *
	 * @param array $fields The existing array of fields to be passed to the metabox
	 * @return array The updated array of fields
	 */
	public function long_description_field( $fields ) {
		$fields[] = array(
			'name' => 'rns_long_description',
			'label' => 'Long Biographical Info',
			'description' => 'Share a longer biography to be used, for example, on the staff pages.',
			'type' => 'wysiwyg',
			'edit_on_profile' => true
		);
		return $fields;
	}

	/**
	 * Expose the "Primary Blog" field to users who can manage the network
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	public function primary_blog_id_metabox() {
		if ( ! function_exists( 'slt_cf_register_box' ) ) {
			return;
		}

		$box = array(
			'type' => 'user',
			'id' => 'primary_blog_id',
			'title' => 'Primary Blog',
			'fields' => array(
				array(
					'name' => 'primary_blog',
					'label' => 'ID',
					'type' => 'text',
					'capabilities' => array( 'manage_network' )
				),
			),
		);

		slt_cf_register_box( $box );
	}

	/**
	 * Filter the <title> tag for Location archive pages when WordPress SEO is on
	 *
	 * @since 1.7.0
	 *
	 * @param string $value A blank string, what gets used if no other filters are added
	 * @param array $option The options array from WordPress SEO
	 * @return string The string "Location: " prepended to whatever is used on Category archives
	 */
	public function wpseo_location_archive_title( $value, $option ) {
		return 'Location: ' . $option['title-category'];
	}

}
