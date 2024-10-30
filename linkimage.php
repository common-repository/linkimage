<?php
/*
    Plugin Name: Linkimage
    Plugin URI: http://zourbuth.com/?p=885
    Description: A powerfull plugin for displaying images with each link in your site. With the beautiful administration interface, tons of options support drag and drop, makes this plugin more elegant than others.
    Version: 1.0.1
    Author: zourbuth
    Author URI: http://zourbuth.com
    License: GPL2

	Copyright 2013 zourbuth.com (email : zourbuth@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


// Launch the plugin
add_action( 'plugins_loaded', 'linkimage_plugin_loaded' );


/**
 * Initializes the plugin and it's features with the 'plugins_loaded' action
 * Creating custom constan variable and load necessary file for this plugin
 * Attach the widget on plugin load
 * @since 1.0.0
 */
function linkimage_plugin_loaded() {

	// Set constant variable
	define( 'LINKIMAGE_VERSION', '1.0.1' );
	define( 'LINKIMAGE_DIR', plugin_dir_path( __FILE__ ) );
	define( 'LINKIMAGE_URL', plugin_dir_url( __FILE__ ) );
	
	add_action( 'widgets_init', 'linkimage_widgets_init' );
}


/**
 * Register widget
 * @since 1.0.0
 */
function linkimage_widgets_init() {
	require_once( LINKIMAGE_DIR . 'linkimage-widget.php' );
	register_widget( 'Linkimage_Widget' );
}


/**
 * Function for creating the images/links in the front end
 * @since 1.0.0
 */
function linkimage_generate( $args ) {
	$html = '';
	//print_r( $args ) ;
	if ( is_array ( $args['images'] ) ) {
		
		$html .= current_user_can( 'manage_options' ) ? "<form method='post' action=''>" : '';
		
		$sortable = current_user_can( 'manage_options' ) ? ' linkimage-sortable' : '';
		$data_id = current_user_can( 'manage_options' ) ? "data-id='{$args['id']}'" : '';

		$html .= "<div $data_id class='linkimage-container$sortable'>";
		
		$target = $args['target_blank'] ? " target='_blank'" : "";
		foreach ( $args['images'] as $key => $val ) {
			$html .= "<div>";
			$html .= "<a href='{$args['urls'][$key]}'$target><img src='{$args['images'][$key]}' alt='{$args['alts'][$key]}' title='{$args['alts'][$key]}' /></a>";
			if( current_user_can( 'manage_options' ) ) {
				$html .= "<input type='hidden' name='urls[]' value='{$args['urls'][$key]}' />";
				$html .= "<input type='hidden' name='images[]' value='{$args['images'][$key]}' />";
				$html .= "<input type='hidden' name='alts[]' value='{$args['alts'][$key]}' />";				
			}			
			$html .= "</div>";
		}
		
		$html .= "</div>";
		
		$html .= current_user_can( 'manage_options' ) ? "</form>" : '';
		
		return $html;
	}
}
?>