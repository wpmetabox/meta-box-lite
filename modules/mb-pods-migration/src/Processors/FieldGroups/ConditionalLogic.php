<?php
namespace MetaBox\Pods\Processors\FieldGroups;

use MetaBox\Support\Arr;

class ConditionalLogic {
	private $settings;

	public function __construct( &$settings ) {
		$this->settings = &$settings;
	}

	public function migrate() {
		$groups = Arr::get( $this->settings, 'conditional_logic' );
		if ( ! $groups ) {
			return;
		}

		$type  = ( Arr::get( $groups, 'action' ) == 'show' ) ? 'visible' : 'hidden';
		$logic = ( Arr::get( $groups, 'action' ) == 'all' ) ? 'and' : 'or';

		$conditional_logic = [
			'type'     => $type,
			'relation' => $logic,
			'when'     => [],
		];

		$rules = Arr::get( $groups, 'rules' );

		foreach ( $rules as $rule ) {
			$id               = uniqid();
			$rule['id']       = $id;
			$rule['name']     = $rule['field'];
			$rule['operator'] = $rule['compare'];

			unset( $rule['field'] );
			unset( $rule['compare'] );

			$conditional_logic['when'][ $id ] = $rule;
		}

		$this->settings['conditional_logic'] = $conditional_logic;
	}
}
