<!-- openingsurenbox //-->
<tr><td>
<div id="openingsurenbox">
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<?
		$table_query = tep_db_query('SELECT `table` FROM `extensions` WHERE name = "Openingsuren"');
		if (tep_db_num_rows($table_query)>0) {
			$table = tep_db_fetch_array($table_query);
			$openingsuren_query = tep_db_query("SELECT * FROM ".$table['table']);
			while ($openingsuren = tep_db_fetch_array($openingsuren_query))
			{ 
				$bgcolor = ($no++ % 2) ? "even" : "odd";
				if ($openingsuren['dag'] == 'ma') { $dag = Translate('Ma'); }
				elseif ($openingsuren['dag'] == 'di') { $dag = Translate('Di'); }
				elseif ($openingsuren['dag'] == 'wo') { $dag = Translate('Wo'); }
				elseif ($openingsuren['dag'] == 'do') { $dag = Translate('Do'); }
				elseif ($openingsuren['dag'] == 'vr') { $dag = Translate('Vr'); }
				elseif ($openingsuren['dag'] == 'za') { $dag = Translate('Za'); }
				elseif ($openingsuren['dag'] == 'zo') { $dag = Translate('Zo'); }
				
				$todaysdate = date("D");
				if (($todaysdate == 'Mon') && ($openingsuren['dag'] == 'ma')) { $bgcolor = 'today'; }
				elseif (($todaysdate == 'Tue') && ($openingsuren['dag'] == 'di')) { $bgcolor = 'today'; }
				elseif (($todaysdate == 'Wed') && ($openingsuren['dag'] == 'wo')) { $bgcolor = 'today'; }
				elseif (($todaysdate == 'Thu') && ($openingsuren['dag'] == 'do')) { $bgcolor = 'today'; }
				elseif (($todaysdate == 'Fri') && ($openingsuren['dag'] == 'vr')) { $bgcolor = 'today'; }
				elseif (($todaysdate == 'Sat') && ($openingsuren['dag'] == 'za')) { $bgcolor = 'today'; }
				elseif (($todaysdate == 'Sun') && ($openingsuren['dag'] == 'zo')) { $bgcolor = 'today'; }
				
				if (($openingsuren['voormiddag_open'] == '') && ($openingsuren['middag_gesloten'] == '') && ($openingsuren['middag_open'] == '') && ($openingsuren['avond_gesloten'] == '')) {
					echo '<tr class="'.$bgcolor.'">
							<td width="10">&nbsp;&nbsp;<strong>'.$dag.'</strong></td>
							<td colspan="4" align="center"><span class="closed">'.Translate('Gesloten').'</span></td>
						</tr><tr><td colspan="5" height="3"></td></tr>';
				} elseif (($openingsuren['voormiddag_open'] == '') && ($openingsuren['middag_gesloten'] == ''))
				{
					echo '<tr class="'.$bgcolor.'">
							<td width="10">&nbsp;&nbsp;<strong>'.$dag.'</strong></td>
							<td width="40%" colspan="2" align="center"><span class="closed">'.Translate('Gesloten').'</span></td>
							<td width="20%">'.$openingsuren['middag_open'].'</td>
							<td width="20%">'.$openingsuren['avond_gesloten'].'</td>
						</tr><tr><td colspan="5" height="3"></td></tr>';
				} 
				elseif (($openingsuren['middag_open'] == '') && ($openingsuren['avond_gesloten'] == '')) 
				{
					echo '<tr class="'.$bgcolor.'">
						<td width="10">&nbsp;&nbsp;<strong>'.$dag.'</strong></td>
						<td width="20%">'.$openingsuren['voormiddag_open'].'</td>
						<td width="20%">'.$openingsuren['middag_gesloten'].'</td>
						<td width="40%" colspan="2" align="center"><span class="closed">'.Translate('Gesloten').'</span></td>
					</tr><tr><td colspan="5" height="3"></td></tr>';
				}
				else
				{
					echo '<tr class="'.$bgcolor.'">
						<td width="10">&nbsp;&nbsp;<strong>'.$dag.'</strong></td>
						<td width="20%">'.$openingsuren['voormiddag_open'].'</td>
						<td width="20%">'.$openingsuren['middag_gesloten'].'</td>
						<td width="20%">'.$openingsuren['middag_open'].'</td>
						<td width="20%">'.$openingsuren['avond_gesloten'].'</td>
					</tr><tr><td colspan="5" height="3"></td></tr>';
				} 
				$col ++;
				if ($col > 2) 
				{
				  $col = 0;
				  $row ++;
				}
				$count++;
			}
		}

	?>
	</table>
</div>
</td></tr>
<!-- openginsurenbox_eof //-->