<?php 
/**
 * Plugin Name:       Chat button for ISL Pronto
 * Plugin URI:        
 * Description:       Render an ISL Pronto chat button on your site
 * Version:           1.0
 * Requires at least: 5.0
 * Requires PHP:      7.2
 * Author:            David Dean
 * Author URI:        
 * License:           GPLv2
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dd-isl-pronto
 * 
 * @package Chat_Button_For_Isl_Pronto
 */

if ( ! class_exists( 'DD_ISLPronto' ) ) {
	
	/**
	 * The Chat button for ISL Pronto class
	 * 
	 * @author ddean
	 */
	class DD_ISLPronto {
		
		const DEFAULTS = array(
			'dd_isl_pronto_scripturl'  => 'https://islpronto.islonline.net/live/islpronto/public/chat.js',
			'dd_isl_pronto_imagepath'  => 'https://www.example.com/images/',
			'dd_isl_pronto_offlineurl' => 'mailto:user@example.com',
			'dd_isl_pronto_position'   => 'static',
			'dd_isl_pronto_domain'     => null,
			'dd_isl_pronto_filter'     => null,
			'dd_isl_pronto_showall'    => false,
		);
		
		/**
		 * Instance variable
		 * 
		 * @var unknown
		 */
		protected static $instance;
		
		/**
		 * Get single instance of this class.
		 * 
		 * @return DD_ISLPronto
		 */
		public static function get_instance() {
			if ( ! self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}
		
		/**
		 * Register all the hooks and make the magic happen.
		 */
		public function __construct() {
			add_shortcode( 'islpronto', array( $this, 'render_shortcode' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'register_chat_script' ) );
			add_action( 'wp_footer',  array( $this, 'maybe_add_chat_image' ) );
			add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			add_action( 'admin_init', array( $this, 'build_settings' ) );
		}
		
		/**
		 * Display the chat button in the page.
		 * 
		 * @param array $attrs Attributes.
		 */
		private function render_chat( $attrs ) {
			$position_style = '';
			
			// If the position isn't static, be lazy and build an inline style statement.
			
			if ( 'static' != $attrs['position'] ) {
				
				// Validate the position string.
				$pos_match = preg_match( '/(top|right|bottom|left)\-?(top|right|bottom|left)?/', $attrs['position'], $pos );
				
				if ( $pos_match ) {
					$position_style = 'style="position: fixed; ';
					$transform = '';
					
					if ( 'top' == $pos[1] ) {
						$position_style .= 'top: 0; ';
						$transform       = 'left: 50%; transform: translateX(-50%);';
					} elseif ( 'right' == $pos[1] ) {
						$position_style .= 'right: 0; ';
						$transform       = 'top: 50%; transform: translateY(-50%);';
					} elseif ( 'bottom' == $pos[1] ) {
						$position_style .= 'bottom: 0; ';
						$transform       = 'left: 50%; transform: translateX(-50%);';
					} elseif ( 'left' == $pos[1] ) {
						$position_style .= 'left: 0; ';
						$transform       = 'top: 50%; transform: translateY(-50%);';
					}
					
					if ( $pos[2] ) {
						if ( 'top' == $pos[2] ) {
							$transform = 'top: 10%; transform: translateY(-50%);';
						} elseif ( 'right' == $pos[2] ) {
							$transform = 'left: 80%; transform: translateX(-50%);';
						} elseif ( 'bottom' == $pos[2] ) {
							$transform = 'top: 80%; transform: translateY(-50%);';
						} elseif ( 'left' == $pos[2] ) {
							$transform = 'left: 10%; transform: translateX(-50%);';
						}
					}
					
					$position_style .= $transform . '"';
				}
			}
			
			$position_style = apply_filters( 'dd_isl_pronto_chat_style', $position_style );
			
			// Render the chat button.
			?>
			<a href="<?= $attrs['offlineurl'] ?>" id="islpronto_link" <?= $position_style ?>>
			  <img alt="Live chat" id="islpronto_image" src="<?= $attrs['imagepath'] ?>/islpronto-message.jpg" style="border:none" />
			</a>
			<?php 
			
			wp_enqueue_script( 'islpronto_script' );
		}

		/**
		 * Render the chat button inline with a shortcode
		 * 
		 * @param array $attrs Attributes from shortcode.
		 */
		public function render_shortcode( $attrs = null ) {
			// Combine passed args with defaults.
			$my_atts = shortcode_atts(
				array(
					'scripturl'  => get_option( 'dd_isl_pronto_scripturl', self::DEFAULTS['dd_isl_pronto_scripturl'] ),
					'imagepath'  => get_option( 'dd_isl_pronto_imagepath',  self::DEFAULTS['dd_isl_pronto_imagepath'] ),
					'position'   => 'static',
					'offlineurl' => get_option( 'dd_isl_pronto_offlineurl', self::DEFAULTS['dd_isl_pronto_offlineurl'] ),
					'domain'     => get_option( 'dd_isl_pronto_domain',     self::DEFAULTS['dd_isl_pronto_domain'] ),
					'filter'     => get_option( 'dd_isl_pronto_filter',     self::DEFAULTS['dd_isl_pronto_filter'] ),
				),
				$attrs
			);
			
			// If script path, domain, or filter differ from default, deregister/reregister script.
			
			$unregistered = false;
			$script_vars = array( 'scripturl', 'domain', 'filter' );
			foreach ( $script_vars as $script_var ) {
				if ( get_option( 'dd_isl_pronto_' . $script_var, self::DEFAULTS[ 'dd_isl_pronto_' . $script_var ] ) != $my_atts[ $script_var ] ) {
					wp_deregister_script( 'islpronto_script' );
					$unregistered = true;
				}
			}
			
			if ( $unregistered ) {
				
				// Re-add the script to the end of the page with the new params.
				$my_atts['scripturl'] = $this->build_script_url( $my_atts['scripturl'], $my_atts['domain'], $my_atts['filter'] );
				
				wp_register_script( 'islpronto_script', apply_filters( 'dd_isl_pronto_scripturl', $my_atts['scripturl'] ), array(), false, true);
			}
			
			// Don't show the global button if we are showing it in a shortcode.
			add_filter( 'dd_isl_pronto_showall', '__return_false' );
			
			ob_start();
			?>
			<?php $this->render_chat( $my_atts ); ?>
			<?php 
			return ob_get_clean();
		}
		
		/**
		 * Build the chat.js script URL and register it with WP
		 */
		public function register_chat_script() {
			$attrs = array(
				'scripturl' => get_option( 'dd_isl_pronto_scripturl', self::DEFAULTS['dd_isl_pronto_scripturl'] ),
				'domain'    => get_option( 'dd_isl_pronto_domain',    self::DEFAULTS['dd_isl_pronto_domain'] ),
				'filter'    => get_option( 'dd_isl_pronto_filter',    self::DEFAULTS['dd_isl_pronto_filter'] ),
			);
			
			// Add the script to the end of the page.
			$attrs['scripturl'] = $this->build_script_url( $attrs['scripturl'], $attrs['domain'], $attrs['filter'] );
			
			wp_register_script( 'islpronto_script', apply_filters( 'dd_isl_pronto_scripturl', $attrs['scripturl'] ), array(), false, true );
		}
		
		/**
		 * Separate script URL logic so we can test it
		 * 
		 * @param string $scripturl Path to the ISL Pronto chat script.
		 * @param string $domain Domain name used for ISL Pronto.
		 * @param string $filter Filter to designate a group within a domain.
		 */
		public function build_script_url( $scripturl = '', $domain = '', $filter = '' ) {
			
			if ( ! empty( $domain ) ) {
				$scripturl .= '?d=' . urlencode( $domain );
				
				if ( ! empty( $filter ) ) {
					$scripturl .= '&filter=' . urlencode( $filter );
				}
			}
			
			return $scripturl;
		}
		
		/**
		 * Show the icon on all pages if the admin has opted for that
		 */
		public function maybe_add_chat_image() {
			$show_chat_image = apply_filters( 'dd_isl_pronto_showall', get_option( 'dd_isl_pronto_showall', self::DEFAULTS['dd_isl_pronto_showall'] ) );
			
			if ( $show_chat_image ) {
				// If the button is to be shown on every page, build the button tags and then render them.
				$attrs = array(
					'imagepath'  => get_option( 'dd_isl_pronto_imagepath',  self::DEFAULTS['dd_isl_pronto_imagepath'] ),
					'position'   => get_option( 'dd_isl_pronto_position',   self::DEFAULTS['dd_isl_pronto_position'] ),
					'offlineurl' => get_option( 'dd_isl_pronto_offlineurl', self::DEFAULTS['dd_isl_pronto_offlineurl'] ),
				);
				
				$this->render_chat( $attrs );
			}
		}
		
		/*
		 * Everything below here is for the admin page
		 */
		
		/**
		 * 
		 * Register settings for the ISL Pronto button
		 */
		public function register_settings() {
			register_setting( 'dd-isl-pronto', 'dd_isl_pronto_scripturl' );
			register_setting( 'dd-isl-pronto', 'dd_isl_pronto_imagepath' );
			register_setting( 'dd-isl-pronto', 'dd_isl_pronto_offlineurl' );
			register_setting( 'dd-isl-pronto', 'dd_isl_pronto_domain' );
			register_setting( 'dd-isl-pronto', 'dd_isl_pronto_filter' );
			register_setting( 'dd-isl-pronto', 'dd_isl_pronto_position' );
			register_setting( 'dd-isl-pronto', 'dd_isl_pronto_showall' );
		}
		
		/**
		 * 
		 * Create a new page for ISL Pronto button settings
		 */
		public function add_settings_page() {
			add_options_page(
				__( 'ISL Pronto settings', 'dd-isl-pronto' ),
				__( 'ISL Pronto settings', 'dd-isl-pronto' ),
				'manage_options',
				'dd-isl-pronto',
				array( $this, 'render_settings_page' )
			);
		}
		
		/**
		 * 
		 * Display the ISL Pronto button settings page
		 */
		public function render_settings_page() {
			?>
			<form method="POST" action="options.php">
				<?php
				settings_fields( 'dd-isl-pronto' );
				do_settings_sections( 'dd-isl-pronto' );
				submit_button();
				?>
			</form>
			<?php
		}
		
		/**
		 * 
		 * Build settings section and fields for the page
		 */
		public function build_settings() {
			add_settings_section(
				'dd_isl_pronto',
				__( 'ISL Pronto settings', 'dd-isl-pronto' ),
				function() { printf( '<p>%s</p>', __( 'Settings for Chat button for ISL Pronto', 'dd-isl-pronto' ) ); },
				'dd-isl-pronto'
			);
			
			add_settings_field( 'dd_isl_pronto_scripturl',  __( 'Script URL','dd-isl-pronto' ),         array( $this, 'render_scripturl' ), 'dd-isl-pronto', 'dd_isl_pronto' );
			add_settings_field( 'dd_isl_pronto_imagepath',  __( 'Image path','dd-isl-pronto' ),         array( $this, 'render_imagepath' ),  'dd-isl-pronto', 'dd_isl_pronto' );
			add_settings_field( 'dd_isl_pronto_offlineurl', __( 'Offline URL','dd-isl-pronto' ),        array( $this, 'render_offlineurl' ), 'dd-isl-pronto', 'dd_isl_pronto' );
			add_settings_field( 'dd_isl_pronto_domain',     __( 'Domain','dd-isl-pronto' ),             array( $this, 'render_domain' ),     'dd-isl-pronto', 'dd_isl_pronto' );
			add_settings_field( 'dd_isl_pronto_filter',     __( 'Filter','dd-isl-pronto' ),             array( $this, 'render_filter' ),     'dd-isl-pronto', 'dd_isl_pronto' );
			add_settings_field( 'dd_isl_pronto_position',   __( 'Position','dd-isl-pronto' ),           array( $this, 'render_position' ),   'dd-isl-pronto', 'dd_isl_pronto' );
			add_settings_field( 'dd_isl_pronto_showall',    __( 'Show on all pages?','dd-isl-pronto' ), array( $this, 'render_showall' ),    'dd-isl-pronto', 'dd_isl_pronto' );
		}
		
		public function render_scripturl() {  $this->render_textbox( 'dd_isl_pronto_scripturl',  array( 'size' => 80 ) ); }
		public function render_imagepath() {  $this->render_textbox( 'dd_isl_pronto_imagepath',  array( 'size' => 50 ) ); }
		public function render_offlineurl() { $this->render_textbox( 'dd_isl_pronto_offlineurl', array( 'size' => 50 ) ); }
		public function render_domain() {     $this->render_textbox( 'dd_isl_pronto_domain',     array( 'size' => 30 ) ); }
		public function render_filter() {     $this->render_textbox( 'dd_isl_pronto_filter',     array( 'size' => 30 ) ); }
		public function render_position() {   $this->render_dropdown( 'dd_isl_pronto_position',  array( 'top', 'top-right', 'right-top', 'right', 'right-bottom', 'bottom-right', 'bottom', 'bottom-left', 'left-bottom', 'left', 'left-top', 'top-left' ) ); }
		public function render_showall() {    $this->render_checkbox( 'dd_isl_pronto_showall' ); }
		
		/**
		 * 
		 * Just a quick function to make a textbox for admin settings
		 * 
		 * @param string     $name Name of the option.
		 * @param array|null $attrs Any HTML attributes for the control.
		 */
		private function render_textbox( $name = '', $attrs = null ) {
			$value = get_option( $name, self::DEFAULTS[ $name ] );
			
			$attr_str = '';
			if ( ! is_null( $attrs ) ) {
				foreach ( $attrs as $key => $attrval ) {
					$attr_str .= htmlspecialchars( $key ) . '="' . htmlspecialchars( $attrval, ENT_QUOTES, null, false ) . '" ';
				}
			}
			
			printf(
				'<input type="text" name="%s" value="%s"%s></input>',
				htmlspecialchars( $name ),
				htmlspecialchars( $value ),
				$attr_str
			);
		}
		
		/**
		 * 
		 * Quick function to make a dropdown for admin settings
		 * 
		 * @param string     $name Name of the option.
		 * @param null|array $options Array of option values or NULL if empty.
		 */
		private function render_dropdown( $name = '', $options = null ) {
			$value = get_option( $name, self::DEFAULTS[ $name ] );
			
			$option_str = '';
			foreach ( $options as $key => $lvalue ) {
				$lvalue = htmlspecialchars( $lvalue );
				$option_str .= sprintf(
					'<option value="%s"%s>%s</option>',
					( is_int( $key ) ? $lvalue : $key ),
					( $value == $lvalue ? ' selected' : '' ),
					$lvalue
				);
			}
			
			printf(
				'<select name="%s">%s</select>',
				htmlspecialchars( $name ),
				$option_str
			);
		}
		
		/**
		 * Quick function to render a checkbox
		 * 
		 * @param string $name Name of the option.
		 */
		private function render_checkbox( $name = '' ) {
			$value = get_option( $name, self::DEFAULTS[ $name ] );
			
			printf(
				'<input type="checkbox" name="%s"%s></input>',
				htmlspecialchars( $name ),
				( $value ? ' checked' : '' )
			);
		}
	}
	
	$DD_ISLPronto = DD_ISLPronto::get_instance();
}
