<?php

if( function_exists('acf_add_options_page') ) {
	acf_add_options_page();
}

if ( ! class_exists( 'Timber' ) ) {
	add_action( 'admin_notices', function() {
		echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url( admin_url( 'plugins.php#timber' ) ) . '">' . esc_url( admin_url( 'plugins.php') ) . '</a></p></div>';
	});

	add_filter('template_include', function($template) {
		return get_stylesheet_directory() . '/static/no-timber.html';
	});

	return;
}

Timber::$dirname = array('templates', 'views');

class StarterSite extends TimberSite {

	function __construct() {
		add_theme_support( 'post-formats' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'menus' );
		add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption' ) );

		add_filter( 'timber_context', array( $this, 'add_to_context' ) );
		add_filter( 'get_twig', array( $this, 'add_to_twig' ) );

		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		add_action( 'init', array( $this, 'handle_form_submission' ) );

		parent::__construct();
	}

	function register_post_types() {
		//this is where you can register custom post types
	}

	function register_taxonomies() {
		//this is where you can register custom taxonomies
	}

	function add_to_context( $context ) {
		// Gets the ACF options for use in Twig templates
		$context['options'] = get_fields('options');
		// Setup menus and the site itself
		$context['menu'] = new TimberMenu();
		$context['site'] = $this;
		return $context;
	}

	function add_to_twig( $twig ) {
		$twig->addExtension( new Twig_Extension_StringLoader() );
		return $twig;
	}

	function handle_form_submission() {
		if (isset($_POST)
			&& isset($_POST['login_token'])
			&& isset($_POST['login_token'] != '')
			&& isset($_POST['username'])
			&& isset($_POST['password'])
		):
			$creds = array(
				'user_login'    => $_POST['username'],
				'user_password' => $_POST['password'],
				'remember'      => true
			);
			$user = wp_signon( $creds, true );

			if ( is_wp_error( $user ) ):
				$GLOBALS['errors']['login_form'] = "Invalid username or password!";
			else:
				wp_safe_redirect( home_url() );
				exit;
			endif;
		endif;
	}

}

new StarterSite();
