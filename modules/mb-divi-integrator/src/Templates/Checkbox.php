<?php

namespace MBDI\Templates;

class Checkbox extends Base {
	public function render(): string {
		$value = $this->get_value();

		$value = et_builder_i18n( $value ? 'Yes' : 'No' );

		return $value;
	}
}
