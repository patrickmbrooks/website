<?php
/**
 * Knowledge graph — multi-entity @graph JSON-LD.
 *
 * A connected graph rather than a single block, so search engines and the
 * answer engines that now sit in front of them can assemble the firm, its
 * attorneys, its site and each page into one entity instead of disconnected
 * fragments:
 *
 *   #firm       LegalService / Attorney (org) — phones, address, hours,
 *               areaServed, languages, sameAs
 *   #attorney-* Person entities, one per configured attorney
 *   #website    WebSite — publisher #firm, SearchAction
 *   #webpage    WebPage / AboutPage / ContactPage per URL — isPartOf
 *               #website, about #firm, dates live from the post
 *   breadcrumbs BreadcrumbList mirroring the page ancestry
 *
 * Everything the graph asserts about the firm comes from inc/schema-identity.php,
 * which derives it from the same Customizer fields the visible page renders.
 * Nothing here is hardcoded per firm: editing the office hours changes both
 * the page and the structured data, and there is no third copy to drift.
 *
 * Complements rather than duplicates an SEO plugin: this graph describes the
 * ENTITIES; titles, meta descriptions and Open Graph belong to Yoast, Rank
 * Math, or Docket Suite's SEO half. Docket Suite deliberately ships no JSON-LD
 * for exactly this reason.
 *
 * Toggle: Customizer → Brooks Law Firm → SEO & Schema.
 *
 * @package brooks-law
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stable entity IDs.
 *
 * @return array
 */
function brooks_law_graph_ids() {
	$home = home_url( '/' );

	$ids = array(
		'firm'    => $home . '#firm',
		'website' => $home . '#website',
		'logo'    => $home . '#logo',
	);

	foreach ( brooks_law_attorneys() as $person ) {
		$ids[ 'attorney:' . $person['slug'] ] = $home . '#attorney-' . $person['slug'];
	}

	return $ids;
}

/**
 * The firm entity.
 *
 * @param array $ids Entity IDs.
 * @return array
 */
function brooks_law_graph_firm( $ids ) {
	$address = brooks_law_parse_locality( (string) brooks_law_get_option( 'firm_city_state' ) );

	$firm = array(
		'@type'       => array( 'LegalService', 'Attorney' ),
		'@id'         => $ids['firm'],
		'name'        => brooks_law_get_option( 'firm_shortname' ),
		'description' => wp_strip_all_tags( (string) brooks_law_get_option( 'hero_lead' ) ),
		'url'         => home_url( '/' ),
		'telephone'   => brooks_law_tel( brooks_law_get_option( 'firm_phone_link', brooks_law_get_option( 'firm_phone' ) ) ),
		'email'       => brooks_law_get_option( 'firm_email' ),
		'priceRange'  => '$$',
	);

	$postal = array(
		'@type'          => 'PostalAddress',
		'streetAddress'  => brooks_law_get_option( 'firm_address' ),
		'addressCountry' => 'US',
	);
	if ( '' !== $address['locality'] ) {
		$postal['addressLocality'] = $address['locality'];
	}
	if ( '' !== $address['region'] ) {
		$postal['addressRegion'] = $address['region'];
	}
	if ( '' !== $address['postal'] ) {
		$postal['postalCode'] = $address['postal'];
	}
	$firm['address'] = $postal;

	$areas = array_values(
		array_filter(
			array_map( 'trim', explode( ',', (string) brooks_law_get_option( 'service_area' ) ) )
		)
	);
	if ( $areas ) {
		$served = array();
		foreach ( $areas as $area ) {
			// "County" and "Parish" are administrative areas; anything else
			// is treated as a place name, which schema.org accepts either way.
			$type     = preg_match( '/\b(county|parish|borough|region)\b/i', $area ) ? 'AdministrativeArea' : 'City';
			$served[] = array(
				'@type' => $type,
				'name'  => $area,
			);
		}
		$firm['areaServed'] = $served;
	}

	$languages = array_values(
		array_filter(
			array_map( 'trim', explode( ',', (string) brooks_law_get_option( 'firm_languages' ) ) )
		)
	);
	if ( $languages ) {
		$firm['knowsLanguage'] = $languages;
	}

	// Derived from the same line the footer and contact section print, so the
	// two can never disagree. Omitted entirely when the line does not parse.
	$hours = brooks_law_parse_hours( (string) brooks_law_get_option( 'firm_hours' ) );
	if ( $hours ) {
		$firm['openingHoursSpecification'] = $hours;
	}

	$services = brooks_law_graph_services( $ids );
	if ( $services ) {
		$firm['hasOfferCatalog'] = array(
			'@type'           => 'OfferCatalog',
			'name'            => __( 'Practice Areas', 'brooks-law-30-pro' ),
			'itemListElement' => $services,
		);
	}

	$people = brooks_law_attorneys();
	if ( $people ) {
		$employees = array();
		foreach ( $people as $person ) {
			$employees[] = array( '@id' => $ids[ 'attorney:' . $person['slug'] ] );
		}
		$firm['employee'] = $employees;
		$firm['founder']  = $employees[0];
	}

	$logo_id = (int) get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$logo_url = wp_get_attachment_image_url( $logo_id, 'full' );
		if ( $logo_url ) {
			$firm['logo'] = array(
				'@type' => 'ImageObject',
				'@id'   => $ids['logo'],
				'url'   => $logo_url,
			);
			$firm['image'] = array( '@id' => $ids['logo'] );
		}
	}

	$same_as  = array();
	$facebook = brooks_law_resolve_url( (string) brooks_law_get_option( 'firm_facebook' ) );
	if ( '' !== $facebook ) {
		$same_as[] = $facebook;
	}

	$extra = (string) brooks_law_get_option( 'schema_sameas', '' );
	if ( '' !== $extra ) {
		foreach ( preg_split( '/\r\n|\r|\n/', $extra ) as $line ) {
			$line = brooks_law_resolve_url( trim( $line ) );
			if ( '' !== $line ) {
				$same_as[] = $line;
			}
		}
	}
	if ( $same_as ) {
		$firm['sameAs'] = array_values( array_unique( $same_as ) );
	}

	return $firm;
}

/**
 * Live service list from the same options the homepage renders.
 *
 * @param array $ids Entity IDs.
 * @return array
 */
function brooks_law_graph_services( $ids ) {
	$out = array();

	for ( $i = 1; $i <= 3; $i++ ) {
		$title = trim( (string) brooks_law_get_option( "practice_{$i}_title" ) );

		if ( '' === $title ) {
			continue;
		}

		$service = array(
			'@type'       => 'Service',
			'name'        => $title,
			'description' => wp_strip_all_tags( (string) brooks_law_get_option( "practice_{$i}_desc" ) ),
			'provider'    => array( '@id' => $ids['firm'] ),
		);

		// Resolves a relative path OR passes an absolute URL through. The old
		// unconditional home_url() produced "https://site/https://other/".
		$url = brooks_law_resolve_url( (string) brooks_law_get_option( "practice_{$i}_url" ) );
		if ( '' !== $url ) {
			$service['url'] = $url;
		}

		$area = trim( (string) brooks_law_get_option( 'service_area' ) );
		if ( '' !== $area ) {
			$service['areaServed'] = $area;
		}

		$out[] = array(
			'@type'       => 'Offer',
			'itemOffered' => $service,
		);
	}

	return $out;
}

/**
 * Attorney Person entities.
 *
 * @param array $ids Entity IDs.
 * @return array[]
 */
function brooks_law_graph_people( $ids ) {
	$out = array();

	foreach ( brooks_law_attorneys() as $person ) {
		$entity = array(
			'@type'    => 'Person',
			'@id'      => $ids[ 'attorney:' . $person['slug'] ],
			'name'     => $person['name'],
			'worksFor' => array( '@id' => $ids['firm'] ),
		);

		if ( '' !== $person['title'] ) {
			$entity['jobTitle'] = $person['title'];
		}
		if ( ! empty( $person['knows'] ) ) {
			$entity['knowsAbout'] = $person['knows'];
		}
		if ( '' !== $person['alumni'] ) {
			$entity['alumniOf'] = array(
				'@type' => 'CollegeOrUniversity',
				'name'  => $person['alumni'],
			);
		}

		$url = brooks_law_resolve_url( $person['url'] );
		if ( '' !== $url ) {
			$entity['url'] = $url;
		}

		$out[] = $entity;
	}

	return $out;
}

/**
 * WebSite entity with SearchAction (sitelinks search box eligibility).
 *
 * @param array $ids Entity IDs.
 * @return array
 */
function brooks_law_graph_website( $ids ) {
	return array(
		'@type'           => 'WebSite',
		'@id'             => $ids['website'],
		'url'             => home_url( '/' ),
		'name'            => get_bloginfo( 'name' ),
		'publisher'       => array( '@id' => $ids['firm'] ),
		'inLanguage'      => get_bloginfo( 'language' ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => home_url( '/?s={search_term_string}' ),
			),
			'query-input' => 'required name=search_term_string',
		),
	);
}

/**
 * Canonical URL for the entity being described.
 *
 * Returns '' for any request that does not have one stable, indexable URL of
 * its own — search results, paginated archives, 404s. The previous version
 * fell through to home_url( $wp->request ), and since $wp->request is empty on
 * a search page, every search result published the FRONT PAGE'S EXACT @id
 * attached to a different title: the entity ambiguity this graph exists to
 * prevent.
 *
 * @return string
 */
function brooks_law_graph_canonical_url() {
	if ( is_404() || is_search() || is_paged() ) {
		return '';
	}

	if ( is_front_page() ) {
		return home_url( '/' );
	}

	if ( is_singular() ) {
		$permalink = get_permalink();
		return $permalink ? $permalink : '';
	}

	if ( is_home() ) {
		$blog_page = (int) get_option( 'page_for_posts' );
		if ( $blog_page ) {
			$permalink = get_permalink( $blog_page );
			return $permalink ? $permalink : '';
		}
		return '';
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$term_link = get_term_link( get_queried_object() );
		return is_string( $term_link ) ? $term_link : '';
	}

	return '';
}

/**
 * Backward-compatible alias.
 *
 * inc/faq-schema.php and third-party code call this name.
 *
 * @return string
 */
function brooks_law_current_url() {
	$url = brooks_law_graph_canonical_url();

	return '' !== $url ? $url : home_url( '/' );
}

/**
 * Per-page WebPage entity.
 *
 * @param array $ids Entity IDs.
 * @return array|null Null when this request has no canonical URL to describe.
 */
function brooks_law_graph_webpage( $ids ) {
	$url = brooks_law_graph_canonical_url();

	if ( '' === $url ) {
		return null;
	}

	$page = array(
		'@type'      => 'WebPage',
		'@id'        => $url . '#webpage',
		'url'        => $url,
		'name'       => wp_get_document_title(),
		'isPartOf'   => array( '@id' => $ids['website'] ),
		'about'      => array( '@id' => $ids['firm'] ),
		'inLanguage' => get_bloginfo( 'language' ),
	);

	if ( is_front_page() ) {
		$page['@type']      = array( 'WebPage', 'ProfilePage' );
		$page['mainEntity'] = array( '@id' => $ids['firm'] );
	}

	if ( is_singular() ) {
		$post = get_post();

		if ( $post instanceof WP_Post ) {
			$page['datePublished'] = get_the_date( 'c', $post );
			$page['dateModified']  = get_the_modified_date( 'c', $post );

			$excerpt = has_excerpt( $post ) ? get_the_excerpt( $post ) : '';
			if ( '' !== $excerpt ) {
				$page['description'] = wp_strip_all_tags( $excerpt );
			}

			if ( has_post_thumbnail( $post ) ) {
				$img = wp_get_attachment_image_url( get_post_thumbnail_id( $post ), 'full' );
				if ( $img ) {
					$page['primaryImageOfPage'] = array(
						'@type' => 'ImageObject',
						'url'   => $img,
					);
				}
			}

			$slug = (string) $post->post_name;
			if ( false !== strpos( $slug, 'contact' ) ) {
				$page['@type'] = 'ContactPage';
			} elseif ( false !== strpos( $slug, 'about' ) || false !== strpos( $slug, 'attorney' ) ) {
				$page['@type'] = 'AboutPage';
			}
		}
	}

	return $page;
}

/**
 * BreadcrumbList mirroring page ancestry.
 *
 * @return array|null
 */
function brooks_law_graph_breadcrumbs() {
	if ( is_front_page() || ! is_singular() ) {
		return null;
	}

	$post = get_post();
	if ( ! $post instanceof WP_Post ) {
		return null;
	}

	$url = brooks_law_graph_canonical_url();
	if ( '' === $url ) {
		return null;
	}

	$trail = array(
		array(
			'name' => __( 'Home', 'brooks-law-30-pro' ),
			'item' => home_url( '/' ),
		),
	);

	foreach ( array_reverse( get_post_ancestors( $post ) ) as $ancestor_id ) {
		$trail[] = array(
			'name' => get_the_title( $ancestor_id ),
			'item' => get_permalink( $ancestor_id ),
		);
	}

	$trail[] = array(
		'name' => get_the_title( $post ),
		'item' => get_permalink( $post ),
	);

	$items = array();
	foreach ( $trail as $index => $crumb ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $index + 1,
			'name'     => $crumb['name'],
			'item'     => $crumb['item'],
		);
	}

	return array(
		'@type'           => 'BreadcrumbList',
		'@id'             => $url . '#breadcrumbs',
		'itemListElement' => $items,
	);
}

/**
 * Assemble and print the graph.
 */
function brooks_law_output_graph() {
	if ( ! brooks_law_get_option( 'schema_enable' ) ) {
		return;
	}

	// Never mark up a page that does not exist, or a feed.
	if ( is_404() || is_feed() || is_robots() ) {
		return;
	}

	$ids   = brooks_law_graph_ids();
	$graph = array( brooks_law_graph_firm( $ids ) );

	foreach ( brooks_law_graph_people( $ids ) as $person ) {
		$graph[] = $person;
	}

	$graph[] = brooks_law_graph_website( $ids );

	$webpage = brooks_law_graph_webpage( $ids );
	if ( $webpage ) {
		$graph[] = $webpage;
	}

	$crumbs = brooks_law_graph_breadcrumbs();
	if ( $crumbs ) {
		$graph[] = $crumbs;
	}

	/**
	 * Filter the final graph.
	 *
	 * Lets a child theme or plugin append entities — reviews, videos, events —
	 * without touching this file. inc/faq-schema.php uses it.
	 *
	 * @param array $graph Entity array.
	 */
	$graph = apply_filters( 'brooks_law_schema_graph', $graph );

	$json = wp_json_encode(
		array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		),
		/*
		 * JSON_HEX_TAG escapes < and > as < / >, so a title or
		 * excerpt containing "</script>" cannot terminate the block early.
		 * JSON_UNESCAPED_SLASHES alone — which is what this used to pass —
		 * switched OFF the \/ escaping that normally provides that guarantee.
		 */
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG
	);

	if ( ! is_string( $json ) ) {
		return;
	}

	echo '<script type="application/ld+json">' . $json . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- wp_json_encode() with JSON_HEX_TAG.
}
add_action( 'wp_head', 'brooks_law_output_graph', 20 );

/* -------------------------------------------------------------------------
 * Extra Customizer fields for the graph
 * ---------------------------------------------------------------------- */

/**
 * The sameAs list. Attorney and area fields live in inc/schema-identity.php.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function brooks_law_graph_customize( $wp_customize ) {
	$wp_customize->add_setting(
		'schema_sameas',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);
	$wp_customize->add_control(
		'schema_sameas',
		array(
			'label'       => __( 'Firm profile URLs (one per line)', 'brooks-law-30-pro' ),
			'description' => __( 'Google Business Profile, Avvo, Justia, LinkedIn, Martindale, State Bar listing. Each one strengthens entity authority (sameAs).', 'brooks-law-30-pro' ),
			'type'        => 'textarea',
			'section'     => 'brooks_law_seo',
		)
	);

	$wp_customize->add_setting(
		'firm_languages',
		array(
			'default'           => 'en, es',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'firm_languages',
		array(
			'label'       => __( 'Languages spoken (comma separated codes)', 'brooks-law-30-pro' ),
			'description' => __( 'Two-letter codes, e.g. “en, es”.', 'brooks-law-30-pro' ),
			'type'        => 'text',
			'section'     => 'brooks_law_seo',
		)
	);
}
add_action( 'customize_register', 'brooks_law_graph_customize', 20 );
