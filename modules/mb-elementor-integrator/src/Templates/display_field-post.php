<?php
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$post = get_post( $data );
echo esc_html( $post->post_title );
