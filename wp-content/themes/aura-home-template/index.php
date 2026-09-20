<?php
/**
 * Default fallback template.
 */
get_header();
?>
<main class="spa-shell">
    <div class="spa-container" style="padding: 4rem 0;">
        <?php
        if (have_posts()) :
            while (have_posts()) :
                the_post();
                the_content();
            endwhile;
        else :
            echo '<h1>Welcome to Aura Spa</h1>';
            echo '<p>This theme is ready to use as a homepage template.</p>';
        endif;
        ?>
    </div>
</main>
<?php
get_footer();
