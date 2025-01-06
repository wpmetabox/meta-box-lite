<?php

namespace MBDI\Templates;

class Video extends Base {
	public function render(): string {
		$video = $this->get_value();

		if ( empty( $video ) ) {
			return '';
		}

		$videos = array_map(function ( $video ) {
			return '<li>' . wp_video_shortcode([
				'src'     => $video['src'],
				'poster'  => $video['image']['src'],
				'preload' => 'metadata',
				'width'   => $video['dimensions']['width'],
				'height'  => $video['dimensions']['height'],
				'class'   => 'mbdi-video',
			]) . '</li>';
		}, $video);

		return '<ul class="mbdi-videos">' . implode( '', $videos ) . '</ul>';
	}
}
