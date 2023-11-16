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
        $newView->startHeader($title);
        $newView->endHeader();
        $newView->startBody($title);
        ?>
        <ul>
            <?php foreach ($users as $user): ?>
                <li>
                    <?= $user->name ?> (
                    <?= $user->email ?>)
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
        echo $request . "<br>";
        print_r($_GET);
        $newView->endBody();
        $newView->startFooter();
        $newView->endFooter();

    }
}

