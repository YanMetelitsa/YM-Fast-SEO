<?php

// Exits if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

echo '<!-- YM Fast SEO Favicons -->';

printf( '<link rel="icon" type="image/x-icon"  sizes="48x48" href="%s">', esc_url( home_url( 'favicon-ico.ico' ) ) );
printf( '<link rel="icon" type="image/svg+xml" sizes="any"   href="%s">', esc_url( home_url( 'favicon.svg' ) ) );

printf( '<link rel="icon" type="image/png" sizes="16x16" href="%s">',   esc_url( home_url( 'favicon-16.png' ) ) );
printf( '<link rel="icon" type="image/png" sizes="32x32" href="%s">',   esc_url( home_url( 'favicon-32.png' ) ) );
printf( '<link rel="icon" type="image/png" sizes="48x48" href="%s">',   esc_url( home_url( 'favicon-48.png' ) ) );
printf( '<link rel="icon" type="image/png" sizes="96x96" href="%s">',   esc_url( home_url( 'favicon-96.png' ) ) );
printf( '<link rel="icon" type="image/png" sizes="192x192" href="%s">', esc_url( home_url( 'favicon-192.png' ) ) );

printf( '<link rel="icon" type="image/png" sizes="192x192" href="%s">', esc_url( home_url( 'android-chrome-192x192.png' ) ) );
printf( '<link rel="icon" type="image/png" sizes="512x512" href="%s">', esc_url( home_url( 'android-chrome-512x512.png' ) ) );
printf( '<link rel="apple-touch-icon"      sizes="180x180" href="%s">', esc_url( home_url( 'apple-touch-icon.png' ) ) );

echo '<!-- / YM Fast SEO Favicons -->';