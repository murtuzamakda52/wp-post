<?php
// single-news.php
get_header();
/* Start the Loop */
while (have_posts()) : the_post();
   ?>
   
<div class="qa-title"><?php the_title(); ?></div>
	<div class="qa-question">
      

         <?php the_content(); // Dynamic Content 

      ?>


</div>

<div class="braker">
   <center>Answer</center>
</div>
<div class="qa-answer">

<?php
$answer = get_post_custom($post->ID); 


 //$my_custom_content = isset( $answer['answer'] ) ? esc_attr( $answer['answer'][0] ) : '';
    
echo apply_filters( 'the_content',  esc_attr( $answer['answer'][0]) );

    
   		
    //$content = $custom_shortcode_output;
    
    







?>
   

</div>









   <?php
endwhile; // End of the loop.
get_footer();