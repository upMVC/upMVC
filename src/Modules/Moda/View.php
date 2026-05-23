<?php
/*
 *   Created on Tue Oct 31 2023
 
 *   Copyright (c) 2023 BitsHost
 *   All rights reserved.

 *   Permission is hereby granted, free of charge, to any person obtaining a copy
 *   of this software and associated documentation files (the "Software"), to deal
 *   in the Software without restriction, including without limitation the rights
 *   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *   copies of the Software, and to permit persons to whom the Software is
 *   furnished to do so, subject to the following conditions:

 *   The above copyright notice and this permission notice shall be included in all
 *   copies or substantial portions of the Software.

 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *   FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *   SOFTWARE.
 *   Here you may host your app for free:
 *   https://bitshost.biz/
 */

use App\Common\Bmvc\BaseView;

$newView = new BaseView();
$title   = "Moda — BaseView Demo";
$newView->startHead($title);
$newView->endHead();
$newView->startBody($title, '
    <a href="?task=add" class="bv-btn bv-btn-primary">+ Add Item</a>
    <a href="?task=export" class="bv-btn">Export</a>
');

// ---- Section: user list ----
$newView->startSection('Users');
?>
<table class="bv-tbl">
    <thead>
        <tr><th>Name</th><th>Email</th></tr>
    </thead>
    <tbody>
    <?php foreach ($users as $user) : ?>
        <tr>
            <td><?= htmlspecialchars($user->name) ?></td>
            <td><?= htmlspecialchars($user->email) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php
$newView->endSection();

// ---- Section: add user form ----
$newView->startSection('Add User');
?>
<form method="post" action="" style="display:grid;gap:14px;max-width:420px;">
    <div>
        <label style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:4px;">Name</label>
        <input type="text" name="name" placeholder="John Doe"
               style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:.9rem;">
    </div>
    <div>
        <label style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:4px;">Email</label>
        <input type="email" name="email" placeholder="john@example.com"
               style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:.9rem;">
    </div>
    <div>
        <button type="submit" class="bv-btn bv-btn-primary">Save</button>
        <a href="?" class="bv-btn" style="margin-left:6px;">Cancel</a>
    </div>
</form>
<?php
$newView->endSection();

// ---- Cards: stats row (3 columns desktop, 1 column mobile via bv-cards grid) ----
$newView->startSection('Overview');
?>
<div class="bv-cards">
    <div class="bv-card">
        <div class="bv-card-label">Total Users</div>
        <div class="bv-card-value">1,284</div>
        <div class="bv-card-sub">↑ 12% this month</div>
    </div>
    <div class="bv-card">
        <div class="bv-card-label">Active Plans</div>
        <div class="bv-card-value">47</div>
        <div class="bv-card-sub">3 trials expiring soon</div>
    </div>
    <div class="bv-card">
        <div class="bv-card-label">Revenue</div>
        <div class="bv-card-value">$9,320</div>
        <div class="bv-card-sub">↓ 2% vs last month</div>
    </div>
</div>
<?php
$newView->endSection();

if (!empty($_GET)) {
    $newView->startSection('GET params');
    echo '<pre style="font-size:.85rem;">' . htmlspecialchars(print_r($_GET, true)) . '</pre>';
    $newView->endSection();
}

$newView->endBody();
$newView->startFooter();
$newView->endFooter();











