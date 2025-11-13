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

namespace App\Modules\React\Component;

class Component
{
    public function componentOne()
    {

        require_once(THIS_DIR."/modules/react/etc/component.js");
       /*echo "'use strict';

            const e = React.createElement;

            class LikeButton extends React.Component {
                constructor(props) {
                    super(props);
                    this.state = {
                        liked: false
                    };
                }

                render() {
                    if (this.state.liked) {
                        return 'You liked this.';
                    }

                    return e(
                        'button', {
                            onClick: () => this.setState({
                                liked: true
                            })
                        },
                        'Likke'
                    );
                }
            }

            const domContainer = document.querySelector('#like_button_container');
            const root = ReactDOM.createRoot(domContainer);
            root.render(e(LikeButton));
       ";
       */
    }
}











