<div class="facturen-navigation">
<a href="<?php echo tep_href_link(FILENAME_INFOPAGE, 'page=25'); ?>" class="button-a"><?php echo Translate('Lijst facturen') ?></a>
<a href="<?php echo tep_href_link(FILENAME_INFOPAGE, 'page=25'); ?>?details=J" class="button-a"><?php echo Translate('Lijst gefactureerde artikelen') ?></a>
</div>

<?php
if (isset($_GET['order_id'])) {
	echo ViewDocRequest($_GET['order_id'], 'F');
} else {
	if ($_GET['details']=='J') {
		echo ListDocRequest('F', 'J');
	} else {
		echo ListDocRequest('F', 'N');
	}
}
?>