<div class="row">
	<div class="col-xs-12">
		<form class="form" name="newsletter" action="<?php echo tep_href_link(basename($_SERVER['PHP_SELF'])); ?>" method="post">
			<div class="form-group">
				<label for="email" class="control-label text-xs">
					<?php echo Translate('Hier kan u zich inschrijving op onze nieuwsbrief.<br />Op deze manier blijft u altijd op de hoogte van onze laatste updates, beurzen, gelegenheden, ...'); ?>
				</label>
				<input type="hidden" name="lang" value="<?php echo $language; ?>" id="lang" />
				<input type="text" id="email" class="form-control" placeholder="email@domain.tld">
			</div>
			<div class="form-group">
				<button class="btn btn-success btn-xs"><i class="fa fa-plus"></i> <?php echo Translate('Inschrijven'); ?></button>
				<button class="btn btn-danger btn-xs"><i class="fa fa-minus"></i> <?php echo Translate('Uitschrijven'); ?></button>
			</div>
		</form>
	</div>
</div>