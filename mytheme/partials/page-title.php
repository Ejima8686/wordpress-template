<?php
$title = get_query_var('title', '');
$description = get_query_var('description', '');
?>
<div style="padding-top: 4rem; padding-bottom: 4rem; text-align: center;">
	<h1 style="width: 100%; font-size: 3rem; color: #334155; font-weight: bold;"><?php echo esc_html($title); ?></h1>
	<?php if ($description): ?>
		<p style="width: 100%; max-width: 42rem; margin-top: 1.5rem; margin-left: auto; margin-right: auto;">
			<?php echo esc_html($description); ?>
		</p>
	<?php endif; ?>
</div>
