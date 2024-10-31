<?php
/*
* Plugin Name: NGO Menu deactivate
* Plugin URI: https://ngo-portal.org
* Description: Inaktiverar onödiga menyer för att göra det lättare att hantera och uppdatera webbplatserna i portalen. Ska vara aktiverad på föreningssidorna. Kan vara aktiv på portalen.
* Version: 1.1.1
* Author: George Bredberg
* Author URI: https://datagaraget.se
* Text Domain: ngo-menu-deactivate
* Domain Path: /languages
* License GPLv3
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

/*
* This plugin removes a lot of unnecessary menus, submenus and widgets.
* It is easy to comment out what you do not want to remove, or uncomment what you do want to remove.
* The settings represents the default setup for NGO-portal. Change at will.
*/

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	echo 'This file should not be accessed directly!';
	exit; // Exit if accessed directly
}

// Exit if we are not in admin-pages
if ( ! is_admin() ) {
	return;
}

// Load translation. This plugin does not really output anything, but for the sake of it..
add_action( 'plugins_loaded', 'ngomd_load_plugin_textdomain' );
 function ngomd_load_plugin_textdomain() {
   load_plugin_textdomain( 'ngo-menu-deactivate', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}

///////////////////////////////////////////////////
// Debug, to show the name of the loaded widgets //
///////////////////////////////////////////////////

// add_action( 'wp_footer', 'ngomd_get_widgets' ); // works best, but does not show up if the footer is removed..
// add_action( 'admin_menu', 'ngomd_get_widgets' ); // Hides a bit, but copy and past to textfile to read
function ngomd_get_widgets() {
  if ( empty ( $GLOBALS['wp_widget_factory'] ) )
   return;

  $widgets = array_keys( $GLOBALS['wp_widget_factory']->widgets );
  print '<pre>$widgets = ' . esc_html( var_export( $widgets, TRUE ) ) . '</pre>';
}

//////////////////////// START ADD_ACTION ////////////////////////
if(!function_exists('wp_get_current_user')) { require_once(ABSPATH . "wp-includes/pluggable.php"); }

add_action( 'wp_loaded', 'ngomd_cleanup' );

function ngomd_cleanup() {
	// Run for everyone except super-admin
	if(!is_super_admin()){
		add_action( 'admin_menu', 'ngomd_remove_menus4admin' );
		add_action( 'admin_menu', 'ngomd_remove_menu_settings_permalink', 99 ); // Should probably run even for super-admin..
		add_action( 'admin_menu', 'ngomd_remove_menu_contact_integration', 99 );
		add_action( 'admin_menu', 'ngomd_remove_meta_boxes' ); // removes metaboxes from posts and links
		remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );	// prevent admin color option to show up in wp-admin -> User
	}

	// Run for everyone except administrators
	if (!(current_user_can('manage_options'))) {
		add_action( 'admin_menu', 'ngomd_remove_menus4editor' );
		add_action( 'admin_menu', 'ngomd_remove_wpcf7' );
		add_action( 'admin_menu', 'ngomd_remove_loginizer' );
		add_action( 'wp_before_admin_bar_render', 'ngomd_remove_admin_bar_links' );
	}
}

// Run for everyone
add_action( 'do_meta_boxes', 'ngomd_remove_dashboard_widgets' );
add_action( 'widgets_init', 'ngomd_unregister_akismet_widget', 11 );
add_action( 'widgets_init', 'ngomd_unregister_default_widgets', 11 );

///////////////////////// START SCRIPT SECTION /////////////////////////

/* Wordpress default */
// Removes menu items for everyone except superadmin
function ngomd_remove_menus4admin(){
	remove_menu_page( 'tools.php' );                    //Tools
	remove_menu_page( 'plugins.php' );                  //Plugins
	remove_submenu_page( 'index.php', 'my-sites.php' ); // My-sites
}

// Removes menu items for everyone except admin and superadmin
function ngomd_remove_menus4editor() {
	remove_menu_page('options-general.php');  //Settings menu
	remove_menu_page( 'themes.php' );         //Appearance
	remove_menu_page( 'users.php' );          //Users
	remove_menu_page( 'link-manager.php' );   //Links, should only be activated on portal-site
}

//Remove submenu item permalink from settings
function ngomd_remove_menu_settings_permalink(){
	remove_submenu_page('options-general.php','options-permalink.php');  //settings->permalink
}

// Removes widgets on the site-admin landing page
function ngomd_remove_dashboard_widgets() {
	remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );   // Right Now (I korthet)
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' ); // Recent Comments
	remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );  // Incoming Links
	remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );   // Plugins
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );  // Quick Press "Snabbutkast"
	remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );  // Recent Drafts
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );   // WordPress blog  (Nyheter från WP)
	remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );   // Other WordPress News
	remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );   // Aktivity (Aktivitet)
	remove_action( 'welcome_panel', 'wp_welcome_panel' ); // This is an action that needs to be removed to get rid of WP Welcome screen
	//'dashboard-network' should(?) be second param for WPMU, but it does not work.. above works...
}

// Remove metaboxes from post and link
function ngomd_remove_meta_boxes() {
	remove_meta_box( 'linktargetdiv', 'link', 'normal' );
	remove_meta_box( 'linkxfndiv', 'link', 'normal' );
	remove_meta_box( 'linkadvanceddiv', 'link', 'normal' );
//	remove_meta_box( 'postexcerpt', 'post', 'normal' ); //Excerpt
	remove_meta_box( 'trackbacksdiv', 'post', 'normal' );
	remove_meta_box( 'postcustom', 'post', 'normal' );
	remove_meta_box( 'commentstatusdiv', 'post', 'normal' );
	remove_meta_box( 'commentsdiv', 'post', 'normal' );
	remove_meta_box( 'revisionsdiv', 'post', 'normal' );
	remove_meta_box( 'authordiv', 'post', 'normal' );
	remove_meta_box( 'sqpt-meta-tags', 'post', 'normal' );
//	remove_meta_box( 'slugdiv', 'post', 'normal' ); //Permalink
}

// Unregister widgets
function ngomd_unregister_default_widgets() {
	// Down are default wordpress widgets
	unregister_widget('WP_Widget_Pages');
	unregister_widget('WP_Widget_Calendar'); // Kalender över inlägg
	unregister_widget('WP_Widget_Archives');
	unregister_widget('WP_Widget_Links');
	unregister_widget('WP_Widget_Meta'); //rss, login-links etc.
	unregister_widget('WP_Widget_Search');
	// unregister_widget('WP_Widget_Text');
	// unregister_widget('WP_Widget_Categories');
	// unregister_widget('WP_Widget_Recent_Posts');
	unregister_widget('WP_Widget_Recent_Comments');
	unregister_widget('WP_Widget_RSS');
	unregister_widget('WP_Widget_Tag_Cloud');
	//unregister_widget('WP_Nav_Menu_Widget'); // Anpassad meny
}

/* WPCF7 */
/* Remove Contact Form 7 totally from dashboard menu items if not admin */
function ngomd_remove_wpcf7() {
	remove_menu_page( 'wpcf7' );
}

// Remove Contact -> Integration submenu
function ngomd_remove_menu_contact_integration(){
	remove_submenu_page('wpcf7', 'wpcf7-integration');  // contact->integration
}

/* Loginizer */
/* Remove Loginizer totally from dashboard menu items if not admin */
function ngomd_remove_loginizer() {
	remove_menu_page( 'loginizer' );
}

/* Akismet */
function ngomd_unregister_akismet_widget() {
	unregister_widget('Akismet_Widget');
}

////////////////////////////// Clean up the admin menu  //////////////////////////////////

function ngomd_remove_admin_bar_links() {
	global $wp_admin_bar;

	//Could be removed by uncommenting here, but instead they are replaced by ngo_branding.
	//$wp_admin_bar->remove_menu('wp-logo'); // Removes WP Logo and submenus completely, to remove individual items, use the below mentioned codes
	//$wp_admin_bar->remove_menu('about'); // 'About WordPress'
	//$wp_admin_bar->remove_menu('wporg'); // 'WordPress.org'
	//$wp_admin_bar->remove_menu('documentation'); // 'Documentation'
	//$wp_admin_bar->remove_menu('support-forums'); // 'Support Forums'
	//$wp_admin_bar->remove_menu('feedback'); // 'Feedback'

	//Remove Site Name Items
	//$wp_admin_bar->remove_menu('site-name'); // Removes Site Name and submenus completely, To remove individual items, use the below mentioned codes
	//$wp_admin_bar->remove_menu('view-site'); // 'Visit Site'
	$wp_admin_bar->remove_menu('dashboard'); // 'Dashboard'
	$wp_admin_bar->remove_menu('themes'); // 'Themes'
	$wp_admin_bar->remove_menu('widgets'); // 'Widgets'
	$wp_admin_bar->remove_menu('menus'); // 'Menus'

	// Remove Comments Bubble
	$wp_admin_bar->remove_menu('comments');

	//Remove Update Link if theme/plugin/core updates are available
	$wp_admin_bar->remove_menu('updates');

	//Remove '+ New' Menu Items
	$wp_admin_bar->remove_menu('new-content'); // Removes '+ New' and submenus completely, to remove individual items, use the below codelines
	//$wp_admin_bar->remove_menu('new-post'); // 'Post' Link
	//$wp_admin_bar->remove_menu('new-media'); // 'Media' Link
	//$wp_admin_bar->remove_menu('new-link'); // 'Link' Link
	//$wp_admin_bar->remove_menu('new-page'); // 'Page' Link
	//$wp_admin_bar->remove_menu('new-user'); // 'User' Link

	// Remove my-sites menu item
	$wp_admin_bar->remove_menu('my-sites');

	// Remove 'Howdy, username' Menu Items
	//$wp_admin_bar->remove_menu('my-account'); // Removes 'Howdy, username' and Menu Items
	//$wp_admin_bar->remove_menu('user-actions'); // Removes Submenu Items Only
	$wp_admin_bar->remove_menu('user-info'); // 'username'
	$wp_admin_bar->remove_menu('edit-profile'); // 'Edit My Profile'
	//$wp_admin_bar->remove_menu('logout'); // 'Log Out'
}

/////////////////////////////////
// Remove Update Notifications //
/////////////////////////////////

// Only show update notifications for Admins.
if(!function_exists('wp_get_current_user')) { require_once(ABSPATH . "wp-includes/pluggable.php"); }
function ngomd_hide_update_notes(){
//	global $user_login;
//	if (!current_user_can('update_plugins')) { // checks to see if current user can update plugins
	if(!is_super_admin()){
		add_action( 'init', create_function( '$a', "remove_action( 'init', 'wp_version_check' );" ), 2 );
		add_filter( 'pre_option_update_core', create_function( '$a', "return null;" ) );
		add_filter( 'pre_site_transient_update_core', create_function( '$a', "return null;" ) );
		add_filter( 'auto_update_plugin', '__return_false' );
		add_filter( 'auto_update_theme', '__return_false' );
	}
}
add_action('admin_init', 'ngomd_hide_update_notes');

// Since the plugin adrotator-ngo is a stripped down version of AdRotator, updating AdRotator *might* cause unforseen issues if functions or calls change names, so we don't want to update this automatically
function ngomd_adr_disable_plugin_updates( $value ) {
	if( isset( $value->response ) ) {
		unset( $value->response['adrotate/adrotate.php'] );
	}
	return $value;
}
add_filter( 'site_transient_update_plugins', 'ngomd_adr_disable_plugin_updates' );

// Since we depend on Event-organiser to work with our "child"-plugin we update this manually so we don't break anything.
function ngomd_eo_disable_plugin_updates( $value ) {
	if( isset( $value->response ) ) {
		unset( $value->response['event-organiser/event-organiser.php'] );
	}
	return $value;
}
add_filter( 'site_transient_update_plugins', 'ngomd_eo_disable_plugin_updates' );

?>
