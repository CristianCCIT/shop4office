<nav class="navbar navbar-default">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="<?php echo www; ?>">
                <img src="$templatedir$/images/logo.png" height="30">
            </a>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <?php
                    $parent_list = abo_get_navigation(0,0,true);
                    $categories = getStyledCategoryList(0,0,true);

                    xD3bug($parent_list);
                    xD3bug($categories,1, '127.0.0.1');

                    if($categories){
                        $_cat = array();
                        foreach ($categories as $category){
                            $category['kids'] = null;
                            $_cat[] =   $category;
                        }

                        $parent_list[] = array(
                            'link' => '#',
                            'title' => Translate('Products') . ' - ' . STORE_NAME,
                            'name'  => Translate('Products'),
                            'kids' => $_cat
                        );
                    }

                    function createDropDown($array, $level=0)
                    {
                        $_return = '';
                        foreach($array as $elem){
                            if(!empty($elem['kids'])){
                                // Dropdown
                                $class = '';
                                $caret = '<span class="caret"></span>';
                                if($level > 0){
                                    $class = 'dropdown-submenu';
                                    $caret = '';
                                }

                                $_return.='<li class="'.$class.'">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">'. $elem['name'] . $caret . '</a>
                                <ul class="dropdown-menu multi-level" role="menu">';
                                $_return.= createDropDown($elem['kids'], $level+1);
                                $_return.='</ul></li>';
                            } else {
                                // Nav Element
                                if(false){
                                    $_return.= '<li class="active"><a href="'. $elem['link'] .'" title="'. $elem['title'] .'">'. $elem['name'] .' <span class="sr-only">(current)</span></a></li>';
                                } else {
                                    $_return.= '<li><a href="'. $elem['link'] .'" title="'. $elem['title'] .'">'. $elem['name'] .'</a></li>';
                                }
                            }
                        }

                        return $_return;
                    }

                    echo createDropDown($parent_list);
                ?>
            </ul>
            <?php loadBox('search'); ?>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>