<?php

// Exits if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

echo '<!-- YM Fast SEO Favicon -->';

printf( '<link rel="shortcut icon"             href="%s">', esc_url( home_url( 'favicon-ico.ico' ) ) );
printf( '<link rel="icon" type="image/svg+xml" href="%s">', esc_url( home_url( 'favicon.svg' ) ) );

printf( '<link rel="icon" type="image/png" href="%s" sizes="32x32">',   esc_url( home_url( 'favicon-32.png' ) ) );
printf( '<link rel="icon" type="image/png" href="%s" sizes="96x96">',   esc_url( home_url( 'favicon-96.png' ) ) );
printf( '<link rel="icon" type="image/png" href="%s" sizes="192x192">', esc_url( home_url( 'favicon-192.png' ) ) );

printf( '<link rel="apple-touch-icon" sizes="180x180" href="%s">', esc_url( home_url( 'apple-touch-icon.png' ) ) );

echo '<!-- / YM Fast SEO Favicon -->';