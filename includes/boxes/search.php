<?php
echo tep_draw_form('quick_find', tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', 'NONSSL', false), 'get');
echo tep_draw_input_field('keywords', Translate('Typ hier uw zoekwoord...'), 'size="10" class="inputbox" maxlength="60" onblur="if(this.value==\'\') this.value=\''. Translate('Typ hier uw zoekwoord...').'\';" onfocus="if(this.value==\''. Translate('Typ hier uw zoekwoord...').'\') this.value=\'\';"') .'<input type="submit" id="searchbox_button" value="'.Translate('Snelzoeken').'" />'.tep_hide_session_id();
echo '</form>';
?>