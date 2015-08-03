<!-- tagcloud //-->
<?php
	$products_query_raw = "select infopages_title, infopages_preview, infopages_description from infopages_text LIMIT 500";
	$products_query1 = tep_db_query($products_query_raw);
	while ($tcproducts = tep_db_fetch_array($products_query1)) {
		$tc_content = $tcproducts['infopages_title'] . ' ' . $tc_content;
		$tc_content = $tcproducts['infopages_preview'] . ' ' . $tc_content;
		$tc_content = $tcproducts['infopages_description'] . ' ' . $tc_content;
	}
	$tc_content = strip_tags($tc_content);
	$tc_a_tags = array();	// normal version
	preg_match_all('/(?:\\W+?|^)([\\wäöüÄÖÜß]+)/i', $tc_content, $regs, PREG_PATTERN_ORDER);
	foreach ($regs[1] as $tc_word) {
		$tc_word = trim($tc_word);
		$tc_word = strtolower(trim($tc_word));
		if (!empty($tc_word)) {
			if (!stristr(Translate('Tagcloud forbidden'), $tc_word))
			{
				if (strlen($tc_word)>=4)
				{
					if (is_numeric($tc_word)=='TRUE')
					{
					}
					else
					{
						if (array_key_exists($tc_word, $tc_a_tags)) {
							$tc_a_tags[$tc_word]++;
						} else {
							$tc_a_tags[$tc_word] = 1;
						}
					}
				}
			}
		}
	}
	$tc_tch = new tagcloud(25);
	$tc_tch->set_tagcloud_data($tc_a_tags);
	$tc_tagcloud = $tc_tch->get_tagcloud();
	echo $tc_tagcloud;
?>