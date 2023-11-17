<?php
namespace Test;

use Html\CommonView;

/**
 * Testview
 */
class Testview
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
        $newView = new commonView();
        $title   = "List";
        $newView->startHead($title);
        ?>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <?php
        $newView->endHead();
        $newView->startBody($title);
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
        $newView->endBody();
        $newView->startFooter();
        $newView->endFooter();

    }
}

