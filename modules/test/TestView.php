<?php
/*
 * Created on Tue Oct 31 2023
 *
 * Copyright (c) 2023 BitsHost
 */
namespace Test;

use Common\Bmvc\BaseView;


/**
 * Testview
 */
class Testview extends BaseView
{
    /**
     * View
     *
     * @param  mixed $request
     * @param  mixed $users
     * @return void
     */
    public function View($request, $users)
    {

        $title = "List";
        $this->startHead($title);
        ?>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <?php
        $this->endHead();
        $this->startBody($title);
        ?>
        <ul id="ul">
            <strong>Click or Double Click on the first line.</strong>
            <?php foreach ($users as $user): ?>
                <li id="cell">
                    <?= $user->name ?> (
                    <?= $user->email ?>)
                </li>
            <?php endforeach; ?>
        </ul>
        <script>
            $(document).ready(function () {
                $("#cell").click(function () {
                    $(this).hide();
                });
                $("#ul").dblclick(function () {
                    $("li").show();
                });

            });

        </script>
        <?php
        echo $request . "<br>";
        print_r($_GET);
        $this->endBody();
        $this->startFooter();
        $this->endFooter();

    }
}

