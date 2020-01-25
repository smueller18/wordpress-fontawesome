<?php
namespace FortAwesome;
/**
 * Class ApiSettingsTest
 */
require_once dirname( __FILE__ ) . '/../includes/class-fontawesome-activator.php';
require_once dirname( __FILE__ ) . '/../includes/class-fontawesome-api-settings.php';
require_once dirname( __FILE__ ) . '/_support/font-awesome-phpunit-util.php';

use \WP_Error, \InvalidArgumentException;

class ApiSettingsTest extends \WP_UnitTestCase {

	public function setUp() {
		wp_delete_file(FontAwesome_API_Settings::ini_path());
		FontAwesome_Activator::activate();
	}

	protected function create_api_settings_with_mocked_response( $response ) {
		return mock_singleton_method(
			$this,
			FontAwesome_API_Settings::class,
			'post',
			function( $method ) use ( $response ) {
				$method->willReturn(
					$response
				);
			}
		);
	}

	public function test_read_from_file() {
		$contents = <<< EOD
api_token = "abc123"
access_token = "xyz456"
access_token_expiration_time = "999999999"
EOD;		
		$path = FontAwesome_API_Settings::ini_path();
		$this->assertStringEndsWith(FontAwesome_API_Settings::FILENAME, $path);

		$write_result = @file_put_contents( $path, $contents ); 

		$this->assertGreaterThan(0, $write_result, 'writing ini file failed');

		// Force re-read
		$api_settings = FontAwesome_API_Settings::reset();

		$this->assertEquals('abc123', $api_settings->api_token());
		$this->assertEquals('xyz456', $api_settings->access_token());
		$this->assertEquals('999999999', $api_settings->access_token_expiration_time());

	}

	public function test_read_from_file_when_no_file_exists() {
		// Force re-read
		$api_settings = FontAwesome_API_Settings::reset();

		$this->assertNull($api_settings->api_token());
		$this->assertNull($api_settings->access_token());
		$this->assertNull($api_settings->access_token_expiration_time());
	}

	public function test_write() {
		// Start with nothing
		// Force re-read
		$api_settings = FontAwesome_API_Settings::reset();

		$api_settings->set_api_token('foo');
		$api_settings->set_access_token('bar');
		$api_settings->set_access_token_expiration_time(42);

		$result = $api_settings->write();
		$this->assertTrue($result, 'writing ini file failed');

		// Force re-read
		$api_settings = FontAwesome_API_Settings::reset();

		$this->assertEquals('foo', $api_settings->api_token());
		$this->assertEquals('bar', $api_settings->access_token());
		$this->assertEquals(42, $api_settings->access_token_expiration_time());
		$this->assertTrue( is_int( $api_settings->access_token_expiration_time() ) );

		// Round-trip it again

		$result = $api_settings->write();
		// Force re-read
		$api_settings = FontAwesome_API_Settings::reset();

		$this->assertEquals('foo', $api_settings->api_token());
		$this->assertEquals('bar', $api_settings->access_token());
		$this->assertEquals(42, $api_settings->access_token_expiration_time());
		$this->assertTrue( is_int( $api_settings->access_token_expiration_time() ) );
	}

	// What if we only write an api token, leave the others null, and the
	// read it back in?
	public function test_round_trip_only_api_token() {
		// Start with nothing
		// Force re-read
		$api_settings = FontAwesome_API_Settings::reset();

		$api_settings->set_api_token('foo');

		$result = $api_settings->write();
		$this->assertTrue($result, 'writing ini file failed');

		// Force re-read
		$api_settings = FontAwesome_API_Settings::reset();

		$this->assertEquals('foo', $api_settings->api_token());
		$this->assertNull($api_settings->access_token());
		$this->assertNull($api_settings->access_token_expiration_time());
	}

	public function test_request_access_token() {
		$api_settings = $this->create_api_settings_with_mocked_response(
			array(
				'response' => array(
					'code'    => 200,
					'message' => 'OK',
				),
				'body'     => json_encode(
					array(
						'access_token' => '123',
						'expires_in' => 3600
					)
				),
			) 
		); 

		$api_settings->set_api_token('xyz');
		$result = $api_settings->request_access_token();

		$this->assertTrue($result);
		$this->assertEquals('123', $api_settings->access_token());
		$this->assertEquals('xyz', $api_settings->api_token());
		$this->assertEqualsWithDelta(time() + 3600, $api_settings->access_token_expiration_time(), 2.0);

		// Force re-read
		$api_settings = FontAwesome_API_Settings::reset();

		// Everything should have remained the same through the write/read round trip
		$this->assertEquals('123', $api_settings->access_token());
		$this->assertEquals('xyz', $api_settings->api_token());
		$this->assertEqualsWithDelta(time() + 3600, $api_settings->access_token_expiration_time(), 2.0);
	}

	public function test_request_access_token_without_api_token() {
		$api_settings = $this->create_api_settings_with_mocked_response(
			array(
				'response' => array(
					'code'    => 200,
					'message' => 'OK',
				),
				'body'     => json_encode(
					array(
						'access_token' => '123',
						'expires_in' => 3600
					)
				),
			) 
		); 

		$result = $api_settings->request_access_token();

		$this->assertTrue( $result instanceof WP_Error );
		$this->assertEquals( 'api_token', $result->get_error_code() );
	}

	public function test_request_access_token_when_request_errors() {
		$api_settings = $this->create_api_settings_with_mocked_response(
			new WP_Error()
		); 

		$api_settings->set_api_token('xyz');

		$result = $api_settings->request_access_token();

		$this->assertTrue( $result instanceof WP_Error );
		$this->assertEquals( 'access_token', $result->get_error_code() );
		$this->assertArraySubset( [ 'status' => 403 ], $result->get_error_data() );
	}

	public function test_request_access_token_when_request_returns_non_200_response() {
		$api_settings = $this->create_api_settings_with_mocked_response(
			array(
				'response' => array(
					'code'    => 403,
					'message' => 'Forbidden',
				),
				'body'     => ''
			) 
		); 


		$api_settings->set_api_token('xyz');

		$result = $api_settings->request_access_token();

		$this->assertTrue( $result instanceof WP_Error );
		$this->assertEquals( 'access_token', $result->get_error_code() );
		$this->assertArraySubset( [ 'status' => 403 ], $result->get_error_data() );
	}

	public function test_request_access_token_when_request_body_lacks_access_token() {
		$api_settings = $this->create_api_settings_with_mocked_response(
			array(
				'response' => array(
					'code'    => 403,
					'message' => 'Forbidden',
				),
				'body'     => json_encode(
					array(
						'expires_in' => 3600
					)
				)
			) 
		); 


		$api_settings->set_api_token('xyz');

		$result = $api_settings->request_access_token();

		$this->assertTrue( $result instanceof WP_Error );
		$this->assertEquals( 'access_token', $result->get_error_code() );
		$this->assertArraySubset( [ 'status' => 403 ], $result->get_error_data() );
	}

	public function test_request_access_token_when_request_body_lacks_expires_in() {
		$api_settings = $this->create_api_settings_with_mocked_response(
			array(
				'response' => array(
					'code'    => 403,
					'message' => 'Forbidden',
				),
				'body'     => json_encode(
					array(
						'access_token' => 'abc'
					)
				)
			) 
		); 


		$api_settings->set_api_token('xyz');

		$result = $api_settings->request_access_token();

		$this->assertTrue( $result instanceof WP_Error );
		$this->assertEquals( 'access_token', $result->get_error_code() );
		$this->assertArraySubset( [ 'status' => 403 ], $result->get_error_data() );
	}

	public function test_set_access_token_expiration_time_non_integer() {
		$api_settings = FontAwesome_API_Settings::reset();

		$this->expectException( InvalidArgumentException::class );

		$api_settings->set_access_token_expiration_time("abc");
	}

	public function test_set_access_token_expiration_time_given_zero() {
		$api_settings = FontAwesome_API_Settings::reset();

		$this->expectException( InvalidArgumentException::class );

		$api_settings->set_access_token_expiration_time(0);
	}
}
