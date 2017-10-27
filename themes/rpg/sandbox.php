<?php
$args = array(
    'label' => __('movies', 'twentythirteen'),
    'description' => __('Movie news and reviews', 'twentythirteen'),
    'labels' => $labels,
    // Features this CPT supports in Post Editor
    'supports' => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields',),
    // You can associate this CPT with a taxonomy or custom taxonomy.
    'taxonomies' => array('genres'),
    /* A hierarchical CPT is like Pages and can have
    * Parent and child items. A non-hierarchical CPT
    * is like Posts.
    */
    'hierarchical' => false,
    'public' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'show_in_nav_menus' => true,
    'show_in_admin_bar' => true,
    'menu_position' => 5,
    'can_export' => true,
    'has_archive' => true,
    'exclude_from_search' => false,
    'publicly_queryable' => true,
    'capability_type' => 'page',
);

// Registering your Custom Post Type
register_post_type('movies', $args);

}

/* Hook into the 'init' action so that the function
* Containing our post type registration is not
* unnecessarily executed.
*/

add_action('init', 'custom_post_type', 0);