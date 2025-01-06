<?php

namespace MBDI\Templates;

class WYSIWYG extends Base {
	public function render(): string {
		$value = $this->get_value();

		return do_shortcode( wpautop( $value ) );
	}
}
