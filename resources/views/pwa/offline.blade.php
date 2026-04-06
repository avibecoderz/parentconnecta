<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Offline | {{ config('app.name', 'ParentConnecta') }}</title>
        <style>
            :root {
                color-scheme: light;
            }

            body {
                margin: 0;
                min-height: 100vh;
                display: grid;
                place-items: center;
                background: radial-gradient(circle at 20% 0%, #dbeafe, #f8fafc 45%, #f1f5f9 100%);
                font-family: Figtree, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
                color: #0f172a;
            }

            .offline-card {
                width: min(92vw, 30rem);
                border-radius: 1.5rem;
                border: 1px solid #cbd5e1;
                background: rgba(255, 255, 255, 0.92);
                backdrop-filter: blur(8px);
                box-shadow: 0 20px 40px -30px rgba(15, 23, 42, 0.35);
                padding: 1.5rem;
            }

            h1 {
                margin: 0;
                font-size: 1.375rem;
            }

            p {
                margin: 0.75rem 0 0;
                line-height: 1.55;
                color: #334155;
            }

            .actions {
                margin-top: 1.25rem;
                display: flex;
                gap: 0.75rem;
                flex-wrap: wrap;
            }

            a,
            button {
                border: 0;
                border-radius: 0.875rem;
                padding: 0.625rem 0.9rem;
                font-weight: 600;
                font-size: 0.92rem;
                text-decoration: none;
                cursor: pointer;
            }

            .primary {
                color: #ffffff;
                background: #0d3b66;
            }

            .secondary {
                color: #0f172a;
                background: #e2e8f0;
            }
        </style>
    </head>
    <body>
        <section class="offline-card" role="status" aria-live="polite">
            <h1>You are offline</h1>
            <p>{{ config('app.name', 'ParentConnecta') }} could not reach the network. Check your internet connection and try again.</p>

            <div class="actions">
                <button class="primary" type="button" onclick="window.location.reload()">Try again</button>
                <a class="secondary" href="{{ url('/') }}">Go home</a>
            </div>
        </section>
    </body>
</html>
