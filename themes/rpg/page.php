<?php
$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

if( $post->post_name == 'home' ):
  $context['include_captcha'] = $context['vars']['include_captcha'];
endif;

Timber::render( array( 'pages/' . $post->post_name . '.twig', 'pages/page.twig' ), $context );
