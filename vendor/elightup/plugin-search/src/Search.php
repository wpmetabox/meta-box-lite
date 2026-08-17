<?php
namespace eLightUp\PluginSearch;

class Search extends Base {
	/**
	 * Insert plugins whose keywords match the search term.
	 */
	public function suggests(): void {
		$term = $this->sanitize_search_term( $this->args->search );
		$data = $this->data();

		foreach ( $data as $plugin ) {
			if ( ! isset( $plugin['keywords'] ) || ! str_contains( $plugin['keywords'], $term ) ) {
				continue;
			}

			$this->insert( $plugin['slug'], $plugin['position'] );
		}
	}

	/**
	 * Sanitize the search term for keyword matching.
	 *
	 * @param string $term Raw search term.
	 */
	private function sanitize_search_term( string $term ): string {
		$term = strtolower( urldecode( $term ) );
		$term = preg_replace( '/[^a-z0-9 ]/', '', $term );

		// Remove strings that don't help matches.
		$term = trim( str_replace( [ 'free', 'wordpress' ], '', $term ) );
		$term = preg_replace( '/\s{2,}/', ' ', $term );

		return $term;
	}

	/**
	 * Get the plugins to suggest based on the search term.
	 */
	private function data(): array {
		return apply_filters( 'eps_search', [
			[
				'slug'     => 'slim-seo',
				'position' => 1,
				'keywords' => '
					seo, seo plugin, seo tools, seo suite, wordpress seo,
					yoast, yoast seo, rank math, rankmath, seopress, all in one seo, aiosco, aioseo, squirrel seo,
					google, google search console, bing, yandex, search engines, search engine optimization,
					sitemap, xml sitemap, sitemaps, xml sitemaps,
					schema, schemas, structured data, rich snippets, json ld, jsonld, microdata,
					meta tags, meta title, meta description, meta robots, robots, noindex, canonical, canonical url, open graph, opengraph, twitter cards, social media, facebook, twitter, linkedin,
					breadcrumbs, breadcrumb,
					redirect, redirects, redirection, 301 redirect, 404, 404 monitor, 404 tracking,
					content analysis, seo analysis, readability, seo audit, focus keyword, seo keywords,
					internal links, internal linking, broken links, link checker, link health, link attributes, nofollow, sponsored, ugc,
					image alt, alt text, image seo,
					rss feed, feed, content protection, copyright,
					header code, footer code, header footer, insert code, insert headers and footers, head and footer, header scripts, footer scripts, javascript code, css code, html code,
					google analytics, google tag manager, facebook pixel, facebook pixels, tracking code, tracking scripts, adsense, conversion pixels,
					import export, migration, migrate, migrator,
					ai, openai, chatgpt, ai writer, ai content, meta title generator,
					',
			],
			[
				'slug'     => 'falcon',
				'position' => 1,
				'keywords' => '
					optimize, optimization, optimizer, speed, performance, faster, fast, slow, bloat, lightweight,
					wp rocket, wprocket, litespeed cache, wp super cache, w3 total cache, cache enabler, autoptimize, perfmatters, wp optimize,
					cache, caching, page cache, html cache, static cache,
					cleanup, clean, cleaner, bloat, remove, disable, disable emojis, disable comments, disable heartbeat,
					heartbeat, emojis, emoji, embeds, embed, xmlrpc, xml rpc, rest api, jquery migrate, query string, version,
					security, secure, protect, protection, firewall, limit login, login attempts, login limit, brute force, spam, comment spam, honeypot, force login, maintenance mode, maintenance,
					database, database cleanup, database optimizer, db, revisions, drafts, transients, orphaned, optimize tables, wp optimize,
					admin, dashboard, admin bar, dashboard widgets, widgets, login screen, site icon,
					gutenberg, block editor, classic editor, disable gutenberg, texturize, smart quotes,
					email, smtp, mail, notification, notifications, password, new user, update emails,
					media, thumbnails, image, images, exif, big image,
					auto updates, cron, wp cron, external requests, application passwords, privacy,
					',
			],
			[
				'slug'     => 'meta-box',
				'position' => 1,
				'keywords' => '
					custom fields, custom field, custom fields plugin, add custom fields,
					advanced custom fields, acf, acf free, pods, cmb2, custom fields framework,
					meta box, metabox, meta boxes, custom meta box,
					custom post types, custom post type, post types, post type, cpt, cpt ui, custom post type ui,
					custom taxonomies, custom taxonomy, taxonomies, taxonomy,
					fields, field, fields builder, field builder, form fields, input fields,
					wysiwyg, text editor, rich text, image, images, gallery, file upload, upload, uploads, date picker, time picker, date time, color picker, checkbox, radio, select, dropdown, map, google map, oembed, video, audio,
					clone, cloning, group, groups, repeatable, repeater, settings page, options page, theme options, settings panel,
					term meta, user meta, comment meta, profile, profiles, user fields, users,
					',
			],
			[
				'slug'     => 'mb-custom-post-type',
				'position' => 2,
				'keywords' => '
					custom post types, custom post type, post type, post types, cpt, cpt ui, custom post type ui, register post type, create post type,
					custom taxonomies, custom taxonomy, taxonomy, taxonomies, register taxonomy, create taxonomy,
					content types, cms, ui, export php code, export code, code generator,
					pods, custom content types,
					',
			],
		] );
	}

	/**
	 * Validate that the search term is a non-empty string with at least 3 characters.
	 */
	protected function validate(): bool {
		return ! empty( $this->args->search ) && is_string( $this->args->search ) && strlen( $this->args->search ) >= 3;
	}
}
