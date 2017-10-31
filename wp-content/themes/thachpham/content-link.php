<article id="post-<?php the_ID();?>" <?php post_class(); ?> >
	<div class="entry-thumbnail">
		<?php thachpham_thumbnail('thumbnail'); ?>
	</div>
	<div class="entry-header">
		<?php
			$link = get_post_meta( $post->ID, 'format_link_url', true );
			$link_description = get_post_meta( $post->ID, 'format_link_description', true );

			if( is_single() ) {
				printf( '<h1 class="entry-title"><a href="%1$s" target="blank">%2$s</a></h1>',
				$link,
				get_the_title() );
			} else {
				printf( '<h2 class="entry-title"><a href="%1$s" target="blank">%2$s</a></h2>',
				$link,
				get_the_title() );
			}
		?>
	</div>
	<div class="entry-content">
		<?php
			printf( '<a href="%1$s" target="blank">%2$s</a>',
			$link, $link_description );
		?>
		<?php ( is_single() ? thachpham_entry_tag() : '' ); ?>
	</div>
</article>