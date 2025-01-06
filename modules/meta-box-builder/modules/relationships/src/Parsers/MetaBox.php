<?php
namespace MBB\Relationships\Parsers;

use MBBParser\Parsers\Base;

class MetaBox extends Base {
	public function parse() {
		$this->remove_default( 'context', 'side' )
			->remove_default( 'priority', 'low' )
			->remove_default( 'style', 'default' )
			->remove_empty_values();
	}
}
