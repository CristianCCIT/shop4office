<div class="orders-navigation">
<a href="<?php echo tep_href_link(FILENAME_INFOPAGE, 'page=8'); ?>" class="button-a"><?php echo Translate('Lijst bestellingen') ?></a>
<a href="<?php echo tep_href_link(FILENAME_INFOPAGE, 'page=8'); ?>?details=J" class="button-a"><?php echo Translate('Lijst bestelde artikelen') ?></a>
</div>
<?php
if (isset($_GET['order_id'])) {
	echo ViewDocRequest($_GET['order_id'], 'B');
} else {
	if ($_GET['details']=='J') {
		echo ListDocRequest('B', 'J');
	} else {
		echo ListDocRequest('B', 'N');
	}
}
?>