<?php
namespace MBCPT;

use MetaBox\Support\Data;

class Edit {
	private $post_type;

	public function __construct( $post_type ) {
		$this->post_type = $post_type;
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	public function enqueue_scripts() {
		if ( ! $this->is_screen() ) {
			return;
		}

		wp_enqueue_style( $this->post_type, MB_CPT_URL . 'assets/style.css', [ 'wp-components' ], MB_CPT_VER );
		wp_enqueue_style( 'font-awesome', MB_CPT_URL . 'assets/fontawesome/css/all.min.css', [], '6.6.0' );
		wp_enqueue_style( 'wp-edit-post' );

		wp_enqueue_script( 'mbcpt-edit', MB_CPT_URL . 'assets/edit.js', [], filemtime( MB_CPT_DIR . '/assets/edit.js' ), true );

		$object = str_replace( 'mb-', '', $this->post_type );
		wp_enqueue_code_editor( [ 'type' => 'application/x-httpd-php' ] );

		$asset = require MB_CPT_DIR . "/assets/build/$object.asset.php";
		wp_enqueue_script( $this->post_type, MB_CPT_URL . "assets/build/$object.js", $asset['dependencies'], $asset['version'], true );
		wp_localize_script( $this->post_type, 'MBCPT', $this->js_vars() );

		$path = '';
		if ( defined( 'META_BOX_LITE_DIR' ) ) {
			$path = META_BOX_LITE_DIR . '/languages/mb-custom-post-type';
		}
		if ( defined( 'META_BOX_AIO_DIR' ) ) {
			$path = META_BOX_AIO_DIR . '/languages/mb-custom-post-type';
		}
		wp_set_script_translations( $this->post_type, 'mb-custom-post-type', $path );
	}

	private function js_vars(): array {
		$post = get_post();

		$vars = [
			'icons'           => Data::get_dashicons(),
			'settings'        => json_decode( $post->post_content, true ),
			'reservedTerms'   => $this->get_reserved_terms(),
			'action'          => get_current_screen()->action,
			'url'             => admin_url( 'edit.php?post_type=' . get_current_screen()->id ),
			'add'             => admin_url( 'post-new.php?post_type=' . get_current_screen()->id ),
			'status'          => $post->post_status,
			'author'          => get_the_author_meta( 'display_name', (int) $post->post_author ),
			'trash'           => get_delete_post_link(),
			'published'       => get_the_date( 'F d, Y' ) . ' ' . get_the_time( 'g:i a' ),
			'modifiedtime'    => get_post_modified_time( 'F d, Y g:i a', true, null, true ),
			'saving'          => __( 'Saving...', 'mb-custom-post-type' ),
			'upgrade'         => ! $this->is_premium_user(),
			'allCapabilities' => $this->get_all_capabilities(),
		];

		if ( 'mb-post-type' === get_current_screen()->id ) {
			$taxonomies = Data::get_taxonomies();
			$options    = [];
			foreach ( $taxonomies as $slug => $taxonomy ) {
				$options[ $slug ] = sprintf( '%s (%s)', $taxonomy->labels->singular_name, $slug );
			}
			$vars['taxonomies']            = $options;
			$vars['icon_type']             = [
				[
					'value' => 'dashicons',
					'label' => esc_html__( 'Dashicons', 'mb-custom-post-type' ),
				],
				[
					'value' => 'svg',
					'label' => esc_html__( 'SVG', 'mb-custom-post-type' ),
				],
				[
					'value' => 'custom',
					'label' => esc_html__( 'Custom URL', 'mb-custom-post-type' ),
				],
				[
					'value' => 'font_awesome',
					'label' => esc_html__( 'Font Awesome', 'mb-custom-post-type' ),
				],
			];
			$vars['menu_position_options'] = $this->get_menu_position_options();
			$vars['show_in_menu_options']  = $this->get_show_in_menu_options();
		}

		if ( 'mb-taxonomy' === get_current_screen()->id ) {
			$post_types = Data::get_post_types();
			$options    = [];
			foreach ( $post_types as $slug => $post_type ) {
				$options[ $slug ] = sprintf( '%s (%s)', $post_type->labels->singular_name, $slug );
			}
			$vars['types'] = $options;
		}

		return $vars;
	}

	private function is_screen() {
		$screen = get_current_screen();
		return 'post' === $screen->base && $this->post_type === $screen->post_type;
	}

	private function is_premium_user(): bool {
		if ( ! class_exists( 'MetaBox\Updater\Option' ) || ! class_exists( 'MetaBox\Updater\Checker' ) ) {
			return false;
		}
		$update_option  = new \MetaBox\Updater\Option();
		$update_checker = new \MetaBox\Updater\Checker( $update_option );
		return $update_checker->has_extensions();
	}

	private function get_show_in_menu_options(): array {
		global $menu;

		$options = [
			[
				'value' => 'true',
				'label' => esc_html__( 'Show as top-level menu', 'mb-custom-post-type' ),
			],
			[
				'value' => 'false',
				'label' => esc_html__( 'Do not show in the admin menu', 'mb-custom-post-type' ),
			],
		];
		foreach ( $menu as $params ) {
			if ( ! empty( $params[0] ) && ! empty( $params[2] ) ) {
				$options[] = [
					'value' => $params[2],
					// Translators: %s is the main menu label.
					'label' => sprintf( __( 'Show as sub-menu of %s', 'mb-custom-post-type' ), $this->strip_span( $params[0] ) ),
				];
			}
		}
		return $options;
	}

	private function get_menu_position_options(): array {
		global $menu;

		$positions = [
			[
				'value' => '',
				'label' => __( 'Default', 'mb-custom-post-type' ),
			],
		];
		foreach ( $menu as $position => $params ) {
			if ( ! empty( $params[0] ) ) {
				$positions[] = [
					'value' => $position,
					'label' => $this->strip_span( $params[0] ),
				];
			}
		}

		return $positions;
	}

	/**
	 * Strip span tag from HTML.
	 *
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	private function strip_span( $html ): string {
		return preg_replace( '@<span .*>.*</span>@si', '', $html );
	}

	/**
	 * Get reserved terms.
	 *
	 * @return string[]
	 */
	private function get_reserved_terms(): array {
		return [
			'action',
			'attachment',
			'attachment_id',
			'author',
			'author_name',
			'calendar',
			'cat',
			'category',
			'category__and',
			'category__in',
			'category__not_in',
			'category_name',
			'comments_per_page',
			'comments_popup',
			'custom',
			'customize_messenger_channel',
			'customized',
			'cpage',
			'day',
			'debug',
			'embed',
			'error',
			'exact',
			'feed',
			'fields',
			'hour',
			'link_category',
			'm',
			'minute',
			'monthnum',
			'more',
			'name',
			'nav_menu',
			'nonce',
			'nopaging',
			'offset',
			'order',
			'orderby',
			'p',
			'page',
			'page_id',
			'paged',
			'pagename',
			'pb',
			'perm',
			'post',
			'post__in',
			'post__not_in',
			'post_format',
			'post_mime_type',
			'post_status',
			'post_tag',
			'post_type',
			'posts',
			'posts_per_archive_page',
			'posts_per_page',
			'preview',
			'robots',
			's',
			'search',
			'second',
			'sentence',
			'showposts',
			'static',
			'status',
			'subpost',
			'subpost_id',
			'tag',
			'tag__and',
			'tag__in',
			'tag__not_in',
			'tag_id',
			'tag_slug__and',
			'tag_slug__in',
			'taxonomy',
			'tb',
			'term',
			'terms',
			'theme',
			'title',
			'type',
			'types',
			'w',
			'withcomments',
			'withoutcomments',
			'year',
		];
	}

	/**
	 * Get all capabilities.
	 *
	 * @return string[]
	 */
	private function get_all_capabilities(): array {
		global $wp_roles;

		$capabilities = [];
		foreach ( $wp_roles->roles as $role ) {
			$capabilities = array_merge( $capabilities, array_keys( $role['capabilities'] ) );
		}

		return array_values( array_unique( $capabilities ) );
	}
}
