<?php
$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

if( $post->post_name == 'home' ):
  if(is_user_logged_in() ):
    wp_safe_redirect( site_url() . '/student-overview' );
    exit();
  endif;
  $context['include_captcha'] = $context['vars']['include_captcha'];
endif;

Timber::render( array( 'pages/' . $post->post_name . '.twig', 'pages/page.twig' ), $context );
