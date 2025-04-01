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

	private function unparse_location() {
		if ( isset( $this->post_types ) ) {
			$this->post_types = array_filter( (array) $this->post_types );
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
