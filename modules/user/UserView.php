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

namespace User;

use Common\Bmvc\BaseView;

class UserView
{

    public $title = "Create User";

    public function renderCreateForm($moduleRoute)
    {
        $view        = new BaseView();
        $this->title = "Create Users";
        $view->startHead($this->title);
        echo "<style>";
        require THIS_DIR . "/modules/user/etc/styles.css";
        echo "</style>";
        echo "<script>";
        include THIS_DIR . "/modules/user/etc/script.js";
        echo "</script>";
        $view->endHead();
        $view->startBody($this->title);
        echo '<div class="container">';
        echo '<form method="post" action="' . $moduleRoute . '">';
        echo '<input type="hidden" name="action" value="create">';
        echo '<label for="name">Name:</label>';
        echo '<input type="text" name="name" required>';
        echo '<br>';
        echo '<label for="email">Email:</label>';
        echo '<input type="email" name="email" required>';
        echo '<br>';
        echo '<input type="submit" value="Create User">';
        echo '</form>';
        echo '</div>';
        $view->endBody();
        $view->startFooter();
        $view->endFooter();
    }




    public function renderReadTable($users, $currentPage, $totalPages, $moduleRoute)
    {
        $view        = new BaseView();
        $this->title = "Read Users";
        $view->startHead($this->title);
        echo "<style>";
        require THIS_DIR . "/modules/user/etc/styles.css";
        echo "</style>";
        echo "<script>";
        include THIS_DIR . "/modules/user/etc/script.js";
        echo "</script>";
        $view->endHead();
        $view->startBody($this->title);
        echo '<div class="container">';
        //echo '<h2>Read Users</h2>';
        echo '<table>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Name</th>';
        echo '<th>Email</th>';
        echo '<th>Action</th>';
        echo '<th>Action</th>';
        echo '<th>Action</th>';
        echo '</tr>';

        foreach ($users as $user) {
            echo '<tr>';
            echo '<td>' . $user['id'] . '</td>';
            echo '<td>' . $user['name'] . '</td>';
            echo '<td>' . $user['email'] . '</td>';
            echo '<td>';
            echo '<a href=" ' . $moduleRoute . '?action=update&id=' . $user['id'] . '">Update</a>';
            echo '</td>';
            echo '<td>';
            echo '<a href="' . $moduleRoute . '?action=delete&id=' . $user['id'] . '" class="delete-link" onclick="return confirmDeletion()">Delete</a>';
            echo '</td>';
            echo '<td>';
            echo '<a href="' . $moduleRoute . '?action=form">Create</a>';
            echo '</td>';
            echo '</tr>';
        }

        echo '</table>';

        // Pagination links
        echo '<div class="pagination">';
        for ($i = 1; $i <= $totalPages; $i++) {
            echo '<a href="' . $moduleRoute . '?action=read&page=' . $i . '" ';
            if ($i == $currentPage) {
                echo 'class="active"';
            }
            echo '>' . $i . '</a>';
        }
        echo '</div>';
        echo '</div>';
        $view->startFooter();
        $view->endFooter();
    }


    public function renderUpdateForm($user, $moduleRoute)
    {
        $view        = new BaseView();
        $this->title = "Update Users";
        $view->startHead($this->title);
        echo "<style>";
        require THIS_DIR . "/modules/user/etc/styles.css";
        echo "</style>";
        echo "<script>";
        include THIS_DIR . "/modules/user/etc/script.js";
        echo "</script>";
        $view->endHead();
        $view->startBody($this->title);
        //print_r($user);
        echo '<div class="container">';
        echo '<form method="post" action=" ' . $moduleRoute . ' ">';
        echo '<input type="hidden" name="action" value="update">';
        echo '<input type="hidden" name="id" value="' . $user['id'] . '">';
        echo '<label for="name">Name:</label>';
        echo '<input type="text" name="name" value="' . $user['name'] . '" required>';
        echo '<br>';
        echo '<label for="email">Email:</label>';
        echo '<input type="email" name="email" value="' . $user['email'] . '" required>';
        echo '<br>';
        echo '<input type="submit" value="Update User">';
        echo '</form>';
        echo '</div>';
        $view->startFooter();
        $view->endFooter();
    }

    public function apiInfo($moduleRoute)
    {
        $view        = new BaseView();
        $this->title = "Api INFO";
        $view->startHead($this->title);
        $view->endHead();
        $view->startBody($this->title);
?>
        <div>Minimal example of a test API without authentication.</div>
        <p>Send POST request to <?php echo  $moduleRoute ?>/apiUsers</p>
        <div>You can use POSTMAN, HTTPie, REQBIN for testing.</div>
        <br>
        <p>CREATE User example: <a href="https://reqbin.com/yummdfum" target="_blank">Example</a></p>
        Example:
        <br>
        task=create <br>
        name=HANAH <br>
        email=hanah@email.com </br></br>
        <pre><code>$url = "https://upmvc.com/demo/apiUsers";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
   "Content-Type: application/x-www-form-urlencoded",
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$data = "task=create&name=HANAH&email=hanah%40email.com";

curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

//for debug only!
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($curl);
curl_close($curl);
var_dump($resp);</code></pre></br>
        <p>READ all Users example: <a href="https://reqbin.com/eif1q78f" target="_blank">Example</a></p>
        Example: <br>
        <span>task=readall</span></br><br>
        <pre><code>$url = "https://upmvc.com/demo/apiUsers";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
   "Content-Type: application/x-www-form-urlencoded",
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$data = "task=readall";

curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

//for debug only!
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($curl);
curl_close($curl);
var_dump($resp);
</code></pre></br>
        <p>READ user by id example: <a href="https://reqbin.com/geowugvz" target="_blank">Example</a></p>
        Example:<br>
        task=readById<br>
        id=50<br><br>
        <pre><code>$url = "https://upmvc.com/demo/apiUsers";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
   "Content-Type: application/x-www-form-urlencoded",
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$data = "task=readById&id=50";

curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

//for debug only!
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($curl);
curl_close($curl);
var_dump($resp);</code></pre><br><br>
        <p>UPDATE User example: <a href="https://reqbin.com/" target="_blank">Example</a></p>
        <div>Example:<br>
            id=62<br>
            task=update<br>
            name=dydy<br>
            email=dydy@email.com<br></div><br>

        <br>
        <pre><code>

$url = "https://upmvc.com/demo/apiUsers";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
"Content-Type: application/x-www-form-urlencoded",
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$data = "id=47&atask=update&name=HANAH&email=hanah@email.com";

curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

//for debug only!
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($curl);
curl_close($curl);
var_dump($resp);



</code></pre></br>
        <p>DELETE user example: <a href="https://reqbin.com/hocr5uae" target="_blank">Example</a></p>
        <br>
        Example:<br>
        task=delete<br>
        id=49<br><br>
        <pre><code>$url = "https://upmvc.com/demo/apiUsers";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
   "Content-Type: application/x-www-form-urlencoded",
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$data = "task=delete&id=49";

curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

//for debug only!
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($curl);
curl_close($curl);
var_dump($resp);
</code></pre>

<?php
        $view->startFooter();
        $view->endFooter();
    }
}
