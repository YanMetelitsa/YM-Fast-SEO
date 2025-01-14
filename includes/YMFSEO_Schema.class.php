<?php

// Exits if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * YM Fast SEO Schema.org class.
 * 
 * @since 3.2.0
 */
class YMFSEO_Schema {
	/**
	 * Prepares data for Schema.org JSON-LD printing.
	 * 
	 * @global $wp
	 * 
	 * @param YMFSEO_Meta_Fields $meta_fields    Meta fields instance.
	 * @param mixed              $queried_object Queried object
	 * 
	 * @return array Prepared Shema.org array for printing JSON-LD.
	 */
	public static function build ( YMFSEO_Meta_Fields $meta_fields, $queried_object = null ) : array {
		global $wp;

		if ( is_null( $queried_object ) ) {
			$queried_object = get_queried_object();
		}

		// Sets object data template.
		$schema_org_blank = [
			'WebPage' => [
				'@type'      => $meta_fields->page_type,
				'@id'        => '#webpage',
				'url'        => home_url( $wp->request ),
				'name'       => wp_get_document_title(),
				'inLanguage' => get_locale(),
				'isPartOf'   => [
					'@id' => '#website',
				],
				'potentialAction' => [
					[
						'@type'  => 'ReadAction',
						'target' => [ home_url( $wp->request ) ],
					],
				],
			],
			'WebSite' => [
				'@type'       => 'WebSite',
				'@id'         => '#website',
				'url'         => home_url(),
				'name'        => get_bloginfo( 'name' ),
				'description' => get_bloginfo( 'description' ),
				'inLanguage'  => get_locale(),
			],
		];
		
		// Adds WebPage description.
		if ( $meta_fields->description ) {
			$schema_org_blank[ 'WebPage' ][ 'description' ] = $meta_fields->description;
		}
		// Adds WebPage image preview URI.
		if ( $meta_fields->image_uri ) {
			$schema_org_blank[ 'WebPage' ][ 'image' ] = $meta_fields->image_uri;
		}
		// Adds WebPage dates.
		if ( $queried_object && 'WP_Post' == get_class( $queried_object ) ) {
			$schema_org_blank[ 'WebPage' ][ 'datePublished' ] = get_the_date( 'c', $queried_object );
			$schema_org_blank[ 'WebPage' ][ 'dateModified' ]  = get_the_modified_date( 'c', $queried_object );
		}

		// Adds representative's details.
		$rep_data = [];

		$rep_type = YMFSEO_Settings::get_option( 'rep_type' );

		$rep_name = YMFSEO_Settings::get_option( "rep_{$rep_type}_name" );
		if ( $rep_name ) $rep_data[ 'name' ] = $rep_name;

		$rep_email = YMFSEO_Settings::get_option( 'rep_email' );
		if ( $rep_email ) $rep_data[ 'email' ] = $rep_email;

		$rep_phone = YMFSEO_Settings::get_option( 'rep_phone' );
		if ( $rep_phone ) $rep_data[ 'telephone' ] = $rep_phone;
		
		// Sets organization address.
		if ( 'org' === $rep_type ) {
			$address = [];

			$rep_city = YMFSEO_Settings::get_option( 'rep_org_city' );
			if ( $rep_city ) {
				$address[ 'addressLocality' ] = $rep_city;
			}

			$rep_region = YMFSEO_Settings::get_option( 'rep_org_region' );
			if ( $rep_region ) {
				$address[ 'addressRegion' ] = $rep_region;
			}

			$rep_address = YMFSEO_Settings::get_option( 'rep_org_address' );
			if ( $rep_address ) {
				$address[ 'streetAddress' ] = $rep_address;
			}

			$rep_postal_code = YMFSEO_Settings::get_option( 'rep_org_postal_code' );
			if ( $rep_postal_code ) {
				$address[ 'postalCode' ] = $rep_postal_code;
			}

			if ( ! empty( $address ) ) {
				$rep_data[ 'address' ] = array_merge(
					[ '@type' => 'PostalAddress' ],
					$address,
				);
			}
		}

		// Sets representative's image.
		$rep_image_id = YMFSEO_Settings::get_option( 'rep_image_id' );
		if ( $rep_image_id ) {
			$rep_data[ 'image' ] = wp_get_attachment_url( $rep_image_id );
		}

		// Pre-builds output object.
		if ( ! empty( $rep_data ) ) {
			$rep_data = array_merge([
				'@type' => match ( $rep_type ) {
					'org'    => YMFSEO_Settings::get_option( 'rep_org_type' ),
					'person' => 'Person',
				},
				'@id'   => '#publisher',
				'url'   => home_url(),
			], $rep_data );

			$schema_org_blank[ 'Publisher' ] = $rep_data;

			$schema_org_blank[ 'WebSite' ][ 'publisher' ] = [
				'@id' => '#publisher',
			];
		}
		
		// Applies user filters.
		$schema_org = apply_filters( 'ymfseo_schema_org', $schema_org_blank, $queried_object );
		
		// Final build.
		$schema_org = [
			'@context' => 'https://schema.org',
			'@graph'   => array_values( $schema_org ),
		];

		return $schema_org;
	}
}