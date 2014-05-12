<div class="exams_container">
	<section class="title">
		<h2><?php echo htmlspecialchars( $title ) ?></h2>
		<p><?php echo htmlspecialchars( $text ) ?></p>
		<?php
			if ( $score >= $this->config->item('exams.passing_score') )
			{
				echo '<p>' . htmlspecialchars( lang('exams:download_your')) . ' <a href="' . site_url( $certificate_uri ) . '" title="' . htmlspecialchars( lang('exams:download_your')) . ' ' . htmlspecialchars( lang('exams:printable_link')) . '">' . htmlspecialchars( lang('exams:printable_link')) . '</a></p>';
			}
			else
			{
				echo '<p><a href="' . site_url( $sit_uri ) . '" title="">Change your answers and try again</a></p>';
			}
		?>
		<pre style="font-family: monospace;"><?php echo htmlspecialchars( $detailed_report ) ?></pre>
	</section>
</div>