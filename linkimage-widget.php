<?php
/*
	Linkimage Widget Class
	@since 1.0.0
	For another improvement, you can drop email to zourbuth@gmail.com or visit http://zourbuth.com/
	Copyright 2013  zourbuth.com  (email : zourbuth@gmail.com)

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

class Linkimage_Widget extends WP_Widget {
	
	/**
	 * Textdomain for the widget.
	 * @since 1.0.0
	 */
	var $textdomain;
	var $prefix;
	
	function __construct() {
		
		// Set the widget textdomain
		$this->prefix 		= 'linkimage';
		$this->textdomain 	= 'linkimage';

		// Load the widget stylesheet for the widgets admin screen
		add_action( 'load-widgets.php', array(&$this, 'custom_post_admin_style') );

		// Set up the widget options
		$widget_options = array(
			'classname' => $this->prefix,
			'description' => esc_html__( '[+] Create many images with each link in elegan way.', $this->textdomain )
		);

		// Set up the widget control options
		$control_options = array( 'width' => 460, 'height' => 350, 'id_base' => $this->prefix );

		$this->WP_Widget( $this->prefix, esc_attr__( 'Linkimage', $this->textdomain ), $widget_options, $control_options );
		
		add_action( 'wp_ajax_linkimage_widget', array( &$this, 'widget_ajax' ) );
			
		if ( is_active_widget( false, false, $this->id_base, false ) && ! is_admin() ) {			
			wp_enqueue_style( $this->prefix, LINKIMAGE_URL . 'css/linkimage.css' );
			add_action( 'wp_head', array( &$this, 'head_print_script' ) );
		}
	}
	
	
	/**
	 * Function to add the widget localize script
	 * Uses 'sp_localize_script' filter
	 * @since 1.4
	 */
	function localize_script( $scripts ) {
		return $scripts + array( 'paging' => 'linkimage_widget_ajax' );
	}
	
	
	/*
	 * Push the widget stylesheet widget.css into widget admin page	
	 * @since 1.3
	**/		
	function custom_post_admin_style() {	
		wp_enqueue_style( 'linkimage-admin', LINKIMAGE_URL . 'css/dialog.css', array('farbtastic', 'thickbox') );
		wp_enqueue_style( 'wp-color-picker' );		
		wp_enqueue_script( 'linkimage-admin', LINKIMAGE_URL . 'js/jquery.dialog.js', array( 'jquery', 'wp-color-picker', 'thickbox' ) );
		wp_localize_script( 'linkimage-admin', 'linkimagevar', apply_filters('sp_localize_script', array(
			'ajaxurl'	=> admin_url('admin-ajax.php'),
			'nonce'		=> wp_create_nonce( 'linkimage' ),
			'action'	=> 'linkimage_widget',
		)));			
	}
	
	
	function head_print_script() {
		$settings = $this->get_settings();
		foreach ( $settings as $key => $val )
			if ( !empty( $val['customstylescript'] ) )
				echo $val['customstylescript'];
				
		// print the style
		echo "<style type='text/css'>";
		foreach ( $settings as $key => $val ) {	
			$this->generate_style( $val );
		}
		echo "</style>\n";		
	}
	
	
	/**
	 * Create widget style for backend and frontend
	 * @since 1.0.0
	 * @param $args
	*/		
	function generate_style( $args ) {
		$divstyle = $a_style = $linkimage = "";
		
		$bg_image = $args['background_image'] ? "url('{$args['background_image']}')" : "none";
		$linkimage .= "background: $bg_image scroll no-repeat 0 0 transparent;";
		
		$divstyle .= "padding:{$args['padding'][0]}px {$args['padding'][1]}px {$args['padding'][2]}px {$args['padding'][3]}px;";
		$divstyle .= "margin:{$args['margin'][0]}px {$args['margin'][1]}px {$args['margin'][2]}px {$args['margin'][3]}px;";
		$divstyle .= "border:{$args['border_width']}px solid {$args['border_color']};";
		$divstyle .= "border-radius: -webkit-border-radius: {$args['border_radius']}px; -moz-border-radius: {$args['border_radius']}px; border-radius: {$args['border_radius']}px;";
		
		$bg_color = $args['background_color'] ? $args['background_color'] : 'transparent';
		$divstyle .= "background-color: $bg_color;";
		$a_style .= "height:{$args['image_height']}px;width:{$args['image_width']}px;";
		echo "#linkimage-{$this->number} .linkimage-container { $linkimage } ";
		echo "#linkimage-{$this->number} .linkimage-container > div { $divstyle } ";
		echo "#linkimage-{$this->number} .linkimage-container > div > a { $a_style } ";
	}
	
	
	/**
	 * Verifies the AJAX request to prevent processing requests external of the blog.
	 * @since 1.0.0
	 * @param _ajax_nonce
	*/	
	function widget_ajax() {
		// Check the nonce and if not isset the id, just die.
		check_ajax_referer( 'linkimage' );
			
		if ( ! current_user_can( 'edit_theme_options' ) )
			wp_die( -1 );
		
		if ( ! isset( $_POST['image'] ) )
			wp_die( -1 );
			
		$link  	= isset( $_POST['link'] ) ? esc_url( $_POST['link'] ) : '#';
		$image  = esc_url( $_POST['image'] );
		$alt	= esc_attr( $_POST['alt'] );
		
		// Ok then, lets we proceed the posts into the template.
		$html = '';
		$instance = $this->get_settings();
		$html .= "<div class='element'>
						<a href='$link' target='_blank'><img src='$image' alt='$alt' /></a>
						<span class='element-delete'></span>
						<input type='hidden' name='{$this->get_field_name('urls')}[]' value='$link' />
						<input type='hidden' name='{$this->get_field_name('images')}[]' value='$image' />
						<input type='hidden' name='{$this->get_field_name('alts')}[]' value='$alt' />
					</div>";

		echo $html;
		wp_die();
	}
	
	
	/**
	 * Widget front-end function
	 * @since 1.0.0
	 * @param $args, $instance
	*/		
	function widget($args, $instance) {
		extract( $args, EXTR_SKIP );

		// Set up the arguments for z_list_authors()
		$args = array(
			'id'				=> $this->number,
			'title'				=> $instance['title'],
			'images'			=> $instance['images'],
			'urls'				=> $instance['urls'],
			'alts'				=> $instance['alts'],
			'padding'			=> $instance['padding'],
			'margin'			=> $instance['margin'],
			'border_width'		=> $instance['border_width'],
			'border_radius'		=> $instance['border_radius'],
			'border_color'		=> $instance['border_color'],
			'background_color'	=> $instance['background_color'],
			'background_image'	=> $instance['background_image'],
			'image_height'		=> $instance['image_height'],
			'image_width'		=> $instance['image_width'],
			'target_blank'		=> ! empty( $instance['target_blank'] ) ? true : false,
			'toggle_active'		=> $instance['toggle_active'],
			'intro_text' 		=> $instance['intro_text'],
			'outro_text' 		=> $instance['outro_text'],
			'customstylescript'	=> $instance['customstylescript']
		);

		// Output the theme's $before_widget wrapper
		echo $before_widget;

		// If a title was input by the user, display it
		if ( ! empty( $instance['title'] ) )
			echo $before_title . apply_filters( 'widget_title',  $instance['title'], $instance, $this->id_base ) . $after_title;

		// Print intro text if exist
		if ( ! empty( $instance['intro_text'] ) )
			echo '<p class="'. $this->id . '-intro-text">' . $instance['intro_text'] . '</p>';

		// Print the custom post
		echo linkimage_generate( $args );
		
		// Print outro text if exist
		if ( ! empty( $instance['outro_text'] ) )
			echo '<p class="'. $this->id . '-outro_text">' . $instance['outro_text'] . '</p>';
			
		// Close the theme's widget wrapper
		echo $after_widget;
	}

	
	/**
	 * Widget update function
	 * @since 1.0.0
	 * @param $new_instance, $old_instance
	*/		
	function update($new_instance, $old_instance) {
		$instance = $old_instance;

		/* Set the instance to the new instance. */
		$instance = $new_instance;

		$instance['images'] 			= $new_instance['images'];
		$instance['urls'] 				= $new_instance['urls'];
		$instance['alts'] 				= $new_instance['alts'];
		$instance['padding'] 			= $new_instance['padding'];
		$instance['margin'] 			= $new_instance['margin'];
		$instance['border_width'] 		= $new_instance['border_width'];
		$instance['border_radius'] 		= $new_instance['border_radius'];
		$instance['border_color'] 		= $new_instance['border_color'];
		$instance['background_color'] 	= $new_instance['background_color'];
		$instance['background_image'] 	= $new_instance['background_image'];
		$instance['image_height'] 		= $new_instance['image_height'];
		$instance['image_width']		= $new_instance['image_width'];
		$instance['target_blank']		= isset( $new_instance['target_blank'] ) ? 1 : 0;
		$instance['toggle_active'] 		= $new_instance['toggle_active'];
		$instance['intro_text'] 		= $new_instance['intro_text'];
		$instance['outro_text'] 		= $new_instance['outro_text'];
		$instance['customstylescript']	= $new_instance['customstylescript'];
		
		return $instance;
	}

	
	/**
	 * Widget update function
	 * @since 1.0.0
	 * @param $instance
	*/	
	function form($instance) {
		/* Set up the default form values. */
		$defaults = array(
			'title' 			=> __( 'Linkimage Widget', $this->textdomain),
			'images' 			=> '',
			'padding' 			=> array(0, 0, 0, 0),
			'margin' 			=> array(0, 5, 5, 0),
			'border_color' 		=> 'transparent',
			'background_color' 	=> '#f2f2f2',
			'background_image' 	=> '',
			'border_width' 		=> 0,
			'border_radius' 	=> 0,
			'image_height' 		=> 125,
			'image_width' 		=> 125,
			'target_blank' 		=> true,
			'toggle_active'		=> array(0 => true, 1 => false, 2 => false, 3 => false),
			'intro_text' 		=> '',
			'outro_text' 		=> '',
			'customstylescript'	=> ''
		);

		/* Merge the user-selected arguments with the defaults. */
		$instance = wp_parse_args( (array) $instance, $defaults );
		
		$tabs = array( 
			__( 'General', $this->textdomain ),  
			__( 'Advanced', $this->textdomain ),
			__( 'Customs', $this->textdomain ),
			__( 'Premium', $this->textdomain ) 
		);
?>
		<div class="pluginName">Linkimage<span class="pluginVersion"><?php echo LINKIMAGE_VERSION; ?></span></div>
		<div id="linkimage-<?php echo $this->number ; ?>" class="totalControls tabbable tabs-left">
			<ul class="nav nav-tabs">
				<?php foreach ($tabs as $key => $tab ) : ?>
					<li class="<?php echo $instance['toggle_active'][$key] ? 'active' : '' ; ?>"><?php echo $tab; ?><input type="hidden" name="<?php echo $this->get_field_name( 'toggle_active' ); ?>[]" value="<?php echo $instance['toggle_active'][$key]; ?>" /></li>
				<?php endforeach; ?>							
			</ul>
			<ul class="tab-content" style="min-height:350px;">
				<li class="tab-pane <?php if ( $instance['toggle_active'][0] ) : ?>active<?php endif; ?>">
					<ul>
						<li>
							<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'Put the title here, or leave it empty to hide the title.', $this->textdomain ); ?></span>
							<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'images' ); ?>"><?php _e( 'Add Images', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'Add the images here, drag and drop to arrange the position. Hit the save button to take effect.', $this->textdomain ); ?></span>
							<a href="#" class="button add-linkimage"><?php _e( 'Add image', $this->textdomain ); ?></a>
							<span class="hidden loading"><?php _e( 'Adding...', $this->textdomain ); ?></span>
							<div id="<?php echo $this->id; ?>roleWrapper" class="linkimage-container clear">
								<style type="text/css">
									<?php $this->generate_style( $instance ); ?>
								</style>							
								<?php if ( is_array ( $instance['images'] ) ) { ?>
									<?php foreach ( $instance['images'] as $key => $val ) { ?>
										<div class="element">
											<a href="<?php echo $instance['urls'][$key]; ?>" target="_blank"><img src="<?php echo $instance['images'][$key]; ?>" alt="<?php echo $instance['alts'][$key]; ?>" title="<?php echo $instance['alts'][$key]; ?>" /></a>
											<span class="element-delete"></span>
											<input type="hidden" name="<?php echo $this->get_field_name( 'urls' ); ?>[]" value="<?php echo $instance['urls'][$key]; ?>" />
											<input type="hidden" name="<?php echo $this->get_field_name( 'images' ); ?>[]" value="<?php echo $instance['images'][$key]; ?>" />
											<input type="hidden" name="<?php echo $this->get_field_name( 'alts' ); ?>[]" value="<?php echo $instance['alts'][$key]; ?>" />
										</div>
									<?php } ?>
								<?php } ?>
							</div>
							<span class="controlDesc"><?php _e( 'Upgrade to <strong>premium</strong> to easily drag and drop the images within this panel or in the frontend. Please note, your theme perhaps does not display the image(s) as the preview above.', $this->textdomain ); ?></span>
						</li>
					</ul>
				</li>
				<li class="tab-pane <?php if ( $instance['toggle_active'][1] ) : ?>active<?php endif; ?>">
					<ul>
						<li>
							<label for="<?php echo $this->get_field_id( 'target_blank' ); ?>">
							<input class="checkbox" type="checkbox" <?php checked( $instance['target_blank'], true ); ?> id="<?php echo $this->get_field_id( 'target_blank' ); ?>" name="<?php echo $this->get_field_name( 'target_blank' ); ?>" /><?php _e( 'Blank Target', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'Open link in new window/tab.', $this->textdomain ); ?></span>
						</li>					
						<li>
							<label for="<?php echo $this->get_field_id( 'image_height' ); ?>"><?php _e( 'Image Height & Weight', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'The image or thumbnail height and weight in pixels unit.', $this->textdomain ); ?></span>
							<input type="text" id="<?php echo $this->get_field_id( 'image_height' ); ?>" name="<?php echo $this->get_field_name( 'image_height' ); ?>" value="<?php echo esc_attr( $instance['image_height'] ); ?>" />
							<input type="text" id="<?php echo $this->get_field_id( 'image_width' ); ?>" name="<?php echo $this->get_field_name( 'image_width' ); ?>" value="<?php echo esc_attr( $instance['image_width'] ); ?>" />
						</li>					
						<li>
							<label for="<?php echo $this->get_field_id( 'padding' ); ?>0"><?php _e( 'Padding', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'The image padding top, right, bottom and left in pixels unit.', $this->textdomain ); ?></span>
							<input class="smallfat" type="text" id="<?php echo $this->get_field_id( 'padding' ); ?>0" name="<?php echo $this->get_field_name( 'padding' ); ?>[]" value="<?php echo (int) $instance['padding'][0]; ?>" />							
							<input class="smallfat" type="text" id="<?php echo $this->get_field_id( 'padding' ); ?>1" name="<?php echo $this->get_field_name( 'padding' ); ?>[]" value="<?php echo (int) $instance['padding'][1]; ?>" />							
							<input class="smallfat" type="text" id="<?php echo $this->get_field_id( 'padding' ); ?>2" name="<?php echo $this->get_field_name( 'padding' ); ?>[]" value="<?php echo (int) $instance['padding'][2]; ?>" />							
							<input class="smallfat" type="text" id="<?php echo $this->get_field_id( 'padding' ); ?>3" name="<?php echo $this->get_field_name( 'padding' ); ?>[]" value="<?php echo (int) $instance['padding'][3]; ?>" />							
						</li>
						<li>
							<label for="<?php echo $this->get_field_id( 'margin' ); ?>0"><?php _e( 'Margin', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'The image margin top, right, bottom and left in pixels unit.', $this->textdomain ); ?></span>
							<input class="smallfat" type="text" id="<?php echo $this->get_field_id( 'margin' ); ?>0" name="<?php echo $this->get_field_name( 'margin' ); ?>[]" value="<?php echo (int) $instance['margin'][0]; ?>" />							
							<input class="smallfat" type="text" id="<?php echo $this->get_field_id( 'margin' ); ?>1" name="<?php echo $this->get_field_name( 'margin' ); ?>[]" value="<?php echo (int) $instance['margin'][1]; ?>" />							
							<input class="smallfat" type="text" id="<?php echo $this->get_field_id( 'margin' ); ?>2" name="<?php echo $this->get_field_name( 'margin' ); ?>[]" value="<?php echo (int) $instance['margin'][2]; ?>" />							
							<input class="smallfat" type="text" id="<?php echo $this->get_field_id( 'margin' ); ?>3" name="<?php echo $this->get_field_name( 'margin' ); ?>[]" value="<?php echo (int) $instance['margin'][3]; ?>" />							
						</li>
						<li>
							<label><?php _e( 'More Style', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'Upgrade to <strong>premium</strong> for more style options.', $this->textdomain ); ?></span>							
						</li>						
					</ul>
				</li>				
				<li class="tab-pane <?php if ( $instance['toggle_active'][2] ) : ?>active<?php endif; ?>">
					<ul>
						<li>
							<label for="<?php echo $this->get_field_id('intro_text'); ?>"><?php _e( 'Intro Text', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'This option will display addtional text before the widget title and HTML supports.', $this->textdomain ); ?></span>
							<textarea name="<?php echo $this->get_field_name( 'intro_text' ); ?>" id="<?php echo $this->get_field_id( 'intro_text' ); ?>" rows="3" class="widefat"><?php echo esc_textarea($instance['intro_text']); ?></textarea>							
						</li>
						<li>
							<label for="<?php echo $this->get_field_id('outro_text'); ?>"><?php _e( 'Outro Text', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'This option will display addtional text after widget and HTML supports.', $this->textdomain ); ?></span>
							<textarea name="<?php echo $this->get_field_name( 'outro_text' ); ?>" id="<?php echo $this->get_field_id( 'outro_text' ); ?>" rows="3" class="widefat"><?php echo esc_textarea($instance['outro_text']); ?></textarea>
							
						</li>				
						<li>
							<label for="<?php echo $this->get_field_id('customstylescript'); ?>"><?php _e( 'Custom Script & Stylesheet', $this->textdomain ) ; ?></label>
							<span class="controlDesc"><?php _e( 'Use this box for additional widget CSS style of custom javascript. This widget selector is: ', $this->textdomain ); ?><?php echo '<tt>#' . $this->id . '</tt>'; ?></span>
							<textarea name="<?php echo $this->get_field_name( 'customstylescript' ); ?>" id="<?php echo $this->get_field_id( 'customstylescript' ); ?>" rows="5" class="widefat code"><?php echo htmlentities($instance['customstylescript']); ?></textarea>
						</li>
						<li>
							<label><?php _e( 'Widget Shortcode', $this->textdomain ); ?></label>
							<span class="controlDesc"><?php _e( 'Upgrade to <strong>premium</strong> to attach this widget to your post content using shortcode.', $this->textdomain ); ?></span>							
						</li>							
					</ul>
				</li>
				<li class="tab-pane <?php if ( $instance['toggle_active'][3] ) : ?>active<?php endif; ?>">
					<ul>
						<li>							
							<h3 style="margin-bottom: 3px;"><?php _e( 'Upgrade To Premium Version', $this->textdomain ); ?></h3>
							<span class="controlDesc">
								<?php _e( 'This premium version gives more abilities, features, options and premium supports. Full documentation will let 
										you customize this premium version easily.', $this->textdomain ); ?><br /><br />
								<?php _e( 'Main key features you will get with premium version:', $this->textdomain ); ?>
							</span>
							
						</li>					
						<li>
							<strong><?php _e( 'Premium Supports', $this->textdomain ) ; ?></strong>
							<span class="controlDesc"><?php _e( 'A premium supports, helps and documentation.', $this->textdomain ); ?></span>
						</li>
						<li>
							<strong><?php _e( 'Plugin Update', $this->textdomain ) ; ?></strong>
							<span class="controlDesc"><?php _e( 'No worries about the update, you will have that.', $this->textdomain ); ?></span>
						</li>
						<li>
							<strong><?php _e( 'Easy Shortcode', $this->textdomain ) ; ?></strong>
							<span class="controlDesc"><?php _e( 'Easy to use shortcodes in your post content or PHP function.', $this->textdomain ); ?></span>
						</li>
						<li>
							<strong><?php _e( 'Drag & Drop for Backend and Frontend ', $this->textdomain ) ; ?></strong>
							<span class="controlDesc"><?php _e( 'Easy to rearrange the images with drag and drop functionality in the administration or frontend section.', $this->textdomain ); ?></span>
						</li>
						<li>
							<strong><?php _e( 'Advanced Image Style Options', $this->textdomain ) ; ?></strong>
							<span class="controlDesc"><?php _e( 'Easy to use visual style generator.', $this->textdomain ); ?></span>
						</li>
						<li>
							<strong><?php _e( 'And More...', $this->textdomain ) ; ?></strong>
							<span class="controlDesc"><?php _e( '
								- Ajax powered for frontend<br />
								- Custom image border color picker<br />
								- Custom image border width<br />
								- Custom widget background image<br />
								- Custom image background color picker
							', $this->textdomain ); ?></span>
						</li>						
						<li>
							<style type="text/css">
								.preimg { 
									border: 1px solid #DDDDDD;
									border-radius: 2px 2px 2px 2px;
									float: right;
									padding: 4px;
									margin-left: 8px;
								}
								.preimg:hover { 
									border: 1px solid #cccccc;
								}
								.wp-core-ui .btnremium { 
									border-color: #CCCCCC;
									height: auto;
									margin-top: 9px;
									padding-bottom: 0;
									padding-right: 0;
								}
								.wp-core-ui .btnremium span {
									background: none repeat scroll 0 0 #FFFFFF;
									border-left: 1px solid #F2F2F2;
									display: inline-block;
									font-size: 18px;
									line-height: 25px;
									margin-left: 9px;
									padding: 0 9px;
									border-radius: 0 3px 3px 0;
								}
							</style>
							<span class="controlDesc">Buy premium version via PayPal or CreditCard</span>
							<a class="button btnremium" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=S55U88GYKBP5N">Get Premium<span>$6</span></a>							
						</li>
					</ul>
				</li>
			</ul>
		</div>
<?php
	}
}
?>