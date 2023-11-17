<?php
/*
 * Created on Tue Oct 31 2023
 *
 * Copyright (c) 2023 BitsHost
 */

use Common\Bmvc\BaseView;

$newView = new BaseView();
$title   = "List";
$newView->startHead($title);
$newView->endHead();
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
print_r($_GET);
$newView->endBody();
$newView->startFooter();
$newView->endFooter();