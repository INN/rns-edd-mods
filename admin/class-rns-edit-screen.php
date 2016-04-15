<?php

namespace RNS;

/**
 * Class for the edit.php screen in the Dashboard
 */
class Edit_Screen {

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * A piece of an SQL query that suggests the query is from months_dropdown().
	 *
	 * @since 1.5.3
	 * @var string
	 */
	private $months_dropdown_query_signature = "SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month";

	/**
	 * Initialize the class.
	 */
	private function __construct() {
		add_filter( 'query', array( $this, 'query' ) );
	}

	/**
	 * Return an instance of this class.
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
	 * Checks whether an SQL query matches the months_dropdown() signature
	 *
	 * @since 1.5.3
	 * @return bool
	 */
	private function is_months_dropdown_query( $query ) {
		return strpos( $query, $this->months_dropdown_query_signature ) !== false;
	}

	/**
	 * Return a value that will cause mysql_query() not to query the database.
	 *
	 * @since 1.5.3
	 * @return null
	 */
	private function erase_query() {
		return null;
	}

	/**
	 * Filter queries before they're sent to the database.
	 *
	 * @see  $this::is_months_dropdown_query().
	 * @see  $this::erase_query().
	 *
	 * @since 1.5.3
	 * @return string|null
	 */
	public function query( $query ) {
		if ( $this->is_months_dropdown_query( $query ) ) {
			$query = $this->erase_query();
		}
		return $query;;
	}

}
