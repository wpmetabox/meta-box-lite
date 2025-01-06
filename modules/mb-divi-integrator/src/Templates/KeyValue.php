<?php

namespace MBDI\Templates;

class KeyValue extends Base {
	public function render(): string {
		$value = $this->get_value();

		$value = array_map(function ( $item ) {
			return $item[0] . ': ' . $item[1];
		}, $value);

		return implode( ', ', $value );
	}
}
