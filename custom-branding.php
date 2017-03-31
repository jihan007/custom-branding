<?php
/*
Plugin Name: Custom Branding
Description: Plugin for white labelling WordPress branding
Author: Jihan Ahmed
Author URI: http://www.wpunmarked.com
Version: 1.0.0
*/

if( ! class_exists( 'Smashing_Updater' ) ){
	include_once( plugin_dir_path( __FILE__ ) . 'updater.php' );
}

$updater = new Smashing_Updater( __FILE__ );
$updater->set_username( 'jihan007' );
$updater->set_repository( 'custom-branding' );
/*
	$updater->authorize( 'abcdefghijk1234567890' ); // Your auth code goes here for private repos
*/
$updater->initialize();

// Start Main Plugin
class Smashing_Fields_Plugin {

    public function __construct() {
    	// Hook into the admin menu
    	add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ) );

        // Add Settings and Fields
    	add_action( 'admin_init', array( $this, 'setup_sections' ) );
    	add_action( 'admin_init', array( $this, 'setup_fields' ) );
    }

    public function create_plugin_settings_page() {
    	// Add the menu item and page
    	$page_title = 'Custom Branding Settings';
    	$menu_title = 'Custom Branding';
    	$capability = 'manage_options';
    	$slug = 'smashing_fields';
    	$callback = array( $this, 'plugin_settings_page_content' );
    	$icon = 'dashicons-admin-plugins';
    	$position = 100;

    	add_menu_page( $page_title, $menu_title, $capability, $slug, $callback, $icon, $position );
    }

    public function plugin_settings_page_content() {?>
    	<div class="wrap">
    		<h2>Custom Branding Settings</h2><?php
            if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ){
                  $this->admin_notice();
            } ?>
    		<form method="POST" action="options.php">
                <?php
                    settings_fields( 'smashing_fields' );
                    do_settings_sections( 'smashing_fields' );
                    submit_button();
                ?>
    		</form>
    	</div> <?php
    }
    
    public function admin_notice() { ?>
        <div class="notice notice-success is-dismissible">
            <p>Your settings have been updated!</p>
        </div><?php
    }

    public function setup_sections() {
        add_settings_section( 'our_first_section', 'WP Login Logo URL', array( $this, 'section_callback' ), 'smashing_fields' );
    }

    public function section_callback( $arguments ) {
    	switch( $arguments['id'] ){
    		case 'our_first_section':
    			echo 'Enter the url of the logo to replace the wp login logo';
    			break;
    	}
    }

    public function setup_fields() {
        $fields = array(
        	array(
        		'uid' => 'cb_text_field',
        		'label' => 'Login Logo URL',
        		'section' => 'our_first_section',
        		'type' => 'url',
        		'placeholder' => '',
        		'helper' => '',
        		'supplimental' => '',
        	)
        );
    	foreach( $fields as $field ){

        	add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callback' ), 'smashing_fields', $field['section'], $field );
            register_setting( 'smashing_fields', $field['uid'] );
    	}
    }

    public function field_callback( $arguments ) {

        $value = get_option( $arguments['uid'] );

        if( ! $value ) {
            $value = $arguments['default'];
        }

        switch( $arguments['type'] ){
            case 'url':
                printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value );
                break;
        }

        if( $helper = $arguments['helper'] ){
            printf( '<span class="helper"> %s</span>', $helper );
        }

        if( $supplimental = $arguments['supplimental'] ){
            printf( '<p class="description">%s</p>', $supplimental );
        }

    }
	
}
new Smashing_Fields_Plugin();

//Register the stylesheets for the admin area.
function load_custom_wp_admin_style($hook) {
        if($hook != 'toplevel_page_smashing_fields') {
                return;
        }
        wp_enqueue_style( 'custom_wp_admin_css', plugins_url('css/admin-style.css', __FILE__) );
}
add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style' );

// Change the login logo
function custom_branding_login_logo() { ?>
    <style type="text/css">
        #login h1 a, .login h1 a {
        background-image: url(<?php echo get_option('cb_text_field'); ?>);
		height:65px;
		width:320px;
		background-size: 320px 65px;
		background-repeat: no-repeat;
		padding-bottom: 30px;
        }
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'custom_branding_login_logo' );

// Change the URL of the WordPress login logo
function custom_branding_login_logo_url() {
    return home_url('/');
}
add_filter( 'login_headerurl', 'custom_branding_login_logo_url' );

// Change the Title of the WordPress login logo
function custom_branding_login_logo_url_title() {
    return get_bloginfo( 'title' );
}
add_filter( 'login_headertitle', 'custom_branding_login_logo_url_title' );


// Remove menu page from admin panel
//add_action( 'admin_menu', 'cb_remove_admin_menus' );
function cb_remove_admin_menus() {
    // don't do anything if the user can publish posts
    if ( current_user_can( 'manage_network' ) ) {
        return;
    }
    // remove these items from the admin menu
    remove_menu_page( 'users.php' );          // Posts
    remove_menu_page( 'index.php' );        // Media
    remove_menu_page( 'tools.php' );         // Tools
    remove_menu_page( 'edit-comments.php' ); // Comments
	remove_menu_page( 'options-general.php' ); // Settings

}

// Restrict page access to certain admin menus
add_action( 'current_screen', 'tcd_restrict_admin_pages' );
function tcd_restrict_admin_pages() {
    // don't do anything if the user can publish posts
    if ( current_user_can( 'manage_network' ) ) {
        return;
    }
    // retrieve the current page's ID
    $current_screen_id = get_current_screen()->id;
    // determine which screens are off limits
    $restricted_screens = array(
        'users',
		'user-new',
		'profile',
        'upload',
        'tools',
		'import',
		'export',
        'edit-comments',
		'options-general',
		'options-writing',
		'options-reading',
		'options-discussion',
		'options-media',
		'options-permalink',
		'update-core',
    );

    // Restrict page access
    foreach ( $restricted_screens as $restricted_screen ) {

        // compare current screen id against each restricted screen
        if ( $current_screen_id === $restricted_screen ) {
            wp_die( __( 'You are not allowed to access this page.', 'tcd' ) );
        }

    }

}





?>