<div id="exams-content">

	<div class="one_full">
		<section class="title">
			<h2><?php echo htmlspecialchars( lang('exams:available_exams')) ?></h2>
		</section>

		<?php if( empty($data_published_exams) ): ?>
	
			<p><?php echo htmlspecialchars( lang('exams:no_exams_found')); ?></p>
	
		<?php else: ?>

			<p><?php echo htmlspecialchars( lang('exams:available_exams_intro')); ?></p>
			
			<ul>
				<?php foreach($data_published_exams as $data): ?>
					<li><?php
						if( $data['enabled'] == 1 )
						{
							echo '<a href="' . current_url() . '/' . $data['quizid'] . '" title="' . htmlspecialchars( lang('exams:frontend_link_title')) . '">' . htmlspecialchars( $data['name'] ) . '</a>';
						}
						else
						{
							echo '<span class="exams-note confirm" title="' . htmlspecialchars( lang('exams:disabled_help')) . '">' . htmlspecialchars( $data['name'] ) . ' (coming soon)</span>';
						}
					?></li>
				<?php endforeach; ?>
			</ul>
	
		<?php endif; ?>

	</div>
</div>