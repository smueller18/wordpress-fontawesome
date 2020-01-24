<?php
/**
 * This module is not considered part of the public API, only internal.
 */
namespace FortAwesome;

//use \WP_Error, \Error, \Exception, \InvalidArgumentException;

/**
 * Provides read/write access to the Font Awesome API settings.
 */
class FontAwesome_API_Settings {
	/**
	 * Name of the settings file that will be stored adjacent to wp-config.php.
	 *
	 * @since 4.0.0
	 * @ignore
	 */
	const FILENAME = 'font-awesome-api.ini';

	/**
	 * Current access token.
	 *
	 * @internal
	 * @ignore
	 */
	protected $_access_token = null;

	/**
	 * Expiration time for current access token.
	 * 
	 * @internal
	 * @ignore
	 */
	protected $_access_token_expiration_time = null;

	/**
	 * Current API token.
	 * 
	 * @internal
	 * @ignore
	 */
	protected $_api_token = null;

	/**
	 * Singleton instance.
	 * 
	 * @internal
	 * @ignore
	 */
	protected static $_instance = null;

	/**
	 * Returns the FontAwesome_API_Settings singleton instance.
	 * 
	 * Internal use only. Not part of this plugin's public API.
	 *
	 * @return FontAwesome_API_Settings
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Resets the singleton instance referenced by this class and returns that new instance.
	 * All previous releases metadata held in the previous instance will be abandoned.
	 *
	 * @return FontAwesome_API_Settings
	 */
	public static function reset() {
		self::$_instance = null;
		return self::instance();
	}

	/**
	 * Private constructor.
	 *
	 * @ignore
	 */
	private function __construct() {
		$initial_data = self::read_from_file();

		if ( ! boolval( $initial_data ) ) {
			return;
		} else {
			if ( isset( $initial_data['api_token'] ) ) {
				$this->_api_token = $initial_data['api_token'];
			}
			if ( isset( $initial_data['access_token'] ) ) {
				$this->_access_token = $initial_data['access_token'];
			}
			if ( isset( $initial_data['access_token_expiration_time'] ) ) {
				$this->_access_token_expiration_time = $initial_data['access_token_expiration_time'];
			}
		}
	}

	/**
	 * Reads ini file into an associative array, or returns false
	 * if the file does not exist or there is an error.
	 * 
	 * Internal use only, not part of this plugin's public API.
	 * 
	 * @ignore
	 * @internal
	 */
	protected static function read_from_file() {
		$config_path = self::ini_path(); 

        if ( ! file_exists( $config_path ) ) { 
            return false; 
		} 

		return parse_ini_file ( $config_path, TRUE );
	}

	/**
     * Returns the path to our font-awesome-api.ini file where we'll store
	 * API token and access token data.
	 * 
	 * Internal use only. Not part of this plugin's public API.
	 *
	 * @ignore
	 * @internal
	 */
	static public function ini_path() {
		return trailingslashit( ABSPATH ) . self::FILENAME;
	}

	/**
	 * Writes current config.
	 * 
	 * Internal use only. Not part of this plugin's public API.
	 * 
	 * @ignore
	 * @internal
	 */
	public function write() {
		$date = date(DATE_RFC2822);
		$api_token = $this->api_token();
		$access_token = $this->access_token();
		$access_token_expiration_time = $this->access_token_expiration_time();
		$contents = <<< EOD
; Font Awesome API Settings
;
; Created by the font-awesome plugin on $date
;
; This was created when you added your Font Awesome API Token in the plugin's
; settings page. It allows your WordPress server to connect to your Font Awesome
; account for using kits and such.
;
; Use the plugin's settings page to manage the contents of this file.
; To get rid of it entirely, instead of just deleting this file, use the
; plugin settings page to delete the API Token. That will cause this whole file
; to go away, and also do the other cleanup necessary in the database.

api_token = '$api_token'
access_token = '$access_token'
access_token_expiration_time = '$access_token_expiration_time'
EOD;
		if ( !@file_put_contents( self::ini_path(), $contents ) ) { 
			return false; 
		} 
	}

	/**
	 * Returns the current API Token.
	 * 
	 * Internal use only. Not part of this plugin's public API.
	 * 
	 * @ignore
	 * @internal
	 */
	public function api_token() {
		return $this->_api_token;
	}

	/**
	 * Sets the API Token.
	 * 
	 * Internal use only. Not part of this plugin's public API.
	 */
	public function set_api_token($api_token) {
		$this->_api_token = $api_token;
	}

	/**
	 * Returns the current access token.
	 * 
	 * Internal use only. Not part of this plugin's public API.
	 * 
	 * @ignore
	 * @internal
	 */
	public function access_token() {
		return $this->_access_token;
	}

	/**
	 * Sets the current access_token.
	 * 
	 * Internal use only. Not part of this plugin's public API.
	 */
	public function set_access_token($access_token) {
		$this->_access_token = $access_token;
	}

	/**
	 * Sets the current access_token_expiration_time.
	 * 
	 * Internal use only. Not part of this plugin's public API.
	 */
	public function set_access_token_expiration_time($access_token_expiration_time) {
		$this->_access_token_expiration_time = $access_token_expiration_time;
	}

	/**
	 * Returns the expiration time for the current access token.
	 * 
	 * Internal use only. Not part of this plugin's public API.
	 * 
	 * @ignore
	 * @internal
	 */
	public function access_token_expiration_time() {
		return $this->_access_token_expiration_time;
	}
}

/**
 * Convenience global function to get a singleton instance of the API Settings.
 * 
 * Internal use only. Not part of this plugin's public API.
 *
 * @return FontAwesome_API_Settings
 */
function fa_api_settings() {
	return FontAwesome_API_Settings::instance();
}
