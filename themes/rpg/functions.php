<?php
include_once 'acf.php';

if (function_exists('acf_add_options_page')) {
    acf_add_options_page();
}

if (!class_exists('Timber')) {
    add_action('admin_notices', function () {
        echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url(admin_url('plugins.php#timber')) . '">' . esc_url(admin_url('plugins.php')) . '</a></p></div>';
    });

    add_filter('template_include', function ($template) {
        return get_stylesheet_directory() . '/static/no-timber.html';
    });

    return;
}

Timber::$dirname = array('templates', 'views');

class StarterSite extends TimberSite
{

    function __construct()
    {
        add_theme_support('post-formats');
        add_theme_support('post-thumbnails');
        add_theme_support('menus');
        add_theme_support('html5', array('comment-list', 'comment-form', 'search-form', 'gallery', 'caption'));

        add_filter('timber_context', array($this, 'add_to_context'));
        add_filter('get_twig', array($this, 'add_to_twig'));

        add_action('init', array($this, 'add_roles'));
        add_action('init', array($this, 'register_post_types'));
        add_action('init', array($this, 'register_taxonomies'));
        add_action('login_init', array($this, 'lost_password_redirect'));
        add_action('init', array($this, 'handle_form_submission'));
        add_action('init', array($this, 'redirect_login'));
        add_action('init', array($this, 'create_posttype'));

        parent::__construct();
    }

    function register_post_types()
    {
        //this is where you can register custom post types
    }

    function register_taxonomies()
    {
        //this is where you can register custom taxonomies
    }

    // Add custom user roles here
    function add_roles()
    {
        add_role('student', 'Student', array('read' => true, 'edit' => false, 'delete' => false, 'level_0' => true));
        add_role('teacher', 'Teacher', array(
            'read' => true,
            'edit' => true,
            'publish' => true,
            'delete' => false,
            'edit_posts'=> true,
            'edit_published_posts'=> true,
            'publish_posts'=> true,
            'level_1' => true));
    }

    function create_posttype()
    {

        register_post_type('teacher_subjects',
            array(
                'labels' => array(
                    'name' => __('Subjects'),
                    'singular_name' => __('Subject')
                ),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'subjects'),
                'supports' => array('title', 'editor', 'author', 'custom-fields')
            )
        );
    }

    function add_to_context($context)
    {
        // Setup menus and the site itself
        $context['menu'] = new TimberMenu();
        $context['site'] = $this;
        // Gets the ACF options for use in Twig templates
        $context['options'] = get_fields('options');
        // Get any errors from the $GLOBALS array for use in Twig
        $context['errors'] = $GLOBALS['errors'];
        // Include generic vars for use in Twig
        $context['vars'] = $GLOBALS['vars'];
        return $context;
    }

    function add_to_twig($twig)
    {
        $twig->addExtension(new Twig_Extension_StringLoader());
        return $twig;
    }

    //when logging out, redirect to custom login page rather than wp-login.php
    function redirect_login()
    {
        $page_viewed = basename($_SERVER['REQUEST_URI']);

        if ($page_viewed == "wp-login.php?loggedout=true") {
            wp_redirect(home_url(), 301);
            exit;
        }
    }

    function lost_password_redirect()
    {
        if (isset($_GET['action'])
            && is_user_logged_in()
            && in_array($_GET['action'], array('lostpassword', 'retrievepassword', 'rp'))
        ) {
            wp_safe_redirect(home_url(), 301);
            exit;
        }
    }

    // Function used to handle any generic form submission. Forms are generally
    // differentiated via an arbitrary 'token'.
    function handle_form_submission()
    {
        // Login form
        if (isset($_POST)
            && isset($_POST['login_token'])
            && isset($_POST['username'])
            && isset($_POST['password'])
        ):
            $this->handle_login($_POST);
        endif;
    }

    // Function used to handle login form submission.
    function handle_login($login_values)
    {
        // If reCAPTCHA was submitted, but isn't valid, set an error and return.
        if (isset($login_values['g-recaptcha-response']) && !validate_recaptcha($login_values['g-recaptcha-response'])):
            $GLOBALS['errors']['login_form'] = "reCAPTCHA not filled out.";
            $GLOBALS['vars']['include_captcha'] = true;
            return;
        endif;

        // Get submitted credentials and attempt to login
        $creds = array(
            'user_login' => $login_values['username'],
            'user_password' => $login_values['password'],
            'remember' => true
        );
        $user = wp_signon($creds, true);

        // Login failed
        if (is_wp_error($user)):
            $failed_attempts = isset($_COOKIE['failed_login_attempts']) ? $_COOKIE['failed_login_attempts'] : 0;
            $failed_attempts = $failed_attempts + 1;
            setcookie('failed_login_attempts', $failed_attempts, strtotime('+1 hour'));

            // On 3 failure, set var to show captcha
            if ($failed_attempts >= 3):
                $GLOBALS['vars']['include_captcha'] = true;
            endif;
            $GLOBALS['errors']['login_form'] = "Invalid username or password!";

        else:
            // If valid, invalidate cookie and redirect to a new location based on user role
            setcookie('failed_login_attempts', 0, time() - (15 * 60));
            redirect_to_overview_by_user_role($user->ID);
        endif;
    }
}

new StarterSite();

/********************/
/* HELPER FUNCTIONS */
/********************/

// Validate reCAPTCHA
function validate_recaptcha($recaptcha_response)
{
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $post_fields = 'secret=' . get_field('recaptcha_secret_key', 'options') . '&response=' . $recaptcha_response;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response)->success;
}

// Redirects a user to an overview page based on their user role.
function redirect_to_overview_by_user_role($user_id)
{
    $user_meta = get_userdata($user_id);
    $user_roles = $user_meta->roles;

    if (in_array('student', $user_roles) == 1):
        wp_safe_redirect(site_url() . '/student-overview');
        exit;
    elseif (in_array('teacher', $user_roles) == 1):
        wp_safe_redirect(site_url() . '/teacher-overview');
        exit;
    endif;
}

// Display debug items in browser console.
function debug_to_console($data)
{
    $output = $data;
    if (is_array($output)):
        $output = implode(',', $output);
    endif;

    echo "<script>console.log( 'Debug Objects: " . $output . "' );</script>";
}