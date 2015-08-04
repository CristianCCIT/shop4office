<?php
$manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name from " . TABLE_MANUFACTURERS . " order by manufacturers_name");
if ($number_of_rows = tep_db_num_rows($manufacturers_query)) {
	// Display a drop-down
	$manufacturers_array = array();
	if (MAX_MANUFACTURERS_LIST < 2) {
		$manufacturers_array[] = array('id' => '', 'text' => Translate('Selecteer een merk'));
	}
	while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
		$manufacturers_name = ((strlen($manufacturers['manufacturers_name']) > MAX_DISPLAY_MANUFACTURER_NAME_LEN) ? substr($manufacturers['manufacturers_name'],
				0, MAX_DISPLAY_MANUFACTURER_NAME_LEN) . '..' : $manufacturers['manufacturers_name']);
		$manufacturers_array[] = array(
			'id' => tep_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . $manufacturers['manufacturers_id']),
			'text' => $manufacturers_name
		);
	}
	?>
	<div class="col-xs-12 mb-20">
		<form>
			<select id="manufacturers_id" class="form-control">
				<?php $_selected = (isset($_GET['manufacturers_id']) ? $_GET['manufacturers_id'] : 0); ?>
				<?php foreach ($manufacturers_array as $entry) { ?>
					<option <?php echo (($_selected && $_selected == $entry['manufacturers_id']) ? 'selected="selected"' : ''); ?>
						value="<?php echo $entry['id']; ?>"><?php echo $entry['text']; ?></option>
				<?php } ?>
			</select>
		</form>
		<script type="text/javascript">
			$(function () {
				$('#manufacturers_id').change(function () {
//					console.log('Changed');
					if ($(this).val().length) {
						window.location = $(this).val();
					}
				});
			});
		</script>
	</div>
<?php }