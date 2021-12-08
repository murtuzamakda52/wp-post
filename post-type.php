<?php 
/**
* Plugin Name: post-type
* Plugin URI: https://test-projext.000webhostapp.com/
* Description: This is the very first plugin I ever created and this is a unique plugin because using .
* Version: 1.0
* WC tested up to: 5.8.2
* Author: Murtuza Makda(idrish)
* Author URI: https://www.upwork.com/freelancers/~018f06972fe4607ad0
*License: GPL v3
* License URI: https://www.gnu.org/licenses/gpl-3.0.html
**/




function ajax_filter_posts_scripts() {
  wp_register_style('post-style', plugins_url('/assets/wp-post.css',__FILE__));
  wp_enqueue_style('post-style');
  wp_register_script('afp_script', plugins_url('/assets/ajax-filter-posts.js',__FILE__),array('jquery'), '', true);
  wp_enqueue_script('afp_script');

  wp_localize_script( 'afp_script', 'afp_vars', array(
        'afp_nonce' => wp_create_nonce( 'afp_nonce' ), // Create nonce which we later will use to verify AJAX request
        'afp_ajax_url' => admin_url( 'admin-ajax.php' ),
      )
  );
}
add_action('wp_enqueue_scripts', 'ajax_filter_posts_scripts', 100);


// Desable gutenberg editor for my custom post type
add_filter('use_block_editor_for_post_type', 'prefix_disable_gutenberg', 10, 2);
function prefix_disable_gutenberg($current_status, $post_type)
{
    if ($post_type === 'question-answer') return false;
    return $current_status;
}
add_action('init', 'create_custom_post_type');
 
function create_custom_post_type() {
$supports = array(
'title', // post title
'editor', // post content
'author', // post author
'thumbnail', // featured images
'excerpt', // post excerpt
'custom-fields', // custom fields
'comments', // post comments
'revisions', // post revisions
'post-formats', // post formats
);
 
$labels = array(
'name' => _x('question-answer', 'plural'),
'singular_name' => _x('question-answer', 'singular'),
'menu_name' => _x('question-answer', 'admin menu'),
'name_admin_bar' => _x('question-answer', 'admin bar'),
'add_new' => _x('Add question-answer', 'add new'),
'add_new_item' => __('Add New question-answer'),
'new_item' => __('New question-answer'),
'edit_item' => __('Edit question-answer'),
'view_item' => __('View question-answer'),
'all_items' => __('All question-answer'),
'search_items' => __('Search question-answer'),
'not_found' => __('No question-answer found.'),
);
 
$args = array(
'supports' => $supports,
'labels' => $labels,
'description' => 'Holds our question-answer and specific data',
'public' => true,
'taxonomies' => array( 'category', 'post_tag' ),
'show_ui' => true,
'show_in_menu' => true,
'show_in_nav_menus' => true,
'show_in_admin_bar' => true,
'can_export' => true,
'capability_type' => 'post',
'show_in_rest' => true,
'query_var' => true,
'rewrite' => array('slug' => 'question-answer'),
'has_archive' => true,
'hierarchical' => false,
'menu_position' => 6,
'menu_icon' => 'dashicons-megaphone',
);
 
register_post_type('question-answer', $args); // Register Post type
}
function mihdan_fix_syntaxhighlighter( $html ) {
    return preg_replace( '/&amp;([^;]+;)/', '&$1', $html );
}
add_filter( 'content_save_pre', 'mihdan_fix_syntaxhighlighter' );
add_filter( 'syntaxhighlighter_htmlresult', 'mihdan_fix_syntaxhighlighter' );
add_filter( 'syntaxhighlighter_precode', 'mihdan_fix_syntaxhighlighter' );


add_filter( 'template_include', 'my_plugin_templates' );
function my_plugin_templates( $template ) {
    $post_types = array( 'question-answer' );

    if ( is_post_type_archive( $post_types ) && file_exists( plugin_dir_path(__FILE__) . 'template-question-answer.php' ) ){
        $template = plugin_dir_path(__FILE__) . 'template-question-answer.php';
    }

    if ( is_singular( $post_types ) && file_exists( plugin_dir_path(__FILE__) . 'single-question-answer.php' ) ){
        $template = plugin_dir_path(__FILE__) . 'single-question-answer.php';
    }
    return $template;
}

add_action('admin_init','add_metabox_post_answer_widget');
add_action('save_post','save_metabox_post_answer_widget');
/*
* Funtion to add a meta box to enable Answer widget on posts.
*/
function add_metabox_post_answer_widget()
{
  add_meta_box("banner_image", "Answer", "enable_post_answer_widget", "question-answer", "normal", "high"); /* replace "post" with your custom post value(eg: "motors") */
}

function enable_post_answer_widget(){
    global $post;
	$image=get_post_custom($post->ID );
	$answer = $image['answer'][0];
	$settings = array(
						'media_buttons' => true, // show insert/upload button(s).
						'textarea_name' => 'answer',
						'textarea_rows' => get_option( 'default_post_edit_rows', 10 ),
						'tabindex'      => '',
						'teeny'         => true,
						'dfw'           => true,
						'tinymce'       => true,
						'quicktags'     => true,
					);
    $content = '';
    if ( isset( $answer ) ) {
	   $content = html_entity_decode( $answer );
    }
    echo wp_editor( $content, 'answer', $settings );
}

/*
* Save the meta box value of Answer widget on posts.
*/
function save_metabox_post_answer_widget($post_id)
{
    // Bail if we're doing an auto save
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

    // if our current user can't edit this post, bail
    if( !current_user_can( 'edit_post' ) ) return;

    $answer = isset($_POST['answer']) ? $_POST['answer']:'';

    update_post_meta( $post_id, 'answer', $answer );
}


// Script for getting posts
function ajax_filter_get_posts( $taxonomy ) {

  // Verify nonce
  if( !isset( $_POST['afp_nonce'] ) || !wp_verify_nonce( $_POST['afp_nonce'], 'afp_nonce' ) )
    die('Permission denied');

  $taxonomy = $_POST['taxonomy'];

  // WP Query
  $args = array(
    'category_name' => $taxonomy,
    'post_type' => 'question-answer',
    'posts_per_page' => -1,
    'order' =>'ASC',
  );
  echo '<h2 class="center-text">'.$taxonomy.'</h2>';
  // If taxonomy is not set, remove key from array and get all posts
  if( !$taxonomy ) {
    unset( $args['tag'] );
  }

  $query = new WP_Query( $args );

  if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); ?>
    <div class="single-post">
    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    <?php the_excerpt(); ?>
    </div>

  <?php endwhile; ?>
  <?php else: ?>
    <h2 class="center-text">No posts found</h2>
  <?php endif;

  die();
}

add_action('wp_ajax_filter_posts', 'ajax_filter_get_posts');
add_action('wp_ajax_nopriv_filter_posts', 'ajax_filter_get_posts');
?>