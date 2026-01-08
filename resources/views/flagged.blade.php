<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Link flagged for suspicious activity</title>

    <style>
        /* Fallback styling if Tailwind isn't present */
        :root {
            color-scheme: light;
            --bg: #0b1020;
            --card: rgba(255,255,255,.08);
            --card-border: rgba(255,255,255,.14);
            --text: rgba(255,255,255,.92);
            --muted: rgba(255,255,255,.70);
            --danger: #ff5a6a;
            --warn: #fbbf24;
            --btn: rgba(255,255,255,.10);
            --btn-border: rgba(255,255,255,.18);
            --shadow: 0 20px 60px rgba(0,0,0,.45);
        }
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji","Segoe UI Emoji";
            background:
                radial-gradient(1200px 600px at 20% 20%, rgba(255, 90, 106, .18), transparent 55%),
                radial-gradient(900px 500px at 80% 30%, rgba(251, 191, 36, .14), transparent 50%),
                radial-gradient(1000px 800px at 50% 90%, rgba(59, 130, 246, .12), transparent 55%),
                var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 28px 16px;
        }
        .wrap { width: 100%; max-width: 880px; }
        .card {
            background: var(--card);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        .top {
            padding: 22px 24px;
            border-bottom: 1px solid rgba(255,255,255,.10);
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }
        .badge {
            width: 40px; height: 40px;
            display: grid; place-items: center;
            border-radius: 12px;
            background: rgba(255, 90, 106, .16);
            border: 1px solid rgba(255, 90, 106, .28);
            flex: 0 0 auto;
        }
        .h1 { font-size: 18px; font-weight: 700; margin: 0; line-height: 1.3; }
        .sub { margin: 6px 0 0; color: var(--muted); font-size: 14px; line-height: 1.5; }
        .body { padding: 22px 24px; display: grid; gap: 14px; }
        .grid { display: grid; gap: 12px; grid-template-columns: 1fr; }
        @media (min-width: 760px) {
            .grid { grid-template-columns: 1fr 1fr; }
        }
        .panel {
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.10);
            border-radius: 16px;
            padding: 14px 14px;
        }
        .label { font-size: 12px; color: var(--muted); margin: 0 0 6px; letter-spacing: .02em; }
        .mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 13px;
            word-break: break-all;
        }
        .warning {
            display: flex;
            gap: 10px;
            padding: 14px 14px;
            border-radius: 16px;
            background: rgba(251, 191, 36, .12);
            border: 1px solid rgba(251, 191, 36, .22);
            color: rgba(255,255,255,.88);
        }
        .warning svg { flex: 0 0 auto; margin-top: 2px; }
        .warning p { margin: 0; font-size: 14px; line-height: 1.5; }
        .actions {
            padding: 18px 24px;
            border-top: 1px solid rgba(255,255,255,.10);
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
        }
        .left-actions { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        .btn {
            appearance: none;
            border: 1px solid var(--btn-border);
            background: var(--btn);
            color: var(--text);
            padding: 10px 14px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        .btn:hover { filter: brightness(1.06); }
        .btn-danger {
            background: rgba(255, 90, 106, .16);
            border-color: rgba(255, 90, 106, .32);
        }
        .btn-disabled {
            opacity: .55;
            cursor: not-allowed;
        }
        .check {
            display: inline-flex;
            gap: 10px;
            align-items: center;
            color: var(--muted);
            font-size: 13px;
            user-select: none;
        }
        .check input { width: 16px; height: 16px; }
        .fineprint { color: rgba(255,255,255,.55); font-size: 12px; line-height: 1.45; margin: 0; }
        .footer { margin-top: 14px; text-align: center; color: rgba(255,255,255,.55); font-size: 12px; }
    </style>
</head>
<body>
@php
    // Display-safe versions
    $shortCode = (string) $url->short_url;

    $rawLong = (string) $url->long_url;

    // Parse to show a "nice" host if possible
    $parts = @parse_url($rawLong);
    $host = $parts['host'] ?? null;

    // Ensure we never render javascript: etc. as a clickable href
    $scheme = strtolower($parts['scheme'] ?? '');
    $isHttp = in_array($scheme, ['http', 'https'], true);

    // Redacted display if you want to avoid showing full path; keeping full by default
    $displayLong = $rawLong;

    $href = $isHttp ? $rawLong : '#';
@endphp

<div class="wrap">
    <div class="card" role="alert" aria-live="polite">
        <div class="top">
            <div class="badge" aria-hidden="true">
                {{-- Alert icon --}}
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M12 9v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M12 17h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                    <path d="M10.3 3.7 1.8 18.3A2 2 0 0 0 3.5 21h17a2 2 0 0 0 1.7-2.7L13.7 3.7a2 2 0 0 0-3.4 0Z"
                          stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                </svg>
            </div>
            <div>
                <h1 class="h1">This link has been reported for suspicious activity</h1>
                <p class="sub">
                    For your safety, we’re showing a warning page instead of redirecting automatically.
                    If you choose to continue, you do so at your own risk.
                </p>
            </div>
        </div>

        <div class="body">
            <div class="grid">
                <div class="panel">
                    <p class="label">Short code</p>
                    <div class="mono">{{ $shortCode }}</div>
                </div>

                <div class="panel">
                    <p class="label">Destination</p>
                    <div class="mono">
                        @if($host)
                            <strong>{{ $host }}</strong>
                            <span style="color: rgba(255,255,255,.55);"> — </span>
                        @endif
                        {{ $displayLong }}
                    </div>
                </div>
            </div>

            <div class="warning">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 2 1 21h22L12 2Z" stroke="currentColor" stroke-width="2" />
                    <path d="M12 9v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M12 17h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                </svg>
                <p>
                    This destination may be attempting to steal information, install malware, or send unwanted spam.
                    If you don’t recognize the site, close this tab.
                </p>
            </div>
        </div>

        <div class="actions">
            <div class="left-actions">
                <a class="btn" href="{{ url('/') }}">
                    {{-- back icon --}}
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Go back
                </a>

                <form method="GET" action="{{ $rawLong }}" style="margin:0; display:inline-flex; gap:10px; align-items:center;">
                    @csrf

                    <button id="proceedBtn" class="btn btn-danger btn-disabled" type="submit" disabled>
                        Proceed anyway
                        <span id="countdown" style="color: rgba(255,255,255,.75); font-weight:700;"></span>
                    </button>

                    <label class="check" for="ack">
                        <input id="ack" type="checkbox">
                        I understand the risk
                    </label>
                </form>
            </div>

            <p class="fineprint">
                If you believe this warning is incorrect, contact the site or the person who sent you this link.
            </p>
        </div>
    </div>

    <div class="footer">
        Tip: You can also copy the destination and inspect it in a safer environment before opening.
    </div>
</div>

<script>
    (() => {
        const ack = document.getElementById('ack');
        const btn = document.getElementById('proceedBtn');
        const countdownEl = document.getElementById('countdown');

        let remaining = 3;
        let timer = null;

        function setDisabled(disabled) {
            btn.disabled = disabled;
            btn.classList.toggle('btn-disabled', disabled);
        }

        function resetCountdown() {
            remaining = 3;
            countdownEl.textContent = '';
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        }

        ack.addEventListener('change', () => {
            resetCountdown();

            if (!ack.checked) {
                setDisabled(true);
                return;
            }

            // Start a short countdown to reduce drive-by clicks
            setDisabled(true);
            countdownEl.textContent = `(${remaining})`;

            timer = setInterval(() => {
                remaining -= 1;

                if (remaining <= 0) {
                    clearInterval(timer);
                    timer = null;
                    countdownEl.textContent = '';
                    setDisabled(false);
                    return;
                }

                countdownEl.textContent = `(${remaining})`;
            }, 1000);
        });
    })();
</script>
</body>
</html>
