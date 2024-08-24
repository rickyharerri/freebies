<?php
/**
 * Page Contents for Properties list Pages
 */

global $post;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'property-content clearfix' ); ?> >

	<div class="entry-content clearfix">

		<?php the_content(); ?>

	</div>
	<!-- /.entry-content clearfix -->

</article>
