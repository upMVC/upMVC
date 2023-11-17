<?php
use Html\CommonView;

$newView = new commonView();
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