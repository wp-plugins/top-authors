<?php
/**
 * Plugin Name: Top Authors
 * Plugin URI: http://developr.nl/work/top-authors
 * Description: A widget that sums the top authors on your blog
 * Version: 0.3.1
 * Author: developR | Seb van Dijk
 * Author URI: http://www.developr.nl
 *
 */

/**
 * Add function to widgets_init that'll load our widget.
 * @since 0.1
 */
add_action( 'widgets_init', 'top_authors' );

/**
 * Register our widget.
 *
 */
function top_authors() {
	register_widget( 'Top_Authors' );
}

/**
 * Example Widget class.
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update.  Nice!
 *
 * @since 0.1
 */
class Top_Authors extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function Top_authors	() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'Top Authors', 'description' => __('A widget that sums the top authors on your blog', 'top_authors') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'top_authors' );

		/* Create the widget. */
		$this->WP_Widget( 'top_authors', __('Top Authors', 'top_authors'), $widget_ops, $control_ops );
	}

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$number_of_authors = $instance['number'];
		$template = $instance['template'];
		
		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;

		$uc=array();
		$blogusers = get_users_of_blog();
		if ($blogusers) {
		  foreach ($blogusers as $bloguser) {
		    $post_count = get_usernumposts($bloguser->user_id);
		    $uc[$bloguser->user_id]=$post_count;
		  }
		  arsort($uc); //use asort($uc) if ascending by post count is desired
		  $maxauthor=$number_of_authors;
		  $count=0;
		  
		  if($uc){echo "<ul>";}
		  foreach ($uc as $key => $value) {
		  $count++;
		    if ($count <= $maxauthor) {
		      $user = get_userdata($key);
		      $author_posts_url = get_author_posts_url($key);
		      $post_count = $value;
		      if(!$user->user_firstname && !$user->user_lastname)
		      {
		      	$user->user_firstname = $user->user_login;
		      }
			  //replace anchors in usertemplate		
		      $output = str_replace("%linktopost%",get_bloginfo("wpurl") .'/author/'.str_replace(" ","-",$user->user_login),$template);
		      $output = str_replace("%firstname%",$user->user_firstname,$output);
		      $output = str_replace("%lastname%",$user->user_lastname,$output);
		      $output = str_replace("%nrofposts%",$post_count,$output);
		      
		      echo $output ."\n";
		    }
		    else
		    {
		    	break;
		    }
		  }
		  if($uc){echo "</ul>";}
		}

	
		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['template'] = $new_instance['template'];
		
		if(is_numeric($new_instance['number']))
		{
			if($new_instance['number'] <100 && $new_instance['number'] >0)
			{
				$instance['number'] =  $new_instance['number'];
			}
			else
			{
				if($new_instance['number'] < 1)
				{
					$instance['number'] = 1;
				}	
				else
				{
					$instance['number'] = 99;
				}
			
			}
		}

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {
		$defaults = array( 'title' => __('Top Authors', 'top_authors'), 'number' => __(5, 'top_authors'), 'template' => __('<li><a href="%linktoposts%">%firstname% %lastname% </a> number of posts: %nrofposts%</li>', 'top_authors'));
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e('Number of authors: (1-99)', 'top_authors'); ?></label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" value="<?php echo $instance['number']; ?>" style="width:100%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'template' ); ?>"><?php _e('HTML template', 'top_authors'); ?></label>
			<textarea id="<?php echo $this->get_field_id( 'template' ); ?>" name="<?php echo $this->get_field_name( 'template' ); ?>"  style="width:100%;height:100px;"><?php echo $instance['template']; ?></textarea>
		</p>

		
	<?php
	}
}

?>