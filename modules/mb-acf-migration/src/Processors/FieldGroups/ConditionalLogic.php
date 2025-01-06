<?php
namespace MetaBox\ACF\Processors\FieldGroups;

class ConditionalLogic {
	private $settings;

	public function __construct( &$settings ) {
		$this->settings = &$settings;
	}

	public function migrate() {
		if ( ! isset( $this->settings['conditional_logic'] ) ) {
			return;
		}

		$groups = $this->settings['conditional_logic'];

		if ( ! is_array( $groups ) ) {
			unset( $this->settings['conditional_logic'] );
			return;
		}

		$rules = $this->migrate_rules( $groups );

		$conditional_logic = [
			'type'     => 'visible',
			'relation' => count( $groups ) === 1 ? 'and' : 'or',
			'when'     => [],
		];
		foreach ( $rules as $rule ) {
			$id         = uniqid();
			$rule['id'] = $id;

			$conditional_logic['when'][ $id ] = $rule;
		}

		$this->settings['conditional_logic'] = $conditional_logic;
	}

	private function migrate_rules( $groups ) {
		$items = [];
		if ( count( $groups ) === 1 ) {
			// 1 group.
			$items = reset( $groups );
		} else {
			// Many groups: take first rule from each group.
			foreach ( $groups as $group ) {
				$items[] = reset( $group );
			}
		}

		$rules = [];
		foreach ( $items as $rule ) {
			$value    = null;
			$operator = $rule['operator'];

			if ( $operator === '==pattern' ) {
				continue;
			}

			switch ( $operator ) {
				case '!=empty':
					$operator = '!=';
					$value    = '';
					break;
				case '==empty':
					$operator = '=';
					$value    = '';
					break;
				case '==contains':
					$operator = 'contains';
					break;
				case '==':
					$operator = '=';
					break;
			}

			$rules[] = [
				'name'     => $this->get_name( $rule['field'] ),
				'operator' => $operator,
				'value'    => null === $value ? $rule['value'] : $value,
			];
		}

		return $rules;
	}

	private function get_name( $field ) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT post_excerpt FROM $wpdb->posts WHERE post_name=%s", $field ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Error.
	}
}
