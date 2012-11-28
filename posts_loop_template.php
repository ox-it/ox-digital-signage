<!-- Note: if you make changes to this file, move it to your current theme's
	directory so this file won't be overwritten when the plugin is upgraded. -->

<!-- This is the output of the post title -->
<h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

<!-- This is the output of the excerpt -->
<div class="entry-summary">
	<?php the_excerpt(); ?>
</div>

<!-- This is the output of the meta information -->
<div class="entry-utility">
	<?php if ( count( get_the_category() ) ) : ?>
		<span class="cat-links">
			<?php printf( __( '<span class="%1$s">Posted in</span> %2$s', 'twentyten' ), 'entry-utility-prep entry-utility-prep-cat-links', get_the_category_list( ', ' ) ); ?>
		</span>
		<span class="meta-sep">|</span>
	<?php endif; ?>
	<?php
		$tags_list = get_the_tag_list( '', ', ' );
		if ( $tags_list ):
	?>
		<span class="tag-links">
			<?php printf( __( '<span class="%1$s">Tagged</span> %2$s', 'twentyten' ), 'entry-utility-prep entry-utility-prep-tag-links', $tags_list ); ?>
		</span>
		<span class="meta-sep">|</span>
	<?php endif; ?>
	<span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'twentyten' ), __( '1 Comment', 'twentyten' ), __( '% Comments', 'twentyten' ) ); ?></span>
	<?php edit_post_link( __( 'Edit', 'twentyten' ), '<span class="meta-sep">|</span> <span class="edit-link">', '</span>' ); ?>
</div>