<?php
$context = Timber::get_context();
$context['posts'] = Timber::get_posts();
$templates = array( 'pages/index.twig' );

if ( is_home() ):
	$context['include_captcha'] = $context['vars']['include_captcha'];
	array_unshift( $templates, 'pages/home.twig' );
endif;

Timber::render( $templates, $context );
