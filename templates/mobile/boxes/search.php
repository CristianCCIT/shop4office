<form class="navbar-form navbar-right form-inline" role="search" name="quick_find" action="<?php echo tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', 'NONSSL', false); ?>" method="get">
	<div class="form-group">
		<div class="input-group">
			<input type="text" name="keywords" class="form-control" placeholder="<?php echo Translate('Typ hier uw zoekwoord...'); ?>">
			<div class="input-group-addon">
				<button type="submit" class="btn btn-xs btn-info"><i class="fa fa-search"></i></button>
			</div>
		</div>
		<?php tep_hide_session_id(); ?>
	</div>
</form>