<?php
	/* 
	 Template Name: Contact
	 */
?>

<?php get_header(); ?>
<div class="content">
	<div id="main-content">
		<div class="contact-info">
			<h4>Địa chỉ liên hệ</h4>
			<p>Kungsgatan 13 LGH 1202, 64130 Katrineholm. Sweden</p>
			<p>076.222.7374</p>
		</div>
		<div class="contact-form">
			<?php echo do_shortcode('[contact-form-7 id="1395" title="Contact form 1"]'); ?>
		</div>
	</div>
	<div id="sidebar">
		<?php get_sidebar(); ?>
	</div>
</div>
<?php get_footer(); ?>
