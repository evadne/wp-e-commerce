<?php
/**
* wpsc has breadcrumbs function
* @return boolean - true if we have and use them, false otherwise
*/
function wpsc_has_breadcrumbs() {
	global $wpsc_query, $wpsc_breadcrumbs;	
	$wpsc_breadcrumbs = new wpsc_breadcrumbs();
	if(($wpsc_breadcrumbs->breadcrumb_count > 0) && (get_option("show_breadcrumbs") == 1)){
		return true;
	} else {
		return false;
	}
}

/**
* wpsc have breadcrumbs function
* @return boolean - true if we have breadcrumbs to loop through
*/
function wpsc_have_breadcrumbs() {
	global $wpsc_query, $wpsc_breadcrumbs;	

	return $wpsc_breadcrumbs->have_breadcrumbs();
}

/**
* wpsc the breadcrumbs function
* @return nothing - iterate through the breadcrumbs
*/
function wpsc_the_breadcrumb() {
	global $wpsc_query, $wpsc_breadcrumbs;	

	$wpsc_breadcrumbs->the_breadcrumb();
}

/**
* wpsc breadcrumb name function
* @return string - the breadcrumb name 
*/
function wpsc_breadcrumb_name() {
	global $wpsc_query, $wpsc_breadcrumbs;	

	return $wpsc_breadcrumbs->breadcrumb['name'];
}

/**
* wpsc breadcrumb URL function
* @return string - the breadcrumb URL
*/
function wpsc_breadcrumb_url() {
	global $wpsc_query, $wpsc_breadcrumbs;	

	if($wpsc_breadcrumbs->breadcrumb['url'] == '') {
		return false;
	} else {
		return $wpsc_breadcrumbs->breadcrumb['url'];
	}
}


/**
 * wpsc_breadcrumbs class.
 * 
 */
class wpsc_breadcrumbs {
	var $breadcrumbs;
	var $breadcrumb_count = 0;
	var $current_breadcrumb = -1;
	var $breadcrumb;

	/**
	 * wpsc__breadcrumbs function.
	 * 
	 * @access public
	 * @return void
	 */
	function wpsc_breadcrumbs() {
		global $wp_query;
		$this->breadcrumbs = array();

		
		$query_data = array(
			'category' =>  $wp_query->query_vars['products'],
			'product' =>  $wp_query->query_vars['name']
		);
		
		
		if(!empty($query_data['product']) && !empty($wp_query->post)) {
			$this->breadcrumbs[] = array(
				'name' => htmlentities($wp_query->post->post_title, ENT_QUOTES, 'UTF-8'),
				'url' => ''// get_permalink($wp_query->post->ID)
			);
		
		}
		
		
		$term_data = get_term_by('slug', $query_data['category'], 'wpsc_product_category');
		if( $term_data != false) {
			$this->breadcrumbs[] = array(
				'name' => htmlentities( $term_data->name, ENT_QUOTES, 'UTF-8'),
				'url' => get_term_link( $term_data->slug, 'wpsc_product_category')
			);
			
			$i = 0;
			
			while(($term_data->parent > 0) && ($i <= 20)) {
				$term_data = get_term($term_data->parent, 'wpsc_product_category');
				$this->breadcrumbs[] = array(
					'name' => htmlentities( $term_data->name, ENT_QUOTES, 'UTF-8'),
					'url' => get_term_link( $term_data->slug, 'wpsc_product_category')
				);
				$i++;
			}
		}
		$this->breadcrumbs = array_reverse($this->breadcrumbs);
		$this->breadcrumb_count = count($this->breadcrumbs);
	}
	
	/**
	 * next_breadcrumbs function.
	 * 
	 * @access public
	 * @return void
	 */
	function next_breadcrumbs() {
		$this->current_breadcrumb++;
		$this->breadcrumb = $this->breadcrumbs[$this->current_breadcrumb];
		return $this->breadcrumb;
	}

	
	/**
	 * the_breadcrumb function.
	 * 
	 * @access public
	 * @return void
	 */
	function the_breadcrumb() {
		$this->breadcrumb = $this->next_breadcrumbs();
	}

	/**
	 * have_breadcrumbs function.
	 * 
	 * @access public
	 * @return void
	 */
	function have_breadcrumbs() {
		if ($this->current_breadcrumb + 1 < $this->breadcrumb_count) {
			return true;
		} else if ($this->current_breadcrumb + 1 == $this->breadcrumb_count && $this->breadcrumb_count > 0) {
			//do_action('wpsc_loop_end');
			// Do some cleaning up after the loop,
			$this->rewind_breadcrumbs();
		}

		//$this->in_the_loop = false;
		return false;
	}

	/**
	 * rewind_breadcrumbs function.
	 * 
	 * @access public
	 * @return void
	 */
	function rewind_breadcrumbs() {
		$this->current_breadcrumb = -1;
		if ($this->breadcrumb_count > 0) {
			$this->breadcrumb = $this->breadcrumbs[0];
		}
	}	

}

?>