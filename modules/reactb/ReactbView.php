<?php

namespace Reactb;

use Common\Bmvc\BaseView;

class ReactbView extends BaseView
{
    public function View($request)
    {
        $title = "Reactb Module";
        $this->startHead($title);

?>
<!--  we just get parts from index.html from our react app latest Build from etc/Build -->
        <meta charset="utf-8" />
        <meta name="theme-color" content="#000000" />
        <meta name="description" content="Web site created using create-react-app" />
        <link rel="apple-touch-icon" href="<?php echo \BASE_URL; ?>/logo" />
        <link rel="manifest" href="<?php echo \BASE_URL; ?>/manifest" />
        <title>React App</title>
        <script defer="defer" src="<?php echo \BASE_URL; ?>/mainjs"></script>
        <link href="<?php echo \BASE_URL; ?>/maincss" rel="stylesheet">

        <style>
            * {
                box-sizing: border-box;
            }

            /* Create three unequal columns that floats next to each other */
            .column {
                float: left;
                padding: 10px;
                height:90%;
                /* Should be removed. Only for demonstration */
            }

            .left,
            .right {
                width: 25%;
            }

            .middle {
                width: 50%;
            }

            /* Clear floats after the columns */
            .row:after {
                content: "";
                display: table;
                clear: both;
            }
        </style>
        <?php
        $this->endHead();
        $this->startBody($title);

        ?>
        <div class="row">
            <div class="column left" style="background-color:#FFA500;">
                <h2>Column 1</h2>
               
            </div>
            <div id="root" class="column middle" style="background-color:#FF00FF;">
                <h2>Column 2</h2>
            
            </div>
            <div class="column right" style="background-color:#FFA500;">
                <h2>Column 3</h2>
                
            </div>
        </div>
        
<?php
        $this->endBody();
        $this->startFooter();
        $this->endFooter();
    }
}
