<?php
$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

if( $post->post_name == 'home' ):
  if(is_user_logged_in() ):
    redirect_to_overview_by_user_role( get_current_user_id() );
  endif;

  $context['include_captcha'] = $context['vars']['include_captcha'];
endif;

Timber::render( array( 'pages/' . $post->post_name . '.twig', 'pages/page.twig' ), $context );
