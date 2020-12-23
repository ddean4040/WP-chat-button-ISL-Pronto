<?php
/**
 * Class SampleTest
 *
 * @package Isl_Pronto_Wp_Plugin
 */

/**
 * Sample test case.
 */
class DD_ISLProntoTest extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->instance = DD_ISLPronto::getInstance();
	}
	
	public function test_shortcode() {
		
		$default_attrs = [
			'scriptpath' => 'http://example.com/scriptpath/chat.js',
			'imagepath'  => 'http://example.com/images/',
			'position'   => 'static',
			'offlineurl' => 'mailto:offline@example.com',
			'domain'     => 'example.org',
			'filter'     => 'abc123'
		];
		
		$attrs = $default_attrs;
		
		ob_start();
		$this->instance->render_shortcode( $attrs );
		
		$result = ob_get_clean();
		
		echo $result;
		
	}
	
	public function test_scripturl() {
		
	}
	
	public function test_single_button_instance() {
		
	}
}
