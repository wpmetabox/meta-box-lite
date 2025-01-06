<?php
namespace MBB\RestApi\ThemeCode;

class GroupVars {
	private static $stack = [];

	/**
	 * Get variables for:
	 * - the current group variable
	 * - the current group item variable (for cloneable group)
	 * - the parent group variable
	 */
	public static function get_current_group_vars( bool $clone ): array {
		$nested_level = count( self::$stack );

		$clone_map = [
			0 => [ '$groups', '$group', '' ],
			1 => [ '$subgroups', '$subgroup', '$group' ],
			2 => [ '$subgroups2', '$subgroup2', '$subgroup' ],
			// 3 => [ '$subgroups3', '$subgroup3', '$subgroup2' ], // And so on
		];
		$clone_default = [
			'$subgroups' . $nested_level,
			'$subgroup' . $nested_level,
			'$subgroup' . ( $nested_level - 1 ),
		];

		$non_clone_map = [
			0 => [ '$group', '$group', '' ],
			1 => [ '$subgroup', '$subgroup', '$group' ],
			2 => [ '$subgroup2', '$subgroup2', '$subgroup' ],
			// 3 => [ '$subgroup3', '$subgroup3', '$subgroup2' ], // And so on
		];
		$non_clone_default = [
			'$subgroup' . $nested_level,
			'$subgroup' . $nested_level,
			'$subgroup' . ( $nested_level - 1 ),
		];

		if ( $clone ) {
			$vars = $clone_map[ $nested_level ] ?? $clone_default;
		} else {
			$vars = $non_clone_map[ $nested_level ] ?? $non_clone_default;
		}

		self::$stack[] = $vars;

		return $vars;
	}

	/**
	 * Get current group item variable to refer to when showing sub-field value.
	 */
	public static function get_current_group_item_var(): string {
		$current = end( self::$stack );
		return $current[1] ?? '';
	}

	public static function pop(): void {
		array_pop( self::$stack );
	}
}
