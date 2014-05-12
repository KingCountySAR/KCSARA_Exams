<div class="exams_container">
<?php echo validation_errors(); ?>
<?php echo form_open($form_uri, 'class="crud"') ?>

	<?php
		echo form_hidden('timestamp', $timestamp);
		echo form_hidden('nonce', $nonce);
		echo form_hidden('signature', $signature);
	?>

	<header class="title">
		<h2><?php echo htmlspecialchars( $exam_data[0]['name'] ) ?></h2>
		<p><?php echo htmlspecialchars( $exam_data[0]['description'] ) ?></p>
		<p><?php echo htmlspecialchars( lang('exams:version') . ' ' . $exam_data[0]['version'] ) ?></p>
	</header>

	<fieldset class="visitor">
		<legend>Your Information:</legend>
		<ul>
			<li>
				<?php echo '<label>' . htmlspecialchars( lang('exams:full_name')) . ':</label>' . form_input('fullname', set_value('fullname', @$prior_attempt['fullname'])) ?>
			</li>
			<li>
				<?php echo '<label>' . htmlspecialchars( lang('exams:email')) . ':</label>' . form_input('email', set_value('email', @$prior_attempt['email'])) ?>
			</li>
			<li id="agency">
				<?php
					$selected = ($this->input->post('agency')) ? $this->input->post('agency') : $default_agency;
				
					echo '<label>' . htmlspecialchars( lang('exams:agency')) . ':</label><select name="agency">';
					foreach( array_keys( $agency_list ) as $data )
					{
						echo '<option';
						if( $data === 'empty' )
						{
							echo ' value=""';
						}
						elseif( strpos($data, 'disabled', 0) === 0 )
						{
							echo ' value="" disabled="disabled"';
						}
						else
						{
							echo ' value="' . htmlspecialchars( $data ) . '"';
						}
						if( $data === $selected or $data == @$prior_attempt['agency'] )
						{
							echo ' selected="selected"';
						}
						echo '>' . htmlspecialchars( $agency_list[$data] ) . '</option>';
					}
					echo '</select>';
				?>
				<a class="help" title="<?php echo htmlspecialchars( lang('exams:agency_help_text')) ?>"><?php echo htmlspecialchars( lang('exams:help')) ?></a>
				<script type="text/javascript" language="JavaScript" charset="utf-8">
				<!--
	
					$(document).ready(function() {
	
						<?php
							$last_key = '';
							foreach(array_keys( $agency_fields ) as $key)
							{
								$o = '$( "#agency" ).data( "' . $key . '", [ ';
								foreach( $agency_fields[$key] as $fields)
								{
									if ( $fields['name'] )
									{
										$o .=  '{ name: "' . htmlspecialchars( $fields['name']) . '", label: "' . htmlspecialchars( $fields['label'] ) . '", value: "' . htmlspecialchars( set_value($fields['name'], @$prior_attempt[$fields['name']]) ) . '" }, ';
									}
								}
								$o .=  '] );
						';
								echo $o;
							}
						?>
	
						function display_modal()
						{
							$(".help").after("<div id=\"help\">" +
								"<div>" +
									"<a class=\"close\">X</a>" +
									"<h2>Help</h2>" +
									"<p><?php echo htmlspecialchars( lang('exams:agency_help_text')) ?></p>" +
								"</div>" +
							"</div>");
							$( "#help" ).bind( "click", function() {
								$( "#help" ).remove();
							});
						}
						
						$( ".help" ).bind( "click", function() {
							display_modal();
						});
	
						function create_agency_fields()
						{
							$( '.agency-field' ).remove();
							var field_array = $( "#agency" ).data( $('select[name=agency] option:selected').val() );
	
							for( var i=field_array.length-1; i>=0; i-- )
							{
								var field_label = field_array[i].label;
								var field_name  = field_array[i].name;
								var field_value  = field_array[i].value;
							
								var new_input = $( '<li class="agency-field"><label>' + field_label + ':</label><input type="text" name="' + field_name + '" value="' + field_value + '" /></li>' );
								$( "#agency" ).after( $( new_input ) );
							}
						};
	
						$( "select[name=agency]" ).on( 'change', function() {
							create_agency_fields()
						});
						
						create_agency_fields();
	
					});
	
				//-->
				</script>
			</li>
		</ul>
		<?php
			/*
		<label class="remember">
			<?php echo form_checkbox( 'remember', 'Remember Me', set_checkbox( 'remember' )); ?>
			<span><?php echo htmlspecialchars( lang('exams:remember_me')) ?></span>
		</label>
			*/
		?>
	</fieldset>

	<?php

		$last_queryid = '';
		$i = 0;
		foreach($question_data as $query_data)
		{

			if( $query_data['queryid'] != $last_queryid )
			{
				if( $i != 0 )
				{
					echo '</ul>';
					echo '</fieldset>';
				}
				echo '<fieldset class="question">';
				echo '<p class="query">' . ( $query_data['query_text'] ) . '</p>';
				echo '<ul>';
			}

			$data = array(
				'name'	=> ($query_data['queryid'] . '[]'),
				'id'	=> $query_data['optionid'],
				'value'	=> $query_data['optionid'],
				'checked'	=> set_checkbox($query_data['queryid'] . '[]', $query_data['optionid'], ( @in_array( $query_data['optionid'], @$prior_attempt[$query_data['queryid']] ) ? TRUE : FALSE ))
			);

			echo '<li class="option">';
			echo form_checkbox($data);
			echo form_label($query_data['option_text'], $query_data['optionid']);
			echo '</li>';

			$last_queryid = $query_data['queryid'];
			$i++;
		}
		echo '</ul>';
		echo '</fieldset>';

	?>

	<fieldset class="notes">
		<ul>
		<li>
			<?php echo '<label>' . htmlspecialchars( lang('exams:notes')) . ':</label>' . form_textarea('notes', set_value('notes', @$prior_attempt['notes'])) ?>
		</li>
		</ul>
	</fieldset>

	<?php echo form_submit('submit', lang('exams:finish'), 'class="controls"'); ?>

<?php echo form_close() ?>
</div>