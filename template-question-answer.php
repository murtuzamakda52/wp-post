<?php
get_header();

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$args = array(
            'post_type' => 'question-answer',
            'posts_per_page' => 10,
            'order' =>'ASC',
            'paged' => $paged,
        );

        $query = new WP_Query( $args );

        $tax = 'category';
        $terms = get_terms( $tax );
        $count = count( $terms );

        if ( $count > 0 ): ?>
            <div class="post-tags">
            <a href="" class="tax-filter" title="">all</a>
            <?php
            foreach ( $terms as $term ) {
                $term_link = get_term_link( $term, $tax );
                echo '<a href="' . $term_link . '" class="tax-filter" title="' . $term->slug . '">' . $term->name . '</a> ';
            } ?>
            </div>
        <div>
        <div style="width:200px; margin:auto;margin-top:40px">
        </div>
        <?php endif;
        if ( $query->have_posts() ): ?>
        <div class="tagged-posts">
            <?php while ( $query->have_posts() ) : $query->the_post(); ?>
            <div class="single-post">
            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            <?php the_excerpt(); ?>
            </div>

            <?php endwhile; ?>
        </div>

        <?php else: ?>
            <div class="tagged-posts">
                <h2>No posts found</h2>
            </div>


        <div style="width:200px; margin:auto">
        </div>
    </div>
        <?php endif; 

    $total_pages = $query->max_num_pages;
    if ($total_pages > 1){

        $current_page = max(1, get_query_var('paged'));
        ?>
        <div class="pagination">
        <?php
        echo paginate_links(array(
            'base' => get_pagenum_link(1) . '%_%',
            'format' => '/page/%#%',
            'current' => $current_page,
            'total' => $total_pages,
            'prev_text'    => __('« prev'),
            'next_text'    => __('next »'),
        ));
        ?>
    </div>
    <?php
    }    
get_footer();
?>