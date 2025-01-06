<?php

namespace MBDI\Templates;

class Heading extends Base {
	public function render(): string {
		$field = $this->get_field();

		return "<h3>{$field['name']}</h3>\n";
	}
}
