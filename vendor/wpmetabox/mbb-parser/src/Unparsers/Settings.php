<?php
namespace MBBParser\Unparsers;

class Settings extends Base {
	// Allow these settings to be empty.
	protected $empty_keys = [ 'post_types', 'taxonomies', 'settings_pages' ];

	public function unparse() {
		$this->add_default( 'context', 'normal' )
			->unparse_boolean_values()
			->unparse_numeric_values()
			->unparse_location()
			->unparse_conditional_logic();
	}

	private function unparse_location(): self {
		// Add missing object type if not set.
		if ( in_array( $this->type, [ 'user', 'comment', 'block' ], true ) ) {
			$this->object_type = $this->type;
		}

		$object_type = $this->object_type ?: 'post';

		if ( $object_type === 'post' ) {
			unset( $this->taxonomies );
			unset( $this->settings_pages );
			unset( $this->type );
			if ( isset( $this->post_types ) ) {
				$this->post_types = array_filter( (array) $this->post_types );
			}

			return $this;
		}

		unset( $this->post_types );
		unset( $this->priority );
		unset( $this->style );
		unset( $this->position );
		unset( $this->closed );
		unset( $this->revision );

		if ( $object_type === 'term' ) {
			unset( $this->settings_pages );
			unset( $this->type );
			unset( $this->context );
			return $this;
		}

		if ( $object_type === 'setting' ) {
			unset( $this->taxonomies );
			unset( $this->type );
			unset( $this->context );
			return $this;
		}

		// block, user, comment

		unset( $this->taxonomies );
		unset( $this->settings_pages );

		if ( ! in_array( $object_type, [ 'user', 'comment' ], true ) ) {
			unset( $this->context );
		}

		return $this;
	}

	public function replace_variables( $string ) {
		if ( empty( $string ) ) {
			return $string;
		}

		return strtr( $string, [
			'{{ site.path }}'  => wp_normalize_path( ABSPATH ),
			'{{ site.url }}'   => untrailingslashit( home_url( '/' ) ),
			'{{ theme.path }}' => wp_normalize_path( get_stylesheet_directory() ),
			'{{ theme.url }}'  => get_stylesheet_directory_uri(),
		] );
	}
}
