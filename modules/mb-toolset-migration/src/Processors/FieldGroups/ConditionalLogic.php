<?php
namespace MetaBox\TS\Processors\FieldGroups;

use MetaBox\Support\Arr;

class ConditionalLogic {
	private $settings;

	public function __construct( &$settings ) {
		$this->settings = &$settings;
	}

	public function migrate() {
		$groups = Arr::get( $this->settings, 'data.conditional_display' );
		if ( ! $groups ) {
			return;
		}

		$conditional_logic = [
			'type'     => 'visible',
			'relation' => Arr::get( $groups, 'relation', 'and' ),
			'when'     => [],
		];

		$rules = Arr::get( $groups, 'conditions' );

		foreach ( $rules as $rule ) {
			$id = uniqid();
			if ( $rule['operation'] === '===' ) {
				$operator = 'match';
			} elseif ( $rule['operation'] === '!==' ) {
				$operator = 'not match';
			} else {
				$operator = $rule['operation'];
			}

			$rule['id']       = $id;
			$rule['name']     = $rule['field'];
			$rule['operator'] = $operator;

			unset( $rule['field'] );
			unset( $rule['operation'] );

			$conditional_logic['when'][ $id ] = $rule;
		}

		$this->settings['conditional_logic'] = $conditional_logic;
	}
}
