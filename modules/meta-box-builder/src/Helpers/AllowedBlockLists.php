<?php
namespace MBB\Helpers;

use WP_Block_Type_Registry;

class AllowedBlockLists {
	public const OPTION_NAME        = 'mbb_allowed_block_lists';
	public const INITIALIZED_OPTION = 'mbb_allowed_block_lists_initialized';

	public static function get_lists(): array {
		return (array) get_option( self::OPTION_NAME, [] );
	}

	public static function update_lists( array $lists ): bool {
		return (bool) update_option( self::OPTION_NAME, $lists );
	}

	public static function get_list( string $id ): ?array {
		$lists = self::get_lists();
		return $lists[ $id ] ?? null;
	}

	public static function update_list( string $id, string $name, array $blocks ): bool {
		$lists        = self::get_lists();
		$lists[ $id ] = [
			'name'   => $name,
			'blocks' => self::filter_valid_blocks( $blocks ),
		];
		return self::update_lists( $lists );
	}

	public static function delete_list( string $id ): bool {
		$lists = self::get_lists();
		unset( $lists[ $id ] );
		return self::update_lists( $lists );
	}

	public static function seed_defaults(): void {
		if ( get_option( self::INITIALIZED_OPTION ) ) {
			return;
		}

		$defaults = [
			'basic'  => [
				'name'   => __( 'Basic', 'meta-box-builder' ),
				'blocks' => [
					'core/paragraph',
					'core/heading',
					'core/image',
					'core/list',
					'core/list-item',
					'core/quote',
				],
			],
			'text'   => [
				'name'   => __( 'Text', 'meta-box-builder' ),
				'blocks' => [
					'core/paragraph',
					'core/heading',
					'core/list',
					'core/list-item',
					'core/quote',
				],
			],
			'layout' => [
				'name'   => __( 'Layout', 'meta-box-builder' ),
				'blocks' => [
					'core/group',
					'core/columns',
					'core/cover',
					'core/media-text',
					'core/paragraph',
					'core/heading',
					'core/image',
					'core/list',
					'core/list-item',
					'core/quote',
				],
			],
		];

		update_option( self::OPTION_NAME, $defaults );
		update_option( self::INITIALIZED_OPTION, true );
	}

	public static function filter_valid_blocks( array $blocks ): array {
		$registry = WP_Block_Type_Registry::get_instance();

		return array_filter( $blocks, static function ( $block ) use ( $registry ) {
			return $registry->is_registered( $block );
		} );
	}

	public static function generate_id( string $name ): string {
		$id      = sanitize_title( $name );
		$lists   = self::get_lists();
		$counter = 2;

		while ( isset( $lists[ $id ] ) ) {
			$id = sanitize_title( $name ) . '-' . $counter;
			++$counter;
		}

		return $id;
	}
}
