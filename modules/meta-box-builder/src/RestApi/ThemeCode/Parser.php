<?php
namespace MBB\RestApi\ThemeCode;

use MBBParser\Parsers\Base;
use MetaBox\Support\Arr;

class Parser extends Base {
	private $theme_code;

	public function parse() {
		$object_type      = $this->settings['object_type'];
		$map              = [
			'setting' => "'" . $this->get_settings_page_option_name( Arr::get( $this->settings, 'settings_pages.0', '' ) ) . "'",
			'term'    => 'get_queried_object_id()',
			'comment' => '$comment_id',
			'user'    => 'get_current_user_id()',
		];
		$this->theme_code = [
			'args'        => $object_type === 'post' ? [] : [ 'object_type' => $object_type ],
			'object_type' => $object_type,
			'object_id'   => $map[ $object_type ] ?? '',
			'prefix'      => $this->settings['prefix'],
		];

		return $this;
	}

	private function get_settings_page_option_name( string $id ): string {
		$settings_pages = apply_filters( 'mb_settings_pages', [] );
		$settings_pages = array_filter( $settings_pages, function ( $args ) use ( $id ) {
			return $args['id'] === $id;
		} );
		if ( empty( $settings_pages ) ) {
			return $id;
		}
		$settings_page = reset( $settings_pages );
		return $settings_page['option_name'] ?? $id;
	}

	public function get_settings(): array {
		return $this->theme_code ?: [];
	}
}
