<?php

// Init the Admin side of the plugin
add_action( 'admin_init', 'register_options_setting' );
function register_options_setting() {
	register_setting( 'shortly_options', WP_SHORTLY_SLUG, 'shortly_sanitize_settings_callback' );
}

function shortly_sanitize_settings_callback( $inputs ) {

	$inputs['bitly_access_token'] = ( strlen( $inputs['bitly_access_token'] ) == 40 ) ? $inputs['bitly_access_token'] : '';
	if( empty( $inputs['bitly_access_token'] ) ) 
			add_settings_error( WP_SHORTLY_SLUG, '1', __('Error 1 : The Bit.ly Access Token is a 40 characters string', WP_SHORTLY_DOMAIN), 'error' );

	return $inputs;
}

/**
 * shortly_option_page
 * 
 * @access public
 * 
 * @since Version 0.2
 *
 * @return mixed Value.
 */
add_action( 'admin_menu', 'shortly_option_page' );
function shortly_option_page() {
	add_options_page(
		'WP Shortly', // The text to be displayed in the title tags of the page when the menu is selected
		'WP Shortly', // The on-screen name text for the menu
		'manage_options', // The capability required for this menu to be displayed to the user. User levels are deprecated and should not be used here!
		'wp_shortly', // The slug name to refer to this menu by (should be unique for this menu).
		'shortly_render_option_page' // The function that displays the page content for the menu page.
	);
}

add_action( 'admin_enqueue_scripts', 'shortly_add_admin_css_js' );
function shortly_add_admin_css_js() {
	wp_enqueue_style( 'options-shortly', WP_SHORTLY_ADMIN_CSS_URL . 'options.css', array(), WP_SHORTLY_VERSION );
}

function shortly_render_option_page() {
	add_settings_section( 'shortly_settings_display', __('WP Shortly Settings', WP_SHORTLY_DOMAIN), '__return_false', 'wp_shortly' );
		add_settings_field( 'shortly_bitly_access_token', __( 'Bit.ly API Access Token', WP_SHORTLY_DOMAIN ), 'shortly_render_options_fields', 'wp_shortly', 'shortly_settings_display',
			array( 'type'=>'text', 'label_for' => 'bitly_access_token', 'placeholder' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 'size' => '40', 'description'=> __('Access Token from your Bit.ly API.', WP_SHORTLY_DOMAIN ) )
		);	?>
	<div class="wrap">
		<div id="icon-link-manager" class="icon32"></div>
		<h2>WP Shortly <sub>v<?php echo WP_SHORTLY_VERSION; ?></sub></h2>
		<form action="options.php" method="post">
			<?php settings_fields( 'shortly_options' ); ?>
			<?php do_settings_sections( 'wp_shortly' ); ?>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php

}

function shortly_render_options_fields( $args ) {
	extract( $args );
	$name = isset( $name ) ? $name : $label_for;
	$description = isset( $description ) ? $description : '';
	switch ( $type ) {
		case 'text':
			$value = shortly_get_option( $name );
			?>
			<label>
				<input type="text" value="<?php echo $value; ?>" id="<?php echo $label_for; ?>" name="<?php echo WP_SHORTLY_SLUG ?>[<?php echo $name; ?>]" <?php if ( isset( $size ) ) echo 'size="' . ( $size+9 ) . '"' ?> <?php if ( isset( $placeholder ) ) echo 'placeholder="' . $placeholder . '"' ?>>
			</label>
			<p class="description">
				<?php echo $description; ?>
			</p>
			<?php
			break;
		
		default:
			break;
	}
}

/**
 * This public function allow template and theme customers to directly get Social Netforum Options
 * 
 * @param mixed $option  Description.
 * @param mixed $default Description.
 *
 * @access public
 *
 * @return mixed Value.
 */
function shortly_get_option( $option, $default=false ) {
	$options = get_option( WP_SHORTLY_SLUG );
	return isset( $options[$option] ) ? $options[$option] : $default;
}