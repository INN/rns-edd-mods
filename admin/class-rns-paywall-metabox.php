<?php

namespace RNS;

class Paywall_Metabox extends Paywall {

	/**
	 * Instance of this class
	 *
	 * @since 1.6.0
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * The capability a user must have to see this metabox.
	 *
	 * @since 1.6.0
	 * @var string
	 */
	private $capability = 'edit_posts';

	/**
	 * ID of the metabox.
	 *
	 * @since 1.6.0
	 * @var string
	 */
	private $id = 'rns_paywall';

	/**
	 * Title of the metabox.
	 *
	 * @since 1.6.0
	 * @var string
	 */
	private $title = 'Paywall';

	/**
	 * Post types the metabox should appear on.
	 *
	 * @since 1.6.0
	 * @var array
	 */
	private $pages = array( 'post' );

	/**
	 * Display name for the "Exempt From Paywall" field in the metabox.
	 *
	 * @since 1.6.0
	 * @var array
	 */
	private $exempt_field_title = 'Post is Exempt From Paywall';
	
	/**
	 * Initialize the class
	 *
	 * @since 1.6.0
	 */
	private function __construct() {
		add_filter( 'cmb_meta_boxes', array( $this, 'add_metabox' ) );
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
	 * Create a metabox field for the "Exempt From Paywall" postmeta.
	 *
	 * @since 1.6.0
	 * @return array
	 */
	private function exempt_from_paywall_field() {
		$field = array(
			'name' => $this->exempt_field_title,
			'id' => $this->exempt_from_paywall_key,
			'type' => 'checkbox'
		);

		return $field;
	}

	/**
	 * Add the "Paywall" metabox.
	 *
	 * @uses $this->capability To check whether the user should see the metabox.
	 * @uses $this::exempt_from_paywall_field() Adds the field.
	 *
	 * @since 1.6.0
	 * @param array $metaboxes The array of existing metaboxes
	 * @return array The updated array, or the existing array if the user is incapable
	 */
	public function add_metabox( array $metaboxes ) {
		if ( current_user_can( $this->capability ) ) {
			$metaboxes[] = array(
				'id' => $this->id,
				'title' => $this->title,
				'pages' => $this->pages,
				'context' => 'normal',
				'priority' => 'default',
				'show_names' => true,
				'fields' => array(
					$this->exempt_from_paywall_field(),
				),
			);
		}

		return $metaboxes;
	}

}
