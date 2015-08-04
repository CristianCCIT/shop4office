<?php
/**
 * Created by PhpStorm.
 * User: Cristian
 * Date: 8/4/2015
 * Time: 3:42 PM
 */

if(in_array($_SERVER['REQUEST_URI'], array('/', '/'.FILENAME_DEFAULT))){
	loadBox('banners');
	$homepage_query = tep_db_query('SELECT infopages_id FROM infopages WHERE type = "home"');
	$homepage = tep_db_fetch_array($homepage_query);
?>
	<div class="col-xs-12">
		<div class="panel panel-info">
			<div class="panel-heading"><?php echo tep_get_infopages_title($homepage['infopages_id']); ?></div>
			<div class="panel-body">
				<?php echo tep_get_infopages_description($homepage['infopages_id']); ?>
			</div>
		</div>
	</div>

	<?php loadBox('specials'); ?>
	<?php loadBox('home_products'); ?>
<?php
} else {

}