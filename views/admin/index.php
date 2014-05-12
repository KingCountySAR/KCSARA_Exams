</pre>
<div id="exams-content">

	<?php if( ! empty($data_published_exams) ): ?>

		<div class="one_full">
			<section class="title">
				<h4><?php echo lang('exams:published_exams') ?></h4>
			</section>

			<section class="item">
				<div class="content">
					<table class="table-list" cellspacing="0">
						<thead>
							<tr>
								<th><?php echo lang('exams:name') ?></th>
								<th class="collapse"><span><?php echo lang('exams:description') ?></span></th>
								<th><?php echo lang('exams:version') ?></th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($data_published_exams as $data): ?>
								<tr>
									<td class="collapse">
										<?php
											if( $data['enabled'] == 1 )
											{
												echo '<a href="' . site_url('exams/' . $data['quizid']) . '" title="' . lang('exams:frontend_link_title') . '">' . $data['name'] . '</a>';
											}
											else
											{
												echo $data['name'];
											}
										?>
									</td>
									<td><?php echo $data['description'] ?></td>
									<td class="align-center"><?php echo $data['version'] ?></td>
									<td class="actions">
										<a href="<?php echo site_url('admin/exams/' . $data['quizid']) ?>" title="<?php echo lang('exams:edit_link_title')?>" class="button confirm"><?php echo lang('exams:edit')?></a>
										<a href="<?php echo site_url('admin/exams/depublish/' . $data['quizid']) ?>" title="<?php echo lang('exams:depublish_link_title')?>" class="button confirm"><?php echo lang('exams:depublish')?></a>
										<?php
											if( $data['enabled'] == 1 )
											{
												echo '<a href="' . site_url('admin/exams/disable/' . $data['quizid']) . '" title="' . lang('exams:disable_link_title') . '" class="button confirm">' . lang('exams:disable') . '</a>';
											}
											else
											{
												echo '<a href="' . site_url('admin/exams/enable/' . $data['quizid']) . '" title="' . lang('exams:enable_link_title') . '" class="button confirm">' . lang('exams:enable') . '</a>';
											}
										?>
									</td>
								</tr>
							<?php endforeach;?>
						</tbody>
					</table>
				</div>
			</section>
		</div>

	<?php endif; ?>

	<?php if( ! empty($data_unpublished_exams) ): ?>

		<div class="one_full">
			<section class="title">
				<h4><?php echo lang('exams:unpublished_exams') ?></h4>
			</section>

			<section class="item">
				<div class="content">
					<table class="table-list" cellspacing="0">
						<thead>
							<tr>
								<th><?php echo lang('exams:name') ?></th>
								<th class="collapse"><span><?php echo lang('exams:description') ?></span></th>
								<th><?php echo lang('exams:version') ?></th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($data_unpublished_exams as $data): ?>
								<tr>
									<td class="collapse"><?php echo $data['name'] ?></td>
									<td><?php echo $data['description'] ?></td>
									<td class="align-center"><?php echo $data['version'] ?></td>
									<td class="actions">
										<a href="<?php echo site_url('admin/exams/edit/' . $data['quizid']) ?>" title="<?php echo lang('exams:edit_link_title')?>" class="button confirm"><?php echo lang('exams:edit')?></a>
										<a href="<?php echo site_url('admin/exams/publish/' . $data['quizid']) ?>" title="<?php echo lang('exams:publish_link_title')?>" class="button confirm"><?php echo lang('exams:publish')?></a>
										<a href="<?php echo site_url('admin/exams/delete/' . $data['quizid']) ?>" title="<?php echo lang('exams:delete_link_title')?>" class="button confirm"><?php echo lang('exams:delete')?></a>
									</td>
								</tr>
							<?php endforeach;?>
						</tbody>
					</table>
				</div>
			</section>
		</div>

	<?php endif; ?>

</div>
<pre>