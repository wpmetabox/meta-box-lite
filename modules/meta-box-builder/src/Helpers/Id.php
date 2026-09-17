<?php
namespace MBB\Helpers;

class Id {
	/**
	 * Sanitize a slug/ID for meta boxes, settings pages, models, etc.
	 *
	 * Percent-encoded characters from sanitize_title() (CJK titles) break WordPress 7.1
	 * meta box tooltips that pass button markup through sprintf().
	 *
	 * @param string $id              Candidate ID or title.
	 * @param string $fallback_source Stable source for a hash fallback (usually the title).
	 */
	public static function sanitize( string $id, string $fallback_source = '' ): string {
		$sanitized = sanitize_title( $id );
		if ( '' === $sanitized || str_contains( $sanitized, '%' ) ) {
			$source = '' !== $fallback_source ? $fallback_source : $id;
			return 'mb-' . substr( md5( $source ), 0, 8 );
		}

		return $sanitized;
	}
}
