<?php
namespace MBB\Upgrade;

use MBBParser\Parsers\MetaBox as Parser;
use WP_Post;
use WP_Query;

/**
 * Convert from data for AngularJS to React.
 * - JavaScript data stored in post meta "settings" and "fields" instead of "post_excerpt"
 * - PHP data stored in post meta "meta_box" instead of "post_content"
 */
class Ver404 {
	private $settings;
	private $fields;

	public function __construct() {
		$this->settings = new Ver404\Settings();
		$this->fields   = new Ver404\Fields();
	}

	public function migrate() {
		$query = new WP_Query( [
			'post_type'              => 'meta-box',
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		] );

		array_walk( $query->posts, [ $this, 'migrate_post' ] );
	}

	public function migrate_post( WP_Post $post ) {
		if ( ! $this->is_updatable( $post ) ) {
			return;
		}

		$data = [];

		// Update "settings" and "fields" for JavaScript.
		$data['settings']   = $this->settings->update( $post );
		$data['fields']     = $this->fields->update( $post );
		$data['post_title'] = $post->post_title;
		$data['post_name']  = $post->post_name;

		// Save parsed data for PHP.
		$parser = new Parser( $data );
		$parser->parse();
		$meta_box = $parser->get_settings();
		update_post_meta( $post->ID, 'meta_box', $meta_box );
	}

	private function is_updatable( WP_Post $post ) {
		// Update only field groups created in version < 4.
		if ( empty( $post->post_excerpt ) ) {
			return false;
		}

		// Ignore already updated field groups.
		$meta_box = get_post_meta( $post->ID, 'meta_box', true );
		return empty( $meta_box );
	}
}
