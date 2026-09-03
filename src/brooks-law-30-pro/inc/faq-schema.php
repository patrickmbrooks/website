<?php
/**
 * Brooks Law 3.2.1 — FAQPage schema.
 *
 * The cost pages and the practice-area pages already end in a run of question
 * headings with the answer underneath. This reads that structure and emits
 * FAQPage schema from it, so the questions become eligible for rich results
 * without anyone having to maintain a second copy of the answers.
 *
 * It appends to the existing graph through the brooks_law_schema_graph filter,
 * so inc/schema-graph.php is untouched.
 *
 * @package Brooks_Law
 * @since   3.2.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pull question-and-answer pairs out of a post's content.
 *
 * A heading counts as a question when its text ends in a question mark. The
 * answer is every paragraph and list that follows it, up to the next heading of
 * any level. Anything else in between is ignored rather than guessed at.
 *
 * @param string $content Raw post content.
 * @return array[] Each: question, answer.
 */
function brooks_law_extract_faqs( $content ) {
	$content = (string) $content;
	if ( '' === trim( $content ) ) {
		return array();
	}

	// Block comments would otherwise show up inside the answers.
	$content = preg_replace( '/<!--.*?-->/s', '', $content );

	$faqs = array();

	// Split on headings, keeping the level and the heading text.
	$chunks = preg_split(
		'/<h([2-4])\b[^>]*>(.*?)<\/h\1>/is',
		$content,
		-1,
		PREG_SPLIT_DELIM_CAPTURE
	);

	// $chunks: [ before, level, heading, body, level, heading, body, ... ]
	$total = count( $chunks );
	for ( $i = 1; $i + 2 <= $total; $i += 3 ) {
		$question = trim( wp_strip_all_tags( $chunks[ $i + 1 ] ) );
		$question = html_entity_decode( $question, ENT_QUOTES, 'UTF-8' );

		if ( '' === $question || '?' !== substr( $question, -1 ) ) {
			continue;
		}

		$body = isset( $chunks[ $i + 2 ] ) ? $chunks[ $i + 2 ] : '';

		// Keep only the prose blocks; skip images, tables, and embeds.
		$answer = '';
		if ( preg_match_all( '/<(p|ul|ol)\b[^>]*>(.*?)<\/\1>/is', $body, $matches ) ) {
			$answer = implode( ' ', $matches[0] );
		}

		$answer = trim( wp_strip_all_tags( $answer ) );
		$answer = html_entity_decode( $answer, ENT_QUOTES, 'UTF-8' );
		$answer = preg_replace( '/\s+/u', ' ', $answer );

		if ( '' === $answer ) {
			continue;
		}

		$faqs[] = array(
			'question' => $question,
			'answer'   => $answer,
		);
	}

	return $faqs;
}

/**
 * Append a FAQPage entity to the schema graph.
 *
 * Two pairs is the floor — a single question is not an FAQ page, and marking
 * one up as though it were invites a manual action rather than a rich result.
 *
 * @param array $graph Existing entities.
 * @return array
 */
function brooks_law_add_faq_schema( $graph ) {
	if ( ! is_singular() || ! brooks_law_get_option( 'faq_schema_enable' ) ) {
		return $graph;
	}

	$post = get_post();
	if ( ! $post ) {
		return $graph;
	}

	$faqs = brooks_law_extract_faqs( $post->post_content );

	/**
	 * Filter the extracted question-and-answer pairs.
	 *
	 * @param array   $faqs Pairs.
	 * @param WP_Post $post Current post.
	 */
	$faqs = apply_filters( 'brooks_law_faqs', $faqs, $post );

	if ( count( $faqs ) < 2 ) {
		return $graph;
	}

	$entities = array();
	foreach ( $faqs as $faq ) {
		$entities[] = array(
			'@type'          => 'Question',
			'name'           => $faq['question'],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $faq['answer'],
			),
		);
	}

	$graph[] = array(
		'@type'      => 'FAQPage',
		'@id'        => brooks_law_current_url() . '#faq',
		'url'        => brooks_law_current_url(),
		'mainEntity' => $entities,
	);

	return $graph;
}
add_filter( 'brooks_law_schema_graph', 'brooks_law_add_faq_schema' );

/**
 * Customizer switch, filed with the other schema controls.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function brooks_law_faq_customize( $wp_customize ) {
	$wp_customize->add_setting(
		'faq_schema_enable',
		array(
			'default'           => true,
			'sanitize_callback' => 'brooks_law_sanitize_checkbox',
		)
	);

	$wp_customize->add_control(
		'faq_schema_enable',
		array(
			'label'       => __( 'FAQ schema', 'brooks-law-30-pro' ),
			'description' => __( 'Marks up any heading that ends in a question mark, with the paragraphs under it as the answer. Needs at least two on a page.', 'brooks-law-30-pro' ),
			'section'     => 'brooks_law_seo',
			'type'        => 'checkbox',
		)
	);
}
add_action( 'customize_register', 'brooks_law_faq_customize', 25 );
