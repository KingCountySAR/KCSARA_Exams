<?php echo form_open(uri_string(), 'class="crud"') ?>
<div class="exams-form">

	<div class="one_full">
		<section class="title">
			<h4>Exam Information</h4>
		</section>
		<section class="item">
			<label>
				<span>Name:</span>
				<?php echo form_input('name', '') ?>
			</label>
			<label>
				<span>Version:</span>
				<?php echo form_input('version', '1.0') ?>
			</label>
			<label>
				<span>Description:</span>
				<?php echo form_input('description', '') ?>
			</label>
		</section>
	</div>

	<div class="one_full">
		<section class="title">
			<h4>Question 1</h4>
		</section>
		<section class="item">
			<label>
				<span>Text:</span>
				<?php echo form_textarea('query1_text', '') ?>
			</label>
			<label>
				<span>Option 1:</span>
				<?php echo form_textarea('query1_option[]', '1.0') ?>
				<span class="exams-controls">
					<input type="checkbox" name="query1_answer[]" value="correct" title="This is a correct answer." />
					<button type="submit" name="query1_deleteoption[]" value="1" class="button actions" title="Delete this option.">-</button>
				</span>
			</label>
			<label>
				<span>Option 2:</span>
				<?php echo form_textarea('query1_option[]', '') ?>
				<span class="exams-controls">
					<input type="checkbox" name="query1_answer[]" value="correct" title="This is a correct answer." class="correct-answer" />
					<button type="submit" name="query1_deleteoption[]" value="2" class="button actions" title="Delete this option.">-</button>
				</span>
			</label>
			<label>
				<span>Option 3:</span>
				<?php echo form_textarea('query1_option[]', '') ?>
				<span class="exams-controls">
					<input type="checkbox" name="query1_answer[]" value="correct" title="This is a correct answer." class="correct-answer" />
					<button type="submit" name="query1_deleteoption[]" value="3" class="button actions" title="Delete this option.">-</button>
				</span>
			</label>
			<label>
				<span>Option 4:</span>
				<?php echo form_textarea('query1_option[]', '') ?>
				<span class="exams-controls">
					<input type="checkbox" name="query1_answer[]" value="correct" title="This is a correct answer." class="correct-answer" />
					<button type="submit" name="query1_addoption" value="true" class="button actions" title="Add an option below this one.">+</button>
				</span>
			</label>
		</section>
		<section>
			<label>
				<button type="submit" class="button" name="addquery" value="2" title="Add a query below this one.">+</button>
			</label>
		</section>
	</div>

	<div class="one_full">
		<section>
			<label>
				<button type="submit" class="btn blue">Save</button>
				<button type="submit" class="btn red" name="delete" value="delete">Delete</button>
				<button type="submit" class="btn gray" name="cancel" value="cancel">Cancel</button>
			</label>
		</section>
	</div>

</div>
<?php echo form_close() ?>
