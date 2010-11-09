<?php
/**
 * Plugin Name: Top Authors
 * Plugin URI: http://developr.nl/work/top-authors
 * Description: A highly customizable widget that sums the top authors(most contributing) on your blog
 * Version: 0.4.1
 * Author: developR | Seb van Dijk
 * Author URI: http://www.developr.nl
 *
 */

/**
 * Add function to widgets_init that'll load top_authors
 */
add_action( 'widgets_init', 'top_authors' );

/**
 * Register our Top_Authors widget.
 *
 */
function top_authors() {
	register_widget( 'Top_Authors' );
}

/**
 * Top Authors Widget Class
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update.  Nice!
 *
 * For Top Authors I used a standard framework for this :) thanx
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
	 * This is the part where the heart of this widget is!
	 * here we get al the authors and count their posts. 
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. (nice tabbed huh!?)*/
		$title = 				apply_filters('widget_title', $instance['title'] );
		$number_of_authors = 	$instance['number'];
		$template = 			htmlspecialchars_decode($instance['template']);
		$before_the_list =		htmlspecialchars_decode($instance['before']);
		$after_the_list = 		htmlspecialchars_decode($instance ['after']);
		$gravatar_size =		$instance['gravatar_size'];
		
		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;

		$user_list=array();
		
		$blogusers = get_users_of_blog(); // doh
			
		// this part can be a heavyload process if you have a lot of authors
		// use a plugin like W3 cash to solve this. 
		
 		if ($blogusers) {
		  foreach ($blogusers as $bloguser) {
		    $user_list[]=$bloguser->user_id;
		   }

		 // replaced deprecated wp-function (http://codex.wordpress.org/Function_Reference/get_usernumposts)
		 $posts = count_many_users_posts($user_list);
		 
		 arsort($posts); //use asort($user_list) if ascending by post count is desired
		  
		  // user defined html elemnt before the list
		  if($user_list){echo $before_the_list;}
		
		  if(count($user_list)<$number_of_authors)
		  {
		  	$number_of_authors=count($user_list);
		  }
		 
		  
		  for ( $counter = 0; $counter <= $number_of_authors-1	; $counter += 1) {

			  //user are saved in array from 0 to ... x so we can use the counter
			  $user = get_userdata($user_list[$counter]);		  
			  
			  
			  $author_posts_url = get_author_posts_url($key);
			 
			  if(!$user->user_firstname && !$user->user_lastname)
			  {
			  	$user->user_firstname = $user->user_login;
			  }    
			  //replace anchors in usertemplate		
			  $output = str_replace("%linktoposts%",get_bloginfo("wpurl") .'/author/'.str_replace(" ","-",$user->user_login),$template);
			  $output = str_replace("%firstname%",$user->user_firstname,$output);
			  $output = str_replace("%lastname%",$user->user_lastname,$output);
			  $output = str_replace("%nrofposts%",$posts[$user->ID],$output);
			  
			  $gravatar_detect = strpos($output,"%gravatar%");
			  
			  if($gravatar_detect !== false){
			 	$gravatar = get_avatar($user->ID, $gravatar_size);
			 	 $output = str_replace("%gravatar%",$gravatar,$output);
			  }
			  
			  // newline in html, al  for the looks!
			  echo $output ."\n";
			}
	
		  // user defined html after the list
		  if($user_list){echo $after_the_list;}
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
		$instance['title'] = 			strip_tags( $new_instance['title'] );
		
		// htmlspecialchars to save html markup in database, at frontend we use htmlspecialchars_decode
		$instance['template'] = 		htmlspecialchars($new_instance['template']);
		$instance['before'] = 			htmlspecialchars($new_instance['before']);
		$instance['after'] = 			htmlspecialchars($new_instance['after']);
		
		// check if datainput isnummeric
		if(is_numeric($new_instance['gravatar_size']))
		{
			$instance['gravatar_size'] = 	$new_instance['gravatar_size'];
		}
		
		// check if datainput isnummeric and postive and under 100
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
		$defaults = array( 
			'title' => __(	'Top Authors', 'top_authors'), 
							'number' => __(5, 'top_authors'), 
							'template' => __('<li><a href="%linktoposts%">%gravatar% %firstname% %lastname% </a> number of posts: %nrofposts%</li>', 'top_authors'),
							'before' => __('<ul>', 'top_authors'),
							'after' => __('</ul>', 'top_authors'),
							'gravatar_size' => __(24)
						);
						
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>Thank you for using this widget, please, give me some <a href="mailto:feedback@developr.nl">feedback</a>! </p>	
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:96%;float:right;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e('Number of authors: (1-99)', 'top_authors'); ?></label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" value="<?php echo $instance['number']; ?>" style="width:96%;float:right;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'template' ); ?>"><?php _e('HTML template use: (%linktoposts% | %firstname% | %lastname% | %nrofposts% | <strong>%gravatar%</strong>)', 'top_authors'); ?></label>
			<textarea id="<?php echo $this->get_field_id( 'template' ); ?>" name="<?php echo $this->get_field_name( 'template' ); ?>"  style="width:100%;height:100px;"><?php echo $instance['template']; ?></textarea>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'before' ); ?>"><?php _e('before the list', 'top_authors'); ?></label>
			<input id="<?php echo $this->get_field_id( 'before' ); ?>" name="<?php echo $this->get_field_name( 'before' ); ?>" value="<?php echo $instance['before']; ?>" style="width:50%;float:right;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'after' ); ?>"><?php _e('after the list', 'top_authors'); ?></label>
			<input id="<?php echo $this->get_field_id( 'after' ); ?>" name="<?php echo $this->get_field_name( 'after' ); ?>" value="<?php echo $instance['after']; ?>" style="width:50%;float:right;" />
		</p>
			<p>
			<label for="<?php echo $this->get_field_id( 'gravatar_size' ); ?>"><?php _e('size of gravatar', 'top_authors'); ?></label>
			<input id="<?php echo $this->get_field_id( 'gravatar_size' ); ?>" name="<?php echo $this->get_field_name( 'gravatar_size' ); ?>" value="<?php echo $instance['gravatar_size']; ?>" style="width:50%;float:right;" />
		</p>
			
	<?php
	}
}
?>