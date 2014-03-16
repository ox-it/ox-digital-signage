<?php

/**
    Plugin name: Oxford Digital Signage (ox-digital-signage)
    Plugin URI: FIXME
    Author: Oxford University IT Services (Guido Klingbeil, Marko Jung)
    Author URI: http://www.it.ox.ac.uk
    Description: Displays a single post from a given category in a page as digital sign. The post is selected in a round robin fashion where the user is able to specify that only the first n posts of the category are to be circled.
    Version: 0.5
    License: GPLv3

    Shortcode usage:
    [oxds_add_sign]  - Add all posts to a page (limit to what number posts in WordPress is set to).  This essentially makes a post look like a sign.
**/

define('OX_DIGITAL_SIGNAGE_VERSION', '0.5');

class oxDigitalSignage{

    protected $args;

    public function __construct(){
        /*
           Add the Wordpress shortcode.
           There are no parameters since the configuration
           is handled by a metabox.
        */
        add_shortcode( 'oxds_add_sign', array( &$this, 'oxdsDisplaySign' ) );

        /*
           Add actions to display the configuration metabox, save the options,
           and add the refresh metatag to the HTML header
        */
        add_action('add_meta_boxes', array( &$this, 'oxdsAddConfigBox') );
        add_action('save_post', array( &$this, 'oxdsSaveOptions') );
        add_action('wp_head', array( &$this, 'oxdsRefreshMetaTag') );

        /*
           Retrieve the number of the post currently displayed.
        */
        add_filter('query_vars', array( &$this, 'oxdsQueryVars') );

        /*
          change 15.3.2014: add custom BSG columns to the show all posts table
          register our new funtions
        */
        add_action( 'manage_posts_custom_column', array( $this, 'oxdsFillColumns'), 10, 2 );
        add_filter( 'manage_posts_columns', array( $this, 'oxdsAddColumns') );
    }


    /*
        Simply return the number of the post currently displayed as an array.
    */
    public function oxdsQueryVars( $queryVars )
    {
        $queryVars[] = 'current';

        return $queryVars;
    }


    /*
       Core method of the Digital Signage plugin.
       This method displays a single post of a specified category as digital sign.
       If there are more than one posts available, the number of posts specified
       by modulo are circled.
    */
    public function oxdsDisplaySign() {
        global $wp_query;

	$pageObject = $wp_query->get_queried_object();
        $optionsArray = unserialize(get_post_meta($pageObject->ID, 'optionsArray', true));
	$this->args['category_name'] = $optionsArray['category'];

        $this->args['posts_per_page'] = 1;

        /*
           Check if we got a VIP post
         */

        $query = new WP_Query( array_merge( (array)($this->args), array('post_type' => 'post', 'meta_key' => 'oxdsPostType', 'meta_value' => 'fullscreen_post', 'orderby' => 'date', 'order' => 'DESC') ) );
        $available_vip_posts = $query->post_count;

        /* if there is a VIP post display it, otherwise use the old scheduling logic */
        if($available_vip_posts > 0) {
            $page_posts = $query;
        } else {

            /*
               Post might have been disabled or deleted between function calls
               I am just refreshing the number of available posts to display.
            */
            $query = new WP_Query( 'category_name=' . $optionsArray['category'] );
            $available_posts = $query->post_count;

	    if (isset($wp_query->query_vars['current']) & $available_posts > intval($wp_query->query_vars['current']))
	    {
	        $current = intval( $wp_query->query_vars['current'] ) + 1;
	    } else {
                $current = 1;
            }
            $this->args['paged'] = $current;

            $page_posts = new WP_Query( $this->args );
        }

        // common code
        $output = self::oxdsTemplatePart( $page_posts );
        wp_reset_postdata();

        return $output;
    }


    /*
        Return true if we have a theme, false otherwise.
    */
    protected function oxdsHasTheme(){
        $template_file = ( $this->args['template'] ) ? self::oxdsGetThemePath()  . '/' . $this->args['template'] : self::oxdsGetThemePath() . '/posts_loop_template.php';

        return ( file_exists( $template_file ) ) ? $template_file : false;
    }


    protected function oxdsTemplatePart( $ic_posts, $singles=false ){
        if( $singles ){
            setup_postdata( $ic_posts );
        }else{
            $ic_posts->the_post();
        }
        ob_start();
        require ( $file_path = self::oxdsHasTheme() ) ? str_replace( site_url(), '', $file_path ) : 'posts_loop_template.php';
        $output .= ob_get_contents();

        return ob_get_clean();
    }


    /*
        Return the path to the current theme.
    */
    protected function oxdsGetThemePath(){
        $theme_data = explode( '/', get_bloginfo( 'stylesheet_directory' ) );
        $theme_path = get_theme_root();

        return $theme_path . '/' . $theme_data[ count( $theme_data ) -1 ];
    }


    /*
        Add a metabox to the admin panel setting the configuration options.
     */
    public function oxdsAddConfigBox() {
	add_meta_box(
	    'oxdsPopulateOptionsBox',
	    'Digital signage',
	    array(&$this, 'oxdsPopulateOptionsBox' ),
	    'page',
	    'side',
	    'low'
	);

        /* 
             add new meta box for the post type 
             this one is addecd to the post rather than to the page
         */
        add_meta_box(
	    'oxdsPopulatePostOptionsBox',
	    'Digital signage',
	    array(&$this, 'oxdsPopulatePostOptionsBox' ),
	    'post',
	    'side',
	    'high'
	);


    }


    /*
       This is the second core method of this plugin inserting the
       refresh meta tag code into the HTML header.
    */
    public function oxdsRefreshMetaTag() {
	global $wp_query;
	$pageObject = $wp_query->get_queried_object();
        $optionsArray = unserialize(get_post_meta($pageObject->ID, 'optionsArray', true));

	/*
            Ensure we have a current value for meta refresh
            otherwise default to 0.
        */
	if (isset($wp_query->query_vars['current']))
	{
	    $current = abs(intval($wp_query->query_vars['current']));
        } else {
	    $current = 0;
        }

	/*
            Get the number of posts in a category and if there are less 
            posts than the maximum number of posts to be circled,
            we circle all available posts.
        */
	$query = new WP_Query( 'category_name=' . $optionsArray['category'] );
        $modulo = min(intval($optionsArray['modulo']), $query->post_count);
        $current = ($current + 1) % $modulo;

	if ( preg_match('/current=(\S*)/', $_SERVER["REQUEST_URI"] ) ) { 
	    $URI = preg_replace('/current=(\S*)/', 'current=' . $current, $_SERVER["REQUEST_URI"] );
	} else {

            /* 
             * If the Wordpress configuration uses the postname rather than 
             * the default setting, 'current' is the first parameter of the URL
             * and we need to add a ? instead of a &.
             */
	    if ( substr_count( $_SERVER["REQUEST_URI"], '?' ) ) {
 	        $URI = $_SERVER["REQUEST_URI"] . '&current=1';
 	    } else {
 	        $URI = $_SERVER["REQUEST_URI"] . '?current=1';
 	    }
	}
	$URL = $_SERVER["HTTP_HOST"] . $URI;

        /*
            Generate the  HTML code and insert it into the HTML header.
        */
	if (!is_admin()) {
	    if (intval($optionsArray['seconds']) && intval($optionsArray['seconds'] > 0)) {
	        echo '<meta http-equiv="refresh" content="' . 
                    $optionsArray['seconds'] . ';URL=http://'. $URL . '" />';
                /*
                    // debug output:
                    echo '<meta debug current: ' . $current . ' />';
                    echo '<meta debug number of posts: ' . $query->post_count . ' />';
		    $optionsArray = unserialize(get_post_meta($pageObject->ID, 'optionsArray', true));
		    echo '<meta debug category: ' . $optionsArray['category'] . ' />';
                */
	    }
	}
    }


    /*
        This methods populates the configuration option box
        by simply generating the HTML code.
        Options implemented:
        seconds   The seconds between screen refreshes
        modulo    The number of posts we are cycling
        category  The category we want to show
    */
    public function oxdsPopulateOptionsBox() {
	global $post;

	$optionsArray = unserialize(get_post_meta($post->ID, 'optionsArray', true));

	echo '<input type="hidden" name="oxdsName" id="oxdsName" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';

        /* Refresh time */
	echo '<p><label>Seconds to refresh sign after?</label> <input type="text" name="optionsArray[seconds]" id="oxdsSeconds" value="' . $optionsArray['seconds'] . '" style="width: 50px;" /></p>';
	echo '<p class="description">Default is 20 sec.</p>';

        /* Number of posts to circle */
	echo '<p><label>Signs to circle?</label> <input type="text" name="optionsArray[modulo]" id="oxdsSeconds" value="' . $optionsArray['modulo'] . '" style="width: 50px;" /></p>';
	echo '<p class="description">Default is 10 (last ten signs).</p>';

        /* Category of the posts */
        /* note: by default wordpress is hiding categories with no posts
           To include empty categories to the list use
           get_categories( array('hide_empty' => 0 ) ); instead.
	*/
	echo '<p><label>Category:</label> <select name="optionsArray[category]" id="oxdsSeconds" />';
  	$categories=  get_categories();
  	foreach ($categories as $category) {
  	    $option = '<option value="' . $category->category_nicename . '"';
	    if ( $optionsArray['category'] == $category->category_nicename ) {
	        $option .= ' selected ';
	    }
	    $option .= '>';
	    $option .= $category->cat_name;
	    $option .= ' ('.$category->category_count.')';
	    $option .= '</option>';
	    echo $option;
	}
	echo '</select></p>';

	/* Location of the display */
	echo '<p><label>Location of the display?</label> <input type="text" name="optionsArray[location]" id="oxdsLocation" value="' . $optionsArray['location'] . '" style="width: 100px;" /></p>';
	echo '<p class="description">Default is empty.</p>';
    }
    

   /*
        This methods populates the configuration option box
        by simply generating the HTML code.
        Options implemented:
        seconds   The seconds between screen refreshes
        modulo    The number of posts we are cycling
        category  The category we want to show
    */
    // this function is only registered for the "post" post type, 
    // so we don't have to check for it
    public function oxdsPopulatePostOptionsBox() {
	global $post;
	
        // get the post meta data, request only the first entry with the key 'oxdsPostType'
        $oxdsPostType = get_post_meta($post->ID, 'oxdsPostType', true);
        error_log( "oxdsPopulatePostOptionsBox: post ID: ".$post->ID.", saved post type: ".$oxdsPostType."." );

        echo '<input type="hidden" name="oxdsName" id="oxdsName" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';

	/* the post types to be implemented:
	   - default: a standard post
	   - vip post,
	   -image post.
	*/
        //$post_types = array( 'default', 'VIP', 'image post' );
	$post_types = array( 'default_post', 'fullscreen_post', 'image_post' );
        $post_type_desc = array('default_post' => 'Default post', 'fullscreen_post' => 'Fullscreen priority post', 'image_post' => 'Image post');
        //$default = 'default_post';
    
	echo '<p><label>Post type:</label> <select name="oxdsPostType" id="oxdsPostType" />';
  	foreach ($post_types as $post_type) {
  	    $option = '<option value="' . $post_type . '"';

            // retrieve existing value from post meta data
            if ( $post_type == $oxdsPostType ) {
	        $option .= ' selected ';
                error_log( "selected: ".$post_type."\n\n\n\n\n\n" );
	    }

	    $option .= '>';
	    $option .= $post_type_desc[ $post_type ];
	    $option .= '</option>';
	    echo $option;
	}
	echo '</select></p>';
    }


    /*
        This method saves the options. If values are missing
        or invalid, meaningful defaults are chosen.
    */

    // TODO: I need to distimguish post types: post, page
    // if it is a page, we save the rotation data using the optionsArray
    // if it is a post, we store the postType in the meta data

    public function oxdsSaveOptions($post_id) {
	global $post;

	if (!wp_verify_nonce($_POST["oxdsName"], plugin_basename(__FILE__))) {
	    return $post_id;
        }

	if ( !current_user_can( 'edit_post', $post_id )) {
	    return $post_id;
        }

        // get the post type (page, post, or unknown)
        if( get_post_type($post_id) == 'post' || get_post_type($post_id) == 'revision') {
            error_log( "oxdsSaveOptions: post type is: " .$_POST["oxdsPostType"]. ".\n\n\n\n\n\n" );

            // add the postType to the post meta data
            // if it is not set, set it to default
            // since this means something went wrong, we clean-up the potentially corrupted data
            if( $_POST["oxdsPostType"] == '') {
                $_POST["oxdsPostType"] = 'default_post';
                //delete_post_meta($post_id, 'oxdsPostType' );
            } 
            update_post_meta($post_id,'oxdsPostType', ($_POST["oxdsPostType"]));
 
        } elseif ( get_post_type($post_id) == 'page' ) {
            error_log( "oxdsSaveOptions: post type is: page.\n\n\n\n\n\n\n\n" );

            // Retrieve the options array from the post
            $optionsArray = $_POST['optionsArray'];
       
	    // New, Update, and Delete
	    if (empty($optionsArray)) {
	        delete_post_meta(
	        $post_id,
	        'optionsArray',
	        get_post_meta($post_id, 'optionsArray', true)
	        );
                return;
	    }

            /*
                The option seconds give the refresh time of the digital sign.
                We enforce a minimum value of 1 second. If no refresh time is given
                we default to a refresh time of 20 seconds.
             */
	    if (!intval($optionsArray['seconds']) || intval($optionsArray['seconds']) <= 0) {
	        $optionsArray['seconds'] = 20;
            }

            /*
                The option modulo defines the number of posts to be circled as digital signs.
                We enforce a minimum value of 1. This means that only the first post is displayed.
            */
	    if (!intval($optionsArray['modulo']) || intval($optionsArray['modulo']) <= 0) {
                $optionsArray['modulo'] = 10;
	    }

            /*
                The category of the posts to be displayed.
                If no category is given, we default to the first category available.
             */
	    if ( $optionsArray['category'] == '' ) {
	        $tmp = get_categories();
                $optionsArray['category'] = $tmp[0]->category_nicename;
	    }

            if (!add_post_meta($post_id, 'optionsArray', serialize($optionsArray), true))
                update_post_meta($post_id, 'optionsArray', serialize($optionsArray));
        }
    } // end of oxdsSaveOptions

    /*
       Change 15.3.2014 new code to insert and display custom columns
       to the view all posts table.
     */
    function oxdsAddColumns( $columns ) {
        // remove unwanted columns tags, date, and comments
        unset($columns[ 'tags' ]);
        unset($columns[ 'date' ]);
        unset($columns[ 'comments' ]);
        unset($columns[ 'author' ]);

        // add new columns Type, Start, End, Status
        // and re-insert Author
        $columns[ 'type' ] = 'Type';
        $columns[ 'start' ] = 'Start';
        $columns[ 'end' ] = 'End';
        $columns[ 'author' ] = 'Author';
        $columns[ 'status' ] = 'Status';

        // rename categories to screen
        $columns[ 'categories' ] = 'Screen';

        return $columns;
    }

    function oxdsFillColumns( $name ) {
        //error_log('In function my_show_columns.');
        global $post;
        //error_log( print_r( get_post_meta( $post->ID), true) );
        switch( $name ) {
            case 'status':
                if( strcmp( $post->post_status, 'draft') == 0 ) {
                    echo 'disabled';
                } elseif( strcmp( $post->post_status, 'publish') == 0 ) {
                    echo 'enabled';
                } else {
                    echo $post->post_status;
                }
            break;
            case 'type':
                $stmp = get_post_meta( $post->ID, 'oxdsPostType', true);
                if( strcmp( $stmp, 'default_post') == 0 ) {
                    echo 'Default post';
                } elseif( strcmp( $stmp, 'fullscreen_post') == 0 ) {
                    echo 'Fullscreen priority post';
                } elseif( strcmp( $stmp, 'image_post') == 0 ) {
                    echo 'Image post';
                } else {
                    echo 'Default post';
                }
            break;
            case 'start':
                echo get_post_meta( $post->ID, 'ox-enable-date', true );
            break;
            case 'end':
                echo get_post_meta( $post->ID, 'ox-disable-date', true );
            break;
        }
    }

} new oxDigitalSignage();

?>
