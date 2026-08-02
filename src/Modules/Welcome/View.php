<?php

namespace App\Modules\Welcome;

/**
 * Welcome homepage view — no Model, no DB.
 */
class View
{
    public function home(): void
    {
        $docs = 'https://github.com/upMVC/upMVC';
        $agentDoc = 'https://github.com/upMVC/upMVC/blob/main/docs/AGENT_PACK.md';
        $saas = 'https://github.com/upMVC/upMVC-SaaS';
        $releases = 'https://github.com/upMVC/upMVC/releases';

        header('Content-Type: text/html; charset=utf-8');
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>upMVC — modular PHP, noFramework</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #0b1220;
            --fog: #d9e2f1;
            --mist: #8fa3c1;
            --signal: #d97706;
            --signal-soft: #fbbf24;
            --panel: rgba(12, 20, 36, 0.55);
            --line: rgba(217, 226, 241, 0.14);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { min-height: 100%; }
        body {
            font-family: Figtree, sans-serif;
            color: var(--fog);
            background:
                radial-gradient(1200px 700px at 12% -10%, rgba(217, 119, 6, 0.22), transparent 55%),
                radial-gradient(900px 600px at 100% 0%, rgba(56, 189, 248, 0.12), transparent 50%),
                linear-gradient(160deg, #070b14 0%, #101a2e 48%, #0b1220 100%);
            overflow-x: hidden;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(217, 226, 241, 0.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(217, 226, 241, 0.035) 1px, transparent 1px);
            background-size: 48px 48px;
            mask-image: radial-gradient(ellipse at center, black 20%, transparent 75%);
            pointer-events: none;
            animation: gridDrift 28s linear infinite;
        }
        @keyframes gridDrift {
            from { transform: translateY(0); }
            to { transform: translateY(48px); }
        }
        @keyframes rise {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .shell {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: clamp(1.5rem, 4vw, 3.5rem);
            max-width: 1100px;
            margin: 0 auto;
        }
        .brand {
            font-family: Fraunces, serif;
            font-weight: 700;
            font-size: clamp(3.4rem, 10vw, 6.5rem);
            letter-spacing: -0.04em;
            line-height: 0.92;
            animation: rise 0.8s ease both;
        }
        .brand span {
            background: linear-gradient(120deg, var(--fog) 20%, var(--signal-soft) 85%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .tag {
            margin-top: 1.25rem;
            max-width: 34rem;
            font-size: clamp(1.05rem, 2.2vw, 1.3rem);
            line-height: 1.55;
            color: var(--mist);
            animation: rise 0.9s ease 0.12s both;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 2rem;
            animation: rise 1s ease 0.22s both;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.85rem 1.25rem;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: transform 0.2s ease, background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }
        .btn:hover { transform: translateY(-2px); }
        .btn-primary {
            background: var(--signal);
            color: #1a1003;
        }
        .btn-primary:hover { background: var(--signal-soft); }
        .btn-ghost {
            border: 1px solid var(--line);
            color: var(--fog);
            background: var(--panel);
            backdrop-filter: blur(8px);
        }
        .btn-ghost:hover { border-color: rgba(251, 191, 36, 0.45); }
        .note {
            margin-top: 3.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--line);
            max-width: 40rem;
            font-size: 0.95rem;
            line-height: 1.6;
            color: var(--mist);
            animation: rise 1.05s ease 0.35s both;
        }
        .note code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.86em;
            color: var(--signal-soft);
        }
        .note a { color: var(--fog); }
    </style>
</head>
<body>
    <main class="shell">
        <h1 class="brand"><span>upMVC</span></h1>
        <p class="tag">
            Pure PHP modular MVC — a thin kernel, optional modules, and an AI agent pack.
            You are already inside the house.
        </p>
        <div class="actions">
            <a class="btn btn-primary" href="<?= htmlspecialchars($docs, ENT_QUOTES, 'UTF-8') ?>">GitHub &amp; docs</a>
            <a class="btn btn-ghost" href="<?= htmlspecialchars($agentDoc, ENT_QUOTES, 'UTF-8') ?>">AI Agent pack</a>
            <a class="btn btn-ghost" href="<?= htmlspecialchars($saas, ENT_QUOTES, 'UTF-8') ?>">upMVC-SaaS</a>
            <a class="btn btn-ghost" href="<?= htmlspecialchars($releases, ENT_QUOTES, 'UTF-8') ?>">Demo modules</a>
        </div>
        <p class="note">
            This page is the <strong>Welcome</strong> module — no database required.
            Homepage route lives in <code>src/Etc/custom-routes.php</code>
            (<code>'/'</code> → <code>Welcome\Controller</code>).
            Delete or change that entry anytime and point <code>/</code> at your own module.
            Optional show-off modules ship as a separate download on
            <a href="<?= htmlspecialchars($releases, ENT_QUOTES, 'UTF-8') ?>">Releases</a>
            — paste into <code>src/Modules/</code>, import demo SQL if needed, done.
        </p>
    </main>
</body>
</html>
        <?php
    }
}
