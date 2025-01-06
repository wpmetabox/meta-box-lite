<?php
namespace MBB\Upgrade\Ver404;

use MetaBox\Support\Arr;

class Base {
	protected function update_conditional_logic( &$new_data, $data ) {
		$old = Arr::get( $data, 'logic' );
		if ( empty( $old ) ) {
			return;
		}
		$new         = [];
		$new['type'] = Arr::get( $old, 'visibility' );
		$this->copy_data( $old, $new, 'relation' );

		$rules = [];
		$when  = Arr::get( $old, 'when', [] );
		foreach ( $when as $rule ) {
			$id           = uniqid();
			$rules[ $id ] = [
				'id'       => $id,
				'name'     => $rule[0],
				'operator' => $rule[1],
				'value'    => $rule[2],
			];
		}

		$new['when']                   = $rules;
		$new_data['conditional_logic'] = $new;
		unset( $new_data['logic'] );
	}

	protected function update_custom_settings( $new_data, $data ) {
		$old = Arr::get( $data, 'attrs' );
		if ( empty( $old ) ) {
			return;
		}
		$new = [];
		foreach ( $old as $item ) {
			$id         = uniqid();
			$new[ $id ] = array_merge( [ 'id' => $id ], $item );
		}
		$new_data['custom_settings'] = $new;
	}

	protected function copy_data( $source, &$destination, $name ) {
		if ( is_string( $name ) ) {
			$value = Arr::get( $source, $name );
			if ( null !== $value ) {
				$destination[ $name ] = $value;
			}
			return;
		}
		foreach ( $name as $n ) {
			$value = Arr::get( $source, $n );
			if ( null !== $value ) {
				$destination[ $n ] = $value;
			}
		}
	}
}
