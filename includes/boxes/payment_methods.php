<?php
global $Modules;
$method_images = array();
ksort($Modules->modules['payment']);
foreach($Modules->modules['payment'] as $sort_order=>$module) {
	global $$module;
	if ($$module->is_active()) {
		if (method_exists($$module, 'show_images')) {
			$method_images = array_merge($method_images, $$module->show_images());
		}
	}
}
$method_images = array_unique($method_images);
echo '<ul class="thumbnails">';
foreach($method_images as $type=>$image) {
	echo '<li class="thumbnail"><img src="'.$image.'" title="'.$type.'" /></li>';
}
echo '</ul>';
?>