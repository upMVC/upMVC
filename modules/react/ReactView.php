<?php

namespace React;

use Common\Bmvc\BaseView;

class ReactView extends BaseView
{
    public function View($request)
    {
        $title = "React Module";
        $this->startHead($title);
?>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <?php
        $this->endHead();
        $this->startBody($title);
        ?>

        <!-- ... existing HTML ... -->

        <div id="like_button_container"></div>

        <!-- ... existing HTML ... -->
        <!-- ... other HTML ... -->

        <!-- Load React. -->
        <!-- Note: when deploying, replace "development.js" with "production.min.js". -->
        <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
        <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>

        <!-- Load our React component. -->
        <script src="<?php echo \BASE_URL;?>/comp"></script>
<?php
        $this->endBody();
        $this->startFooter();
        $this->endFooter();
    }
}
