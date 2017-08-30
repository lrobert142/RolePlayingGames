<?php
$context = Timber::get_context();
$post = Timber::query_post();
$context['post'] = $post;

if ( post_password_required( $post->ID ) ) {
	Timber::render( 'pages/single-password.twig', $context );
} else {
	Timber::render( array( 'pages/single-' . $post->ID . '.twig', 'pages/single-' . $post->post_type . '.twig', 'pages/single.twig' ), $context );
}
