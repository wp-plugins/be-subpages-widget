<?php
/*
Plugin Name: BE Subpages Widget
Plugin URI: http://www.billerickson.net
Description: Lists subpages of the current section
Version: 1.0
Author: Bill Erickson
Author URI: http://www.billerickson.net
License: GPLv2
*/

/**
 * Translations
 *
 */
function be_subpages_translations() {
	load_plugin_textdomain( 'be-subpages', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'be_subpages_translations' );


/** 
 * Register Widget
 *
 */
function be_subpages_load_widgets() {
	register_widget( 'BE_Subpages_Widget' );
}
add_action( 'widgets_init', 'be_subpages_load_widgets' );

/**
 * Subpages Widget Class
 *
 * @author       Bill Erickson <bill@billerickson.net>
 * @copyright    Copyright (c) 2011, Bill Erickson
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class BE_Subpages_Widget extends WP_Widget {
	
    /**
     * Constructor
     *
     * @return void
     **/
	function BE_Subpages_Widget() {
		$widget_ops = array( 'classname' => 'widget_subpages', 'description' => __( 'Lists current section subpages', 'be-subpages' ) );
		$this->WP_Widget( 'subpages-widget', __( 'Subpages Widget', 'be-subpages' ), $widget_ops );
	}

    /**
     * Outputs the HTML for this widget.
     *
     * @param array  An array of standard parameters for widgets in this theme 
     * @param array  An array of settings for this widget instance 
     * @return void Echoes it's output
     **/
	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		
		// Only run on pages
		if ( !is_page() )
			return;
			
		// Find top level parent
		global $post;
		$parent = $post;
		while( $parent->post_parent ) $parent = get_post( $parent->post_parent );
			
		// Build a menu listing top level parent's children
		$args = array(
			'child_of' => $parent->ID,
			'title_li' => '',
			'depth' => '1',
			'echo' => false,
		);
		$subpages = wp_list_pages( apply_filters( 'be_subpages_widget_args', $args ) );
		
		// If there are pages, display the widget
		if ( !empty( $subpages ) ) {
			echo $before_widget;
			
			// Build title
			$title = $instance['title'];
			if( 1 == $instance['title_from_parent'] ) {
				$title = $parent->post_title;
				if( 1 == $instance['title_link'] )
					$title = '<a href="' . get_permalink( $parent->ID ) . '">' . $title . '</a>';
			}	

			if( !empty( $title ) ) 
				echo $before_title . $title . $after_title;
			
			// Build the page listing	
			echo '<ul>' . $subpages . '</ul>';
			
			echo $after_widget;			
		}
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['title_from_parent'] = $new_instance['title_from_parent'];
		$instance['title_link'] = $new_instance['title_link'];
		
		return $instance;
	}

	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => '', 'title_from_parent' => 0, 'title_link' => 0 );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		 
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'be-subpages' );?></label>
		<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
		</p>
		
		<p>
			<input class="checkbox" type="checkbox" value="1" <?php checked( $instance['title_from_parent'], 1 ); ?> id="<?php echo $this->get_field_id( 'title_from_parent' ); ?>" name="<?php echo $this->get_field_name( 'title_from_parent' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'title_from_parent' ); ?>"><?php _e( 'Use top level page as section title.', 'be-subpages' );?></label>
		</p>		

		<p>
			<input class="checkbox" type="checkbox" value="1" <?php checked( $instance['title_link'], 1 ); ?> id="<?php echo $this->get_field_id( 'title_link' ); ?>" name="<?php echo $this->get_field_name( 'title_link' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'title_link' ); ?>"><?php _e( 'Make title a link', 'be-subpages' ); echo '<br /><em>('; _e( 'only if "use top level page" is checked', 'be-subpages' ); echo ')</em></label>';?>
		</p>			<?php
	}	

}

?>