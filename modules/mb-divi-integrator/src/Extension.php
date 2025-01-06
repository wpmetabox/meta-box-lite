<?php

namespace MBDI;

/**
 * The Divi Integrator extension.
 */
class Extension extends \DiviExtension {


	/**
	 * The gettext domain for the extension's translations.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $gettext_domain = 'mb-divi-integrator';

	/**
	 * The extension's WP Plugin name.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $name = 'mb-divi-integrator';

	/**
	 * The extension's version
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	public function __construct( $name = 'mb-divi-integrator', $args = [] ) {
		$this->plugin_dir     = MBDI_PATH . 'includes';
		$this->plugin_dir_url = plugin_dir_url( $this->plugin_dir );

		add_action( 'wp_ajax_mb_divi_integrator_get_fields', [ $this, 'ajax_get_fields' ] );

		parent::__construct( $name, $args );
	}

	public static function get_fields() {
		$layouts = get_posts([
			'post_type'      => 'et_pb_layout',
			'posts_per_page' => -1,
		]);

		$layout_options    = [];
		$layout_options[0] = esc_html__( 'Select a layout', 'mbdi' );

		foreach ( $layouts as $layout ) {
			$layout_options[ $layout->ID ] = $layout->post_title;
		}

		// Get all cloneable fields from Meta Box.
		$cloneable_query = new FieldQuery([
			'clone' => true,
		]);

		$cloneable_fields        = $cloneable_query->pluck( 'name', 'id' );
		$cloneable_field_options = array_merge( [ '' => esc_html__( 'Select a field', 'mbdi' ) ], $cloneable_fields );

		// Get all fields from Meta Box.
		$query = new FieldQuery([
			'not_type' => [ 'group' ],
		]);

		$fields        = $query->pluck( 'name', 'id' );
		$field_options = array_merge( [ '' => esc_html__( 'Select a field', 'mbdi' ) ], $fields );

		return compact( 'layout_options', 'cloneable_field_options', 'field_options' );
	}

	/**
	 * Get the extension's fields.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function ajax_get_fields() {
		wp_send_json_success( self::get_fields() );
	}
}
