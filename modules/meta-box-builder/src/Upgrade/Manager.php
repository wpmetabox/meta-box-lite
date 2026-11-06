<?php
namespace MBB\Upgrade;

class Manager {
	public function __construct() {
		$this->upgrade();
	}

	public function upgrade() {
		/**
		 * Get current version to check.
		 * - Make sure it <= MBB_VER, in case users already installed a newer version that will bypass the upgrade.
		 */
		$current_version = get_option( 'mbb_version', '1.0.0' );
		if ( version_compare( $current_version, MBB_VER, '>' ) ) {
			$current_version = MBB_VER;
		}

		$versions = [ '3.0.0', '3.0.1', '4.0.4', '5.6.1' ];
		foreach ( $versions as $version ) {
			if ( version_compare( $current_version, $version, '>=' ) ) {
				continue;
			}
			$class = __NAMESPACE__ . '\Ver' . str_replace( '.', '', $version );
			( new $class() )->migrate();
		}

		// Always update the DB version to the plugin version.
		if ( $current_version !== MBB_VER ) {
			update_option( 'mbb_version', MBB_VER );
		}
	}
}
