<?php
$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

Timber::render( array( 'pages/' . $post->post_name . '.twig', 'pages/page.twig' ), $context );
