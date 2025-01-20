<?php

namespace Admin;

use Common\Bmvc\BaseView;


class View
{
    public $title = 'Demonstrative - React, Vue Integration without Build Step. PHP, React, Vue - NO Bundle.';


    public function render($data = [])
    {
        switch ($data['view'] ?? 'index') {
            case 'index':
                // print_r($data);
                $this->renderIndex($data);
                break;
                // ... other cases 

            default:
                echo 'Invalid view';
                break;
        }
    }

    private function renderIndex($data)
    {
        $view        = new BaseView();
        $this->title;
        $view->startHead($this->title);
?>
        <script type="importmap">
            {
        "imports": {
          "preact": "https://esm.sh/preact@10.23.1",
          "htm/preact": "https://esm.sh/htm@3.1.1/preact?external=preact",
          "preact/": "https://esm.sh/preact@10.23.1/",
          "react-dom": "https://esm.sh/preact@10.23.1/compat",
           "@mui/material": "https://esm.sh/@mui/material@5.16.7?external=react,react-dom",
          "@preact/signals": "https://esm.sh/@preact/signals@1.3.0?external=preact"

        }
      }
    </script>
        <script src="https://unpkg.com/vue@3.2.47"></script>

        <?php
        $view->endHead();
        $view->startBody($this->title);
        ?>

        <body>
            <div class="container">
                <h3>Demonstrative PHP and Preact - NO Build step.</h3>
                <div id="app"></div>
                <div id="counter"></div>
            </div>
            <hr>
            <div class="container">
                <h3>Demonstrative PHP and VUE - NO Build step.</h3>
                <div id="appvue"></div>

            </div>
            <hr>
            <div class="container">
                <h3>Demonstrative PHP and REACT - NO Build step.</h3>
                <div id="appreact"></div>

            </div>

        <?php
        $this->preactHello();
        $this->preactCounter();
        $this->vueHello();
        $this->reactHello();




        $view->startFooter();
        $view->endFooter();
    }







    private function preactHello()
    {
        ?>
            <script type="module">
                import {
                    render
                } from 'preact';
                import {
                    html
                } from 'htm/preact';

                function Button({
                    action,
                    children
                }) {
                    return html`<button onClick=${action}>${children}</button>`;
                }

                function hey() {
                    return html`<h2>Hello from Preact! </h2>`;

                }
                export function App() {
                    return html`<${hey} />`;

                }

                render(html`<${App} />`, document.getElementById('app'));
            </script>
        <?php
    }


    private function preactCounter()
    {
        ?>
            <script type="module">
                import {
                    render
                } from 'preact';
                import {
                    useState
                } from 'preact/hooks';
                import {
                    html
                } from 'htm/preact';

                function Button({
                    action,
                    children
                }) {
                    return html`<button onClick=${action}>${children}</button>`;
                }

                function Counter() {
                    const [count, setCount] = useState(0);

                    return html`
		<div class="counter-container">
			<${Button} action=${() => setCount(count + 1)}>Increment<//>
			<input readonly value=${count} />
			<${Button} action=${() => setCount(count - 1)}>Decrement<//>
		</div>
	`;
                }

                export function App() {
                    return html`<${Counter} />`;

                }
                render(html`<${App} />`, document.getElementById('counter'));
            </script>
        <?php
    }


    private function vueHello()
    {
        ?>
            <script>
                const {
                    createApp
                } = Vue;

                const app = createApp({
                    data() {
                        return {
                            message: 'Hello from Vue.js!'
                        }
                    },
                    template: `
                        <h2>{{ message }}</h2>
                    `
                });

                app.mount('#appvue');
            </script>
        <?php
    }

    private function reactHello()
    {
        ?>
            <script type="module">
                import React from "https://unpkg.com/es-react@latest/dev/react.js";
                import ReactDOM from "https://unpkg.com/es-react@latest/dev/react-dom.js";
                import PropTypes from "https://unpkg.com/es-react@latest/dev/prop-types.js";
                import htm from "https://unpkg.com/htm@latest?module";
                const html = htm.bind(React.createElement);

                const App = (props) => {
                    return html`<div><h2>Hello from React! foo: ${props.foo}</h2></div>`;
                };

                ReactDOM.render(
                    html`<${App} foo=${"bar"} />`,
                    document.getElementById("appreact")
                );
            </script>
    <?php
    }
}





    // ... other render methods 
