<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> >
    <section class="section-white small-padding-bottom home-pricing">
        <div class="container">
            <?php echo do_shortcode('[su_tabs style="default" active="1" vertical="no" class=""]'
                .'[su_tab title="CẤP MỚI" disabled="no" anchor="" url="" target="blank" class=""]'. do_shortcode("[wptm id=1]").'[/su_tab]'
                .'[su_tab title="GIA HẠN" disabled="no" anchor="" url="" target="blank" class=""]'. do_shortcode("[wptm id=4]").'[/su_tab]'
                .'[su_tab title="CHUYỂN ĐỔI" disabled="no" anchor="" url="" target="blank" class=""]'. do_shortcode("[wptm id=6]").'[/su_tab]'
                .'[su_tabs]');
            ?>
        </div><!-- /.container -->
    </section>
</article>