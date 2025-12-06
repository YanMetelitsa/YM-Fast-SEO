<?php

namespace YMFSEO;

// Exits if accessed directly.
if ( ! \defined( 'ABSPATH' ) ) exit;

$ymfseo_is_full_type = 'full' == $llms_txt_type;

// Write visit log.
Logger::write( 'llms-txt', [
	'user-agent' => isset( $_SERVER[ 'HTTP_USER_AGENT' ] )
		? sanitize_text_field( wp_unslash( $_SERVER[ 'HTTP_USER_AGENT' ] ) )
		: esc_html__( 'Unknown User-Agent', 'ym-fast-seo' ),
	'file' => $ymfseo_is_full_type ? 'llms-full.txt' : 'llms.txt',
]);

// File header.
echo esc_html( '# ' . get_bloginfo( 'name' ) . ': ' . get_bloginfo( 'description' ) . "\n\n" );

// File description.
echo esc_html__( 'Important notes:', 'ym-fast-seo' );

/* translators: %s: llms.txt */
echo "\n- " . \sprintf( esc_html__( 'This is an %s file, intended for use by large language models (LLMs).', 'ym-fast-seo' ), 
	'llms.txt',
);

/* translators: %s: Sitemap URL */
echo "\n- " . \sprintf( esc_html__( 'Each section below shows up to 100 of the most recent entries. The site may contains many more sections, pages, and content than are displayed here. A complete list of site resources is available in the [sitemap](%s).', 'ym-fast-seo' ),
	esc_url( get_sitemap_url( 'index' ) ),
);

echo "\n- " . esc_html__( 'This page has just been automatically generated and may not be complete.', 'ym-fast-seo' );

echo "\n";

// Post Types loop.
$ymfseo_public_post_types = Core::get_public_post_types();
$ymfseo_page_item         = array_search( 'page', $ymfseo_public_post_types );

if ( false !== $ymfseo_page_item ) {
    unset( $ymfseo_public_post_types[ $ymfseo_page_item ] );
    array_unshift( $ymfseo_public_post_types, $ymfseo_page_item );
}

foreach ( $ymfseo_public_post_types as $ymfseo_post_type_slug ) {
	$post_type = get_post_type_object( $ymfseo_post_type_slug );

	$ymfseo_post_type_query = new \WP_Query([
		'post_type'      => $ymfseo_post_type_slug,
		'posts_per_page' => 100,
		'meta_query'     => [ // phpcs:ignore
			'relation' => 'OR',
			[
				'key'     => 'ymfseo_fields',
				'compare' => 'NOT EXISTS',
			],
			[
				'key'     => 'ymfseo_fields',
				'value'   => 's:7:"noindex";s:1:"1";',
				'compare' => 'NOT LIKE',
			],
		],
	]);
	
	if ( $ymfseo_post_type_query->have_posts() ) {
		// Post Type title.
		echo "\n## " . esc_html( $post_type->label ) . "\n\n";

		// Posts loop.
		while ( $ymfseo_post_type_query->have_posts() ) {
			$ymfseo_post_type_query->the_post();

			if ( $ymfseo_is_full_type ) {
				$ymfseo_meta_fields = new MetaFields( get_post() );

				// Post title.
				echo "### " . esc_html( get_the_title() ) . "\n\n";

				$ymfseo_excerpt = get_the_excerpt();

				if ( ! Checker::are_strings_similar( $ymfseo_meta_fields->description, $ymfseo_excerpt ) ) {
					/* translators: %s: Description */
					\printf( esc_html__( 'Description: %s', 'ym-fast-seo' ),
						wp_kses_post( $ymfseo_meta_fields->description ) . "\n\n",
					);
				}

				if ( $ymfseo_excerpt ) {
					/* translators: %s: Post excerpt */
					\printf( esc_html__( 'Excerpt: %s', 'ym-fast-seo' ),
						wp_kses_post( $ymfseo_excerpt ) . "\n\n",
					);
				}

				$ymfseo_custom_fields = apply_filters( "ymfseo_{$ymfseo_post_type_slug}_posts_llms_txt_custom_fields", [], get_the_ID() );
				
				if ( is_array( $ymfseo_custom_fields ) && ! empty( $ymfseo_custom_fields ) ) {
					// Custom fields.
					foreach ( $ymfseo_custom_fields as $ymfseo_key => $ymfseo_value ) {
						\printf( '%s: %s',
							esc_html( (string) $ymfseo_key ),
							esc_html( (string) $ymfseo_value ) . "\n\n",
						);
					}
				}

				if ( 'page' == $ymfseo_post_type_slug ) {
					/* translators: %s: Page type */
					\printf( esc_html__( 'Page type: %s', 'ym-fast-seo' ),
						esc_html( $ymfseo_meta_fields->page_type ) . "\n\n",
					);
				}

				if ( 'post' == $ymfseo_post_type_slug ) {
					$ymfseo_categories = get_the_category();

					if ( $ymfseo_categories ) {
						/* translators: %s: Post categories */
						\printf( esc_html__( 'Categories: %s', 'ym-fast-seo' ),
							esc_html( implode( ', ', wp_list_pluck( $ymfseo_categories, 'name' ) ) ) . "\n\n",
						);
					}

					$ymfseo_tags = get_the_tags();

					if ( $ymfseo_tags ) {
						/* translators: %s: Post tags */
						\printf( esc_html__( 'Tags: %s', 'ym-fast-seo' ),
							esc_html( implode( ', ', wp_list_pluck( $ymfseo_tags, 'name' ) ) ) . "\n\n",
						);
					}
				}

				/* translators: %s: Modification time */
				\printf( esc_html__( 'Last modification time: %s', 'ym-fast-seo' ),
					esc_html( get_the_modified_time( 'j F Y H:i:s' ) ) . "\n\n",
				);

				/* translators: %s: Post URL */
				\printf( esc_html__( 'URL: %s', 'ym-fast-seo' ), sprintf( "[%1\$s](%1\$s)\n\n",
					esc_url( get_the_permalink() ),
				));
			} else {
				// Post info.
				\printf( "- [%s](%s)\n",
					esc_html( get_the_title() ),
					esc_url( get_the_permalink() ),
				);
			}
		}
	}
	
	wp_reset_postdata();
}

// Taxonomies loop.
foreach ( Core::get_public_taxonomies( 'names', true ) as $ymfseo_taxonomy_name ) {
	$taxonomy = get_taxonomy( $ymfseo_taxonomy_name );

	$ymfseo_terms = get_terms([
		'taxonomy'   => $ymfseo_taxonomy_name,
		'hide_empty' => true,
	]);

	if ( ! is_wp_error( $ymfseo_terms ) && ! empty( $ymfseo_terms ) ) {
		// Taxonomy title.
		echo "\n## " . esc_html( $taxonomy->label ) . "\n\n";

		if ( $ymfseo_is_full_type && $taxonomy->object_type ) {
			/* translators: %s: Post Types list */
			echo \sprintf( esc_html__( 'This taxonomy is used for the following post types: %s.', 'ym-fast-seo' ),
				esc_html( implode( ', ',
					array_map( function ( $post_type_name ) {
						return esc_html( get_post_type_object( $post_type_name )->label );
					}, $taxonomy->object_type )
				)),
			) . "\n\n";
		}

		// Terms loop.
		foreach ( $ymfseo_terms as $term ) {
			$ymfseo_meta_fields = new MetaFields( $term );

			$ymfseo_term_description = $ymfseo_meta_fields->description ?: $term->description;

			if ( $ymfseo_is_full_type ) {
				// Term title.
				echo "### " . esc_html( $term->name ) . "\n\n";

				if ( $ymfseo_term_description ) {
					/* translators: %s: Description */
					\printf( esc_html__( 'Description: %s', 'ym-fast-seo' ),
						esc_html( $ymfseo_term_description ) . "\n\n",
					);
				}

				/* translators: %s: Number of Term items */
				\printf( esc_html__( 'Number of items: %s', 'ym-fast-seo' ),
					esc_html( $term->count ) . "\n\n",
				);

				// Term URL.
				\printf( 'URL: %s', \sprintf( "[%1\$s](%1\$s)\n\n",
					esc_url( get_term_link( $term ) ),
				));
			} else {
				// Term info.
				\printf( "- [%s](%s)%s\n",
					esc_html( $term->name ),
					esc_url( get_term_link( $term ) ),
					esc_html( $ymfseo_term_description ? " – {$ymfseo_term_description}" : '' ),
				);
			}
		}
	}
}