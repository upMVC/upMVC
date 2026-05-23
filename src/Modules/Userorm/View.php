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

namespace App\Modules\Userorm;

use App\Common\Bmvc\BaseView;

class View
{

    public $title = "Create User";

    public function header()
    {
        $view        = new BaseView();
        $this->title = "Users ORM";
        $view->startHead($this->title);
        ?>
        <style>
            /* Userorm: Bootstrap 5 class mapping → BaseView design system */
            .container { max-width: 960px; margin: 0 auto; }
            .mt-4 { margin-top: 1.5rem; }
            .mb-4 { margin-bottom: 1.5rem; }
            .mb-3 { margin-bottom: 1rem; }
            .d-flex { display: flex; }
            .d-inline { display: inline; }
            .justify-content-between { justify-content: space-between; }
            .align-items-center { align-items: center; }
            h2 { font-size: 1.2rem; font-weight: 700; color: #0f172a; }

            /* Table */
            .table { width: 100%; border-collapse: collapse; font-size: .87rem; }
            .table th { padding: 10px 14px; text-align: left; background: #f8fafc; border-bottom: 2px solid #e2e8f0; font-size: .76rem; font-weight: 600; color: #374151; text-transform: uppercase; letter-spacing: .04em; }
            .table td { padding: 11px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #1e293b; }
            .table tr:last-child td { border-bottom: none; }
            .table-striped tbody tr:nth-child(odd) td { background: #f8fafc; }
            .table tr:hover td { background: #f0f9ff; }

            /* Buttons */
            .btn { display: inline-block; padding: 6px 14px; font-size: .82rem; font-weight: 600; border-radius: 6px; text-decoration: none; cursor: pointer; border: 1px solid #cbd5e1; background: #f1f5f9; color: #374151; transition: background .15s; }
            .btn:hover { background: #e2e8f0; color: #0f172a; }
            .btn-sm { padding: 4px 10px; font-size: .78rem; }
            .btn-primary { background: #0369a1; color: #e0f2fe; border-color: #0284c7; }
            .btn-primary:hover { background: #0284c7; color: #fff; }
            .btn-secondary { background: #1e293b; color: #94a3b8; border-color: #334155; }
            .btn-secondary:hover { background: #334155; color: #e2e8f0; }
            .btn-danger { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
            .btn-danger:hover { background: #fca5a5; color: #7f1d1d; }
            .btn-info { background: #dbeafe; color: #1e40af; border-color: #bfdbfe; }
            .btn-info:hover { background: #bfdbfe; color: #1e3a8a; }

            /* Alerts */
            .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: .88rem; border: 1px solid transparent; }
            .alert-success { background: #dcfce7; border-color: #86efac; color: #166534; }
            .alert-danger  { background: #fee2e2; border-color: #fca5a5; color: #991b1b; }

            /* Form */
            .form-label { display: block; font-size: .82rem; font-weight: 600; color: #374151; margin-bottom: 5px; }
            .form-control { display: block; width: 100%; padding: 8px 12px; font-size: .9rem; color: #1e293b; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; transition: border-color .15s, box-shadow .15s; }
            .form-control:focus { border-color: #38bdf8; box-shadow: 0 0 0 3px rgba(56,189,248,.15); }

            /* Pagination */
            .pagination { display: flex; gap: 4px; list-style: none; padding: 0; margin: 16px 0; flex-wrap: wrap; }
            .page-link { display: inline-block; padding: 6px 12px; font-size: .85rem; color: #374151; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; text-decoration: none; transition: background .15s; }
            .page-link:hover { background: #f1f5f9; }
            .page-item.active .page-link { background: #0369a1; color: #fff; border-color: #0284c7; }
        </style>
        <?php
        $view->endHead();
        $view->startBody($this->title);

    }

    public function footer()
    {
        $view        = new BaseView();
        $view->endBody();
        $view->startFooter();
        $view->endFooter();
    }
}











