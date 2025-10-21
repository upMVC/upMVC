/*
 *   Copyright (c) 2025 BitsHost
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
 */


/**
 * TodoApp Component
 * 
 * Demonstrates:
 * - Loading component from external file
 * - State management with hooks
 * - Form handling
 * - HMR will reload when this file changes!
 * 
 * Try editing this file and watch the browser auto-reload!
 */

import { useState } from 'https://esm.sh/preact@10.23.1/hooks';
import { html } from 'https://esm.sh/htm@3.1.1/preact?external=preact';

function TodoApp({ initialTodos = [] }) {
    const defaultTodos = initialTodos.length > 0 ? initialTodos : [
        { id: 1, text: 'Learn Preact with HMR', completed: true },
        { id: 2, text: 'Build amazing apps', completed: false },
        { id: 3, text: 'No webpack needed!', completed: false }
    ];
    
    const [todos, setTodos] = useState(defaultTodos);
    const [inputValue, setInputValue] = useState('');

    const addTodo = (e) => {
        e.preventDefault();
        if (inputValue.trim()) {
            const newTodo = {
                id: Date.now(),
                text: inputValue,
                completed: false
            };
            setTodos([...todos, newTodo]);
            setInputValue('');
        }
    };

    const toggleTodo = (id) => {
        setTodos(todos.map(todo => 
            todo.id === id ? { ...todo, completed: !todo.completed } : todo
        ));
    };

    const deleteTodo = (id) => {
        setTodos(todos.filter(todo => todo.id !== id));
    };

    return html`
        <div>
            <form class="todo-input" onSubmit=${addTodo}>
                <input 
                    type="text"
                    value=${inputValue}
                    onInput=${(e) => setInputValue(e.target.value)}
                    placeholder="Add a new todo..."
                />
                <button type="submit">Add Todo</button>
            </form>

            <div class="todo-list">
                ${todos.map(todo => html`
                    <div key=${todo.id} class=${`todo-item ${todo.completed ? 'completed' : ''}`}>
                        <input 
                            type="checkbox"
                            checked=${todo.completed}
                            onChange=${() => toggleTodo(todo.id)}
                        />
                        <span class="todo-text" style="flex: 1;">${todo.text}</span>
                        <button 
                            onClick=${() => deleteTodo(todo.id)}
                            style="background: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;"
                        >
                            Delete
                        </button>
                    </div>
                `)}
            </div>

            <p style="margin-top: 15px; color: #666; text-align: center;">
                ${todos.filter(t => !t.completed).length} pending, 
                ${todos.filter(t => t.completed).length} completed
            </p>
        </div>
    `;
}

// Export as default
export default TodoApp;
