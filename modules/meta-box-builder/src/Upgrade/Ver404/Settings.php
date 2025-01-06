<?php
namespace MBB\Upgrade\Ver404;

use MetaBox\Support\Arr;
use MBB\Helpers\Data;

/**
 * Convert settings from data for AngularJS to React.
 */
class Settings extends Base {
	public function update( $post ) {
		$data     = json_decode( $post->post_excerpt, true );
		$settings = [];

		$this->update_location( $settings, $data );
		$this->update_include_exclude( $settings, $data );
		$this->update_show_hide( $settings, $data );
		$this->update_conditional_logic( $settings, $data );
		$this->update_block( $settings, $data );
		$this->update_custom_settings( $settings, $data );
		$this->update_custom_table( $settings, $data );

		$this->copy_data( $data, $settings, [ 'prefix', 'text_domain', 'tab_style', 'tab_wrapper' ] );

		update_post_meta( $post->ID, 'settings', $settings );

		return $settings;
	}

	private function update_location( &$settings, $data ) {
		// Object type.
		$for                     = Arr::get( $data, 'for', 'post_types' );
		$object_types            = [
			'post_types'     => 'post',
			'taxonomies'     => 'term',
			'settings_pages' => 'setting',
		];
		$settings['object_type'] = isset( $object_types[ $for ] ) ? $object_types[ $for ] : $for;

		if ( 'post' === $settings['object_type'] ) {
			$names = [ 'post_types', 'context', 'priority', 'style', 'default_hidden', 'autosave' ];
			$this->copy_data( $data, $settings, $names );

			// Show in media modal for attachments.
			$post_types = Arr::get( $settings, 'post_types', [ 'post' ] );
			if ( in_array( 'attachment', $post_types ) ) {
				$this->copy_data( $data, $settings, 'media_modal' );
			}
		}

		// Taxonomies.
		if ( 'term' === $settings['object_type'] ) {
			$this->copy_data( $data, $settings, 'taxonomies' );
		}

		// Settings pages.
		if ( 'setting' === $settings['object_type'] ) {
			$this->copy_data( $data, $settings, 'settings_pages' );
		}
	}

	private function update_include_exclude( &$settings, $data ) {
		$old = Arr::get( $data, 'includeexclude' );
		if ( empty( $old ) ) {
			return;
		}
		$new = [];
		$this->copy_data( $old, $new, [ 'type', 'relation' ] );
		unset( $old['type'], $old['relation'] );

		$rules = [];

		// is_child, custom.
		$names = [ 'is_child', 'custom' ];
		foreach ( $names as $name ) {
			$value = Arr::get( $old, $name );
			unset( $old[ $name ] );
			if ( empty( $value ) ) {
				continue;
			}
			$id           = uniqid();
			$rules[ $id ] = compact( 'id', 'name', 'value' );
		}

		// ID, parent.
		$names = [ 'ID', 'parent' ];
		foreach ( $names as $name ) {
			$value = Arr::get( $old, $name );
			unset( $old[ $name ] );
			if ( empty( $value ) ) {
				continue;
			}
			$label        = array_map( [ $this, 'get_post_title' ], $value );
			$id           = uniqid();
			$rules[ $id ] = compact( 'id', 'name', 'value', 'label' );
		}

		// user_id, edited_user_id.
		$names = [ 'user_id', 'edited_user_id' ];
		foreach ( $names as $name ) {
			$value = Arr::get( $old, $name );
			unset( $old[ $name ] );
			if ( empty( $value ) ) {
				continue;
			}
			$label        = array_map( [ $this, 'get_user_display_name' ], $value );
			$id           = uniqid();
			$rules[ $id ] = compact( 'id', 'name', 'value', 'label' );
		}

		// user_role, edited_user_role.
		$names = [ 'user_role', 'edited_user_role' ];
		foreach ( $names as $name ) {
			$value = Arr::get( $old, $name );
			unset( $old[ $name ] );
			if ( empty( $value ) ) {
				continue;
			}
			$label        = array_map( [ $this, 'get_user_role' ], $value );
			$id           = uniqid();
			$rules[ $id ] = compact( 'id', 'name', 'value', 'label' );
		}

		// template.
		$value = Arr::get( $old, 'template' );
		unset( $old['template'] );
		if ( $value ) {
			$label        = array_map( [ $this, 'get_template' ], $value );
			$id           = uniqid();
			$rules[ $id ] = compact( 'id', 'name', 'value', 'label' );
		}

		// Terms & parent terms.
		foreach ( $old as $name => $value ) {
			$label        = array_map( [ $this, 'get_term_name' ], $value );
			$id           = uniqid();
			$rules[ $id ] = compact( 'id', 'name', 'value', 'label' );
		}

		$new['rules']                = $rules;
		$settings['include_exclude'] = $new;
	}

	private function update_show_hide( &$settings, $data ) {
		$old = Arr::get( $data, 'showhide' );
		if ( empty( $old ) ) {
			return;
		}
		$new = [];
		$this->copy_data( $old, $new, [ 'type', 'relation' ] );
		unset( $old['type'], $old['relation'] );

		$rules = [];

		// template.
		$value = Arr::get( $old, 'template' );
		unset( $old['template'] );
		if ( $value ) {
			$label        = array_map( [ $this, 'get_template' ], $value );
			$id           = uniqid();
			$rules[ $id ] = compact( 'id', 'name', 'value', 'label' );
		}

		// post_format.
		$value = Arr::get( $old, 'post_format' );
		unset( $old['post_format'] );
		if ( $value ) {
			$label        = array_map( [ $this, 'get_post_format' ], $value );
			$id           = uniqid();
			$rules[ $id ] = compact( 'id', 'name', 'value', 'label' );
		}

		// Terms & parent terms.
		foreach ( $old as $name => $value ) {
			$label        = array_map( [ $this, 'get_term_name' ], $value );
			$id           = uniqid();
			$rules[ $id ] = compact( 'id', 'name', 'value', 'label' );
		}

		$new['rules']          = $rules;
		$settings['show_hide'] = $new;
	}

	private function update_block( &$settings, $data ) {
		$names = [
			'description',
			'icon_type',
			'icon',
			'icon_svg',
			'icon_background',
			'icon_foreground',
			'category',
			'keywords',
			'block_context',
			'supports',
			'render_with',
			'render_callback',
			'render_template',
			'render_code',
			'enqueue_style',
			'enqueue_script',
			'enqueue_assets',
		];
		$this->copy_data( $data, $settings, $names );
	}

	private function update_custom_table( $settings, $data ) {
		$old = Arr::get( $data, 'table' );
		if ( empty( $old ) ) {
			return;
		}
		$new                      = [
			'enable' => true,
			'name'   => $old,
		];
		$settings['custom_table'] = $new;
	}

	private function get_post_title( $post_id ) {
		return get_post( $post_id )->post_title;
	}

	private function get_user_display_name( $user_id ) {
		return get_userdata( $user_id )->display_name;
	}

	private function get_user_role( $role ) {
		global $wp_roles;

		$roles = $wp_roles->roles;
		return isset( $roles[ $role ] ) ? $roles[ $role ]['name'] : $role;
	}

	private function get_template( $file ) {
		$templates = Data::get_templates();

		// Group templates by file, which eliminates duplicates templates for multiple post types.
		$items = [];
		foreach ( $templates as $template ) {
			if ( empty( $s ) || false !== strpos( strtolower( $template['name'] ), $s ) ) {
				$items[ $template['file'] ] = $template['name'];
			}
		}

		return isset( $items[ $file ] ) ? $items[ $file ] : $file;
	}

	private function get_term_name( $term_id ) {
		return get_term( $term_id )->name;
	}

	private function get_post_format( $format ) {
		$items = Data::get_post_formats();
		return isset( $items[ $format ] ) ? $items[ $format ] : $format;
	}
}
