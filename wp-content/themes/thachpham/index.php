<?php get_header(); ?>
    <div class="section-hero">
        <div class="container">
        <div id="mainSlider" class="carousel slide" data-ride="carousel" data-interval="3500">
            <div class="carousel-inner" role="listbox">
                <div class="item active">
                    <a href="https://chukyso24h.vn/chuyen-muc/26/bao-gia.html">
                        <img class="d-block img-fluid" src="https://chukyso24h.vn/public/uploads/system/slideshow/slide-cks-11.png"
                                                                                     alt="Chữ ký số giá rẻ">
                    </a></div>
            </div>
        </div>
        </div>
    </div>
    <div style="clear: both"></div>
<?php get_template_part('content', 'banggia'); ?>
    <div style="clear: both"></div>
    <div class="container">
        <div id="main-content">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                <?php get_template_part('content', get_post_format()); ?>
            <?php endwhile ?>
                <?php thachpham_pagination(); ?>
            <?php else: ?>
                <?php get_template_part('content', 'none'); ?>
            <?php endif; ?>
        </div>
        <div id="sidebar">
            <?php get_sidebar(); ?>
        </div>
    </div>
    <div style="clear: both"></div>
<?php get_footer(); ?>