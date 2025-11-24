<?php
namespace MBBParser\Unparsers;

class Settings extends Base {
	/**
	 * Allow these settings to be empty.
	 * @var array
	 */
	protected $empty_keys = [ 'post_types', 'taxonomies', 'settings_pages' ];

	public function unparse() {
		$this->add_default( 'context', 'normal' )
			->unparse_boolean_values()
			->unparse_numeric_values()
			->unparse_location()
			->unparse_conditional_logic();
	}

	private function unparse_location(): self {
		if ( ! empty( $this->taxonomies ) ) {
			$this->object_type = 'term';
			$this->ensure_array( 'taxonomies' );

			unset( $this->post_types );
			unset( $this->settings_pages );
			unset( $this->type );

			unset( $this->priority );
			unset( $this->style );
			unset( $this->closed );
			unset( $this->revision );
			unset( $this->context );
			unset( $this->default_hidden );

			return $this;
		}

		if ( ! empty( $this->settings_pages ) ) {
			$this->object_type = 'setting';
			$this->ensure_array( 'settings_pages' );

			unset( $this->post_types );
			unset( $this->taxonomies );
			unset( $this->type );

			return $this;
		}

		if ( in_array( $this->type, [ 'user', 'comment' ], true ) ) {
			$this->object_type = $this->type;

			unset( $this->post_types );
			unset( $this->taxonomies );
			unset( $this->settings_pages );

			unset( $this->priority );
			unset( $this->style );
			unset( $this->closed );
			unset( $this->revision );
			unset( $this->context );
			unset( $this->default_hidden );

			return $this;
		}

		if ( $this->type === 'block' ) {
			$this->object_type = $this->type;

			unset( $this->post_types );
			unset( $this->taxonomies );
			unset( $this->settings_pages );

			unset( $this->priority );
			unset( $this->style );
			unset( $this->closed );
			unset( $this->revision );
			unset( $this->default_hidden );

			return $this;
		}

		$this->object_type = 'post';
		$this->ensure_array( 'post_types' );

		unset( $this->taxonomies );
		unset( $this->settings_pages );
		unset( $this->type );

		return $this;
	}

	public function replace_variables( $text ) {
		if ( empty( $text ) ) {
			return $text;
		}

		return strtr( $text, [
			'{{ site.path }}'  => wp_normalize_path( ABSPATH ),
			'{{ site.url }}'   => untrailingslashit( home_url( '/' ) ),
			'{{ theme.path }}' => wp_normalize_path( get_stylesheet_directory() ),
			'{{ theme.url }}'  => get_stylesheet_directory_uri(),
		] );
	}

	private function ensure_array( string $key ): void {
		$value      = $this->$key;
		$this->$key = array_filter( (array) $value );
	}
}
