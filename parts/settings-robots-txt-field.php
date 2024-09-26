<?php

ob_start();
	
do_robots();

$current_robots_txt = ob_get_contents();

ob_end_clean();

printf( '<textarea name="%1$s" id="%1$s" class="code" rows="10" cols="50">%2$s</textarea>',
	esc_attr( $args[ 'label_for' ] ),
	esc_textarea( $current_robots_txt ),
);