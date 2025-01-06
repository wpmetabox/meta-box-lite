<?php
namespace MBB\Relationships\Parsers;

use MBBParser\Parsers\Base;

class Field extends Base {
	public function parse() {
		$this->parse_array_attributes( 'query_args' )->remove_empty_values();
	}
}
