<?php
namespace MBB\Upgrade;

use MBB\LocalJson;

/**
 * Write Local JSON for settings pages and relationships that still live only in the database.
 *
 * These types register from JSON only, so objects without a file stop registering.
 * `Manager` runs this once when `mbb_version` is below 5.6.1.
 */
class Ver561 {
	public function migrate(): void {
		LocalJson::export_missing( [ 'mb-settings-page', 'mb-relationship' ] );
	}
}
