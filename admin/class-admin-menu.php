<?php

namespace RNS;

class Admin_Menu {

	/**
	 * Instance of this class
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * The text to be displayed in the title tags of the page when the menu is selected
	 *
	 * @var string
	 */
	private $page_title = 'Religion News LLC';

	/**
	 * The text to be used for the menu
	 *
	 * @var string
	 */
	private $menu_title = 'Religion News LLC';

	/**
	 * The capability required for this menu to be displayed to the user
	 *
	 * @var string
	 */
	private $capability = 'manage_options';

	/**
	 * The slug name to refer to this menu by
	 *
	 * @var string
	 */
	protected $menu_slug = 'rns_global';

	/**
	 * Settings group name for this menu
	 *
	 * All settings that appear on this menu should be registered to this group
	 *
	 * @var string
	 */
	private $option_group = 'rns_global';

	/**
	 * Dashicons helper class to use a font icon
	 *
	 * @var string
	 */
	private $icon_url = 'dashicons-admin-site';

	/**
	 * Initialize the class
	 */
	private function __construct() {
		//add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
	}

	/**
	 * Return an instance of this class
	 * 
	 * @return object
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Register the administration menu for this plugin
	 *
	 * @return void
	 */
	public function add_plugin_admin_menu() {
		add_menu_page(
			__( $this->page_title, 'rns_global' ),
			__( $this->menu_title, 'rns_global' ),
			$this->capability,
			$this->menu_slug,
			array( $this, 'include_view' ),
			$this->icon_url
		);
	}

	/**
	 * Render the admin menu
	 *
	 * @return void
	 */
	public function include_view() {
		include_once( 'views/admin-menu.php' );		
	}

}
