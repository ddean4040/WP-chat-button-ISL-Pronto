<?php
/**
 * Class DD_ISLProntoTest
 *
 * @package Chat_Button_For_Isl_Pronto
 */

/**
 * Sample test case.
 */
class DD_ISLProntoTest extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->instance = DD_ISLPronto::get_instance();
	}
	
	/**
	 * Test that image src and offline URL appear as expected in the shortcode.
	 */
	public function testBuildShortcode() {
		
		$default_attrs = [
			'scripturl'  => 'https://www.example.com/live/islpronto/public/chat.js',
			'imagepath'  => 'https://www.example.com/images/',
			'position'   => 'static',
			'offlineurl' => 'mailto:offline@example.com',
			'domain'     => 'example.org',
			'filter'     => 'abc123'
		];
		
		$attrs = $default_attrs;
		
		// Check that the defaults come through.
		$result = $this->instance->render_shortcode( $attrs );
		$this->assertRegexp( '/src\=\"https:\/\/www.example.com\//', $result );
		
		$attrs = array_merge(
			$default_attrs,
			[
				'imagepath'  => 'http://example.org/images/',
				'offlineurl' => 'mailto:offline@example.org',
			]
		);
		
		// Change the imagepath and offlineurl, then check that defaults were not used.
		$result = $this->instance->render_shortcode( $attrs );
		$this->assertRegexp( '/src\=\"http:\/\/example.org\//', $result );
		$this->assertRegexp( '/href\=\"mailto:offline@example.org/', $result );
		
	}
	
	/**
	 * Test that the script URL is built as expected, including domain and filter args.
	 */
	public function testBuildScriptURL() {
		$default_attrs = [
			'scripturl' => 'https://www.example.com/live/islpronto/public/chat.js',
			'domain'     => 'example.org',
			'filter'     => 'abc123'
		];
		
		$attrs = $default_attrs;
		
		$scripturl = $this->instance->build_script_url(
			$attrs['scripturl'],
			$attrs['domain'],
			$attrs['filter']
		);
		
		$this->assertEquals( $scripturl, 'https://www.example.com/live/islpronto/public/chat.js?d=example.org&filter=abc123' );
		
		// Test with a domain set but no filter.
		$attrs = [
			'scripturl' => 'https://www.example.com/live/islpronto/public/chat.js',
			'domain'     => 'example.org',
			'filter'     => ''
		];
		
		$scripturl = $this->instance->build_script_url(
			$attrs['scripturl'],
			$attrs['domain'],
			$attrs['filter']
			);
		
		$this->assertEquals( $scripturl, 'https://www.example.com/live/islpronto/public/chat.js?d=example.org' );
		
		// Test with no domain set.
		$attrs = [
			'scripturl' => 'https://www.example.com/live/islpronto/public/chat.js',
			'domain'     => '',
			'filter'     => ''
		];
		
		$scripturl = $this->instance->build_script_url(
			$attrs['scripturl'],
			$attrs['domain'],
			$attrs['filter']
			);
		
		$this->assertEquals( $scripturl, 'https://www.example.com/live/islpronto/public/chat.js' );
		
		// Test with a different scripturl.
		$attrs = [
			'scripturl' => 'https://example.org/live/islpronto/public/chat.js',
			'domain'     => '',
			'filter'     => ''
		];
		
		$scripturl = $this->instance->build_script_url(
			$attrs['scripturl'],
			$attrs['domain'],
			$attrs['filter']
			);
		
		$this->assertEquals( $scripturl, 'https://example.org/live/islpronto/public/chat.js' );
	}
	
	/**
	 * Test that when a button shortcode appears on a page, it overrides the site default button.
	 */
	public function testRenderSingleButtonInstance() {
		// Verify that showall setting is true.
		$showall_setting = apply_filters( 'dd_isl_pronto_showall', get_option( 'dd_isl_pronto_showall', true ) );
		
		$this->assertTrue( $showall_setting );
		
		
		// Render a shortcode.
		$default_attrs = [
			'scripturl' => 'https://www.example.com/live/islpronto/public/chat.js',
			'imagepath'  => 'https://www.example.com/images/',
			'position'   => 'static',
			'offlineurl' => 'mailto:offline@example.com',
			'domain'     => 'example.org',
			'filter'     => 'abc123'
		];
		
		$attrs = $default_attrs;
		$result = $this->instance->render_shortcode( $attrs );
		
		// Verify that showall setting is now false.
		$showall_setting = apply_filters( 'dd_isl_pronto_showall', get_option( 'dd_isl_pronto_showall', true ) );
		
		$this->assertFalse( $showall_setting );
		
	}
}
