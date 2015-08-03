<div class="box-title"><?php echo Translate('Mijn account'); ?></div>
<div class="box-content">
	<?php
    if (!tep_session_is_registered('customer_id')){
        echo '<a href="'.tep_href_link(FILENAME_LOGIN).'" title="'.Translate('Login').' - '.STORE_NAME.'">'.Translate('Login').'</a> (Nieuwe klant? <a href="'.tep_href_link(FILENAME_CREATE_ACCOUNT).'" title="'.Translate('Registreer').' - '.STORE_NAME.'">'.Translate('Registreer').'</a>)';
    } else {
        echo '<a href="'.tep_href_link(FILENAME_ACCOUNT).'" title="'.Translate('Mijn account').' - '.STORE_NAME.'">'.Translate('Mijn account').'</a> <span class="split"><span>|</span></span> <a href="'.tep_href_link(FILENAME_LOGOFF).'" title="'.Translate('Log uit').' - '.STORE_NAME.'">'.Translate('Log uit').'</a>';
    }
    ?>
</div>