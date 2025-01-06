<?php

namespace MBDI\Templates;

class CustomHtml extends Base {
	public function render(): string {
		return $this->get_value();
	}
}
