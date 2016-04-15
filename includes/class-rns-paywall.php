<?php

namespace RNS;

class Paywall {

	/**
	 * Instance of this class.
	 *
	 * @since 1.6.0
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * The capability allowing a user to bypass the archive.
	 *
	 * @since 1.6.0
	 * @var string
	 */
	private $bypass_paywall_cap = 'rns_bypass_paywall';

	/**
	 * The capability a user needs to be shown the paywall warning message.
	 *
	 * @since 1.6.0
	 * @var string
	 */
	private $paywall_warning_cap = 'edit_posts';

	/**
	 * Length of time in seconds for which content remains outside the paywall.
	 *
	 * 31536000 == 1 year.
	 *
	 * @since 1.6.0
	 * @var int
	 */
	private $time_before_paywall = 31536000;

	/**
	 * Post types affected by the paywall.
	 *
	 * @since 1.6.0
	 * @var array
	 */
	private $post_types_blocked = array( 'post', 'attachment' );

	/**
	 * Message shown to users trying to access content behind the paywall.
	 *
	 * @since 1.6.0
	 * @var string
	 */
	private $subscription_required_message = 'Active RNS subscribers and members can view this content&nbsp;<a href="http://archives.religionnews.com" target="_blank">at the RNS Archives website</a>.';

	/**
	 * Warning shown to capable users that content is behind the paywall.
	 *
	 * This is helpful to show in case editors want to link to old content on
	 * social media, for example.
	 *
	 * @since 1.6.0
	 * @var string
	 */
	private $behind_paywall_warning = 'Eds: This article is behind the paywall and cannot be accessed by non-subscribers';

	/**
	 * Meta key for marking a post as exempt from the paywall.
	 *
	 * @since 1.6.0
	 * @var string
	 */
	protected $exempt_from_paywall_key = '_rns_exempt_from_paywall';

	/**
	 * Initialize the class.
	 *
	 * @since 1.6.0
	 */
	private function __construct() {
		add_filter( 'members_get_capabilities', array( $this, 'add_cap' ) );
		add_filter( 'the_content', array( $this, 'filter_content' ) );
	}

	/**
	 * Return an instance of this class
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
	 * Getter for $bypass_paywall_cap.
	 *
	 * @since 1.6.0
	 * @return string
	 */
	public function get_bypass_paywall_cap() {
		return $this->bypass_paywall_cap;		
	}

	/**
	 * Getter for $exempt_from_paywall_key.
	 *
	 * @since 1.6.0
	 * @return string
	 */
	public function get_exempt_from_paywall_key() {
		return $this->exempt_from_paywall_key;	
	}

	/**
	 * Add the rns_bypass_paywall capability via the Members plugin.
	 *
	 * @since 1.6.0
	 * @param array $capabilities The array of available capabilities
	 * @return array The updated array
	 */
	public function add_cap( $capabilities ) {
		$capabilities[] = $this->bypass_paywall_cap;
		return $capabilities;
	}

	/**
	 * Determine whether a post is exempt from the paywall.
	 *
	 * @see  RNS\Paywall_Metabox::exempt_from_paywall_field() For how the field
	 *     is set up and saved in the backend, including the values that would
	 *     be returned for "on" or "off" states.
	 *
	 * @since 1.6.0
	 * @return string|null
	 */
	private function is_exempt_from_paywall() {
		return get_post_meta( get_the_ID(), $this->exempt_from_paywall_key, true );	
	}

	/**
	 * Determine whether a post is behind the paywall.
	 * 
	 * Must be used within the loop.
	 * 
	 * @uses $this->time_before_paywall
	 * @uses get_post_time()
	 *
	 * @since 1.6.0
	 * @return boolean
	 */
	private function is_behind_paywall() {
		$now = strtotime( 'now ' . get_option( 'timezone_string' ) );
		$published = get_post_time();

		$time_since_published = $now - $published;
		return $time_since_published > $this->time_before_paywall;
	}

	/**
	 * Format and return a warning to editors that a post is behind the paywall.
	 *
	 * @uses $this->behind_paywall_warning The text of the warning.
	 *
	 * @since 1.6.0
	 * @return string
	 */
	private function paywall_warning() {
		return sprintf( '<p style="background-color: #c60f13; color: white; padding: 5px;"><strong>%s</strong></p>', $this->behind_paywall_warning );
	}

	/**
	 * Format and return a warning to users that a post is behind the paywall.
	 *
	 * @uses $this->subscription_required_message The text of the message.
	 *
	 * @since 1.6.0
	 * @return string
	 */
	private function subscription_required() {
		return sprintf( '<p><strong>%s</strong></p>', $this->subscription_required_message );	
	}

	/**
	 * Generate a preview of a post that is behind the paywall.
	 *
	 * @see  $this::filter_content(). Previews are currently the post excerpt,
	 *     but when this method is called from filter_content(), it creates an
	 *     infinite loop of calls to the "the_content" filter. This method adds
	 *     and removes the filter so the excerpt can be safely generated.
	 * @link http://lists.automattic.com/pipermail/wp-hackers/2010-June/032424.html For further discussion and the source of this hack.
	 *
	 * @since 1.6.0
	 * @return string The preview
	 */
	private function generate_preview() {
		remove_filter( 'the_content', array( $this, 'filter_content' ) );
		$preview = wpautop( get_the_excerpt() );
		add_filter( 'the_content', array( $this, 'filter_content' ) );
		return $preview;
	}

	/**
	 * Hide paywalled content from unprivileged users.
	 *
	 * Returns the content immediately if the post has been marked as exempt
	 * from the paywall. The paywall itself applies only when is_single().
	 *
	 * @uses $this::is_behind_paywal()
	 * @uses $this::subscription_required()
	 * @uses $this::generate_preview()
	 * @uses $this::paywall_warning()
	 *
	 * @since 1.6.0
	 * @param  string $content The post content
	 * @return string          The full post content or a preview
	 */
	public function filter_content( $content ) {
		if ( $this->is_exempt_from_paywall() ) {
			return $content;
		}

		$warning = '';

		if ( $this->is_behind_paywall() && is_single() ) {
			if ( ! current_user_can( $this->bypass_paywall_cap ) ) {
				$content = $this->subscription_required();
				$content .= $this->generate_preview();
			}
			if ( current_user_can( $this->paywall_warning_cap ) ) {
				$warning = $this->paywall_warning();
			}
		}

		return $warning . $content;
	}

}
