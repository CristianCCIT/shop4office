<!-- AddThis Button BEGIN -->
<?php
$data_query = tep_db_query("SELECT * FROM social_media");
$data = tep_db_fetch_array($data_query);
?>
<!-- ORIGINAL <script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js?pub=xa-4a239d486e91ea36"></script>-->
<script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
<?php
        $opties = '';
        if ($data['services_exclude']!='') {
                $opties .= "services_exclude:'".$data['services_exclude']."',";
        }
        if ($data['services_compact']!='') {
                $opties .= "services_compact:'".$data['services_compact']."',";
        }
        if ($data['services_expanded']!='') {
                $opties .= "services_expanded:'".$data['services_expanded']."',";
        }
        if ($data['services_custom']!='') {
                $opties .= "services_custom:'".$data['services_custom']."',";
        }
        if ($opties!='')
        {
                $opties = substr($opties,0,-1);
        ?>
        <script type="text/javascript">
        var addthis_config =
        {
           <?php echo $opties; ?>
        }
        </script>
    <?php
        } ?>
    <?php
    if ($data['style']=='addthis_toolbox') {
    ?>
        <div class="addthis_toolbox">
            <?php
                $services = explode (',',$data['services_compact']);
                foreach($services as $service) {
                    ?>
                    <!--<a class="addthis_button_<?php //echo $service; ?>"></a>-->
                    <a class='st_<?php echo trim($service); ?>'></a>
                    <?php
                }///for ends
            ?>
        </div>
    <?php
    } else {
    ?>
        <a class="<?php echo $data['style']; ?>"><?php Translate('Delen'); ?></a>
    <?php
    }
    ?>
<!-- AddThis Button END -->
