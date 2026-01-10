<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>AzipLink — Fast, simple URL shortener</title>
    <meta name="description" content="Got a long URL? Not anymore. Shorten links, bulk upload via CSV, and check visit analytics." />
    <meta name="robots" content="index,follow" />
    <link rel="canonical" href="https://azip.link/" />

    <!-- Open Graph -->
    <meta property="og:url" content="https://azip.link/" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="AzipLink — Fast, simple URL shortener" />
    <meta property="og:description" content="Shorten links, bulk upload via CSV, and check visit analytics." />
    <meta property="og:site_name" content="AzipLink" />
    <!-- TODO: set a real social preview image -->
    <meta property="og:image" content="https://azip.link/img/og-image.png" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:locale" content="en_US" />

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:domain" content="xhortl.ink" />
    <meta name="twitter:url" content="https://azip.link/" />
    <meta name="twitter:title" content="AzipLink — Fast, simple URL shortener" />
    <meta name="twitter:description" content="Shorten links, bulk upload via CSV, and check visit analytics." />
    <meta name="twitter:image" content="https://azip.link/img/og-image.png" />

    <!-- PWA / Icons -->
    <link rel="manifest" href="./manifest.json" />
    <link rel="icon" type="image/x-icon" href="./favicon.ico" />
    <meta name="theme-color" content="#0b1020" />

    <!-- Optional: structured data -->
    <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "WebSite",
          "name": "AzipLink",
          "url": "https://azip.link/",
          "description": "Shorten links, bulk upload via CSV, and check visit analytics."
        }
    </script>

    <style>
        :root{
            color-scheme: light;
            --bg:#0b1020;
            --card: rgba(255,255,255,.08);
            --card-border: rgba(255,255,255,.14);
            --text: rgba(255,255,255,.92);
            --muted: rgba(255,255,255,.70);
            --muted-2: rgba(255,255,255,.55);

            --danger:#ff5a6a;
            --warn:#fbbf24;
            --info:#60a5fa;

            --btn: rgba(255,255,255,.10);
            --btn-border: rgba(255,255,255,.18);
            --shadow: 0 20px 60px rgba(0,0,0,.45);

            --radius: 20px;
            --radius-sm: 14px;
        }

        * { box-sizing: border-box; }
        body{
            margin:0;
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji","Segoe UI Emoji";
            background:
                radial-gradient(1200px 600px at 20% 20%, rgba(255, 90, 106, .18), transparent 55%),
                radial-gradient(900px 500px at 80% 30%, rgba(251, 191, 36, .14), transparent 50%),
                radial-gradient(1000px 800px at 50% 90%, rgba(59, 130, 246, .12), transparent 55%),
                var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding: 28px 16px;
            display: grid;
            place-items: center;
        }

        a { color: inherit; }

        .wrap{
            width: 100%;
            max-width: 1040px;
        }

        .header{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap: 16px;
            margin-bottom: 14px;
        }

        .brand{
            display:flex;
            align-items:center;
            gap: 12px;
        }

        .logo{
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.14);
            box-shadow: 0 10px 30px rgba(0,0,0,.22);
            display:grid;
            place-items:center;
            overflow:hidden;
        }

        .logo img{
            width: 36px;
            height: 36px;
            object-fit: contain;
            display:block;
        }

        .brand h1{
            margin: 0;
            font-size: 18px;
            letter-spacing: .2px;
            line-height: 1.2;
        }

        .brand p{
            margin: 2px 0 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.4;
        }

        .pill{
            display:inline-flex;
            align-items:center;
            gap: 8px;
            padding: 8px 10px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.14);
            background: rgba(255,255,255,.06);
            color: var(--muted);
            font-size: 12px;
            white-space: nowrap;
        }

        .grid{
            display:grid;
            gap: 14px;
            grid-template-columns: 1fr;
        }

        @media (min-width: 860px){
            .grid{
                grid-template-columns: 1.2fr .8fr;
                align-items: start;
            }
        }

        .card{
            background: var(--card);
            border: 1px solid var(--card-border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow:hidden;
        }

        .card-head{
            padding: 18px 20px;
            border-bottom: 1px solid rgba(255,255,255,.10);
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap: 12px;
        }

        .card-title{
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .2px;
            line-height: 1.35;
        }

        .card-sub{
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.45;
        }

        .card-body{
            padding: 18px 20px;
            display: grid;
            gap: 12px;
        }

        .row{
            display:flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .input{
            width: 100%;
            padding: 12px 12px;
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,.14);
            background: rgba(255,255,255,.06);
            color: var(--text);
            font-size: 14px;
            outline: none;
            transition: box-shadow .2s ease, border-color .2s ease;
        }

        .input::placeholder{ color: rgba(255,255,255,.45); }

        .input:focus{
            border-color: rgba(96, 165, 250, .35);
            box-shadow: 0 0 0 4px rgba(96, 165, 250, .12);
        }

        .btn{
            appearance:none;
            border: 1px solid var(--btn-border);
            background: var(--btn);
            color: var(--text);
            padding: 11px 14px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 14px;
            cursor:pointer;
            transition: filter .15s ease, transform .05s ease;
            display:inline-flex;
            gap: 8px;
            align-items:center;
            justify-content:center;
            text-decoration:none;
            user-select:none;
        }

        .btn:hover{ filter: brightness(1.06); }
        .btn:active{ transform: translateY(1px); }

        .btn-primary{
            background: rgba(96, 165, 250, .16);
            border-color: rgba(96, 165, 250, .32);
        }

        .btn-ghost{
            background: rgba(255,255,255,.06);
            border-color: rgba(255,255,255,.12);
            color: rgba(255,255,255,.82);
            font-weight: 650;
        }

        .btn-danger{
            background: rgba(255, 90, 106, .14);
            border-color: rgba(255, 90, 106, .30);
        }

        .split{
            display:grid;
            gap: 10px;
            grid-template-columns: 1fr;
        }

        @media(min-width: 560px){
            .split{
                grid-template-columns: 1fr auto;
                align-items: center;
            }
        }

        .short-url{
            padding: 12px 12px;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,.12);
            background: rgba(255,255,255,.06);
            font-size: 16px;
            font-weight: 800;
            letter-spacing: .2px;
            color: rgba(255,255,255,.92);
            cursor: pointer;
            opacity: 0;
            transform: translateY(14px);
            transition: opacity .45s ease, transform .45s ease;
            word-break: break-all;
        }
        .short-url.visible{
            opacity: 1;
            transform: translateY(0);
        }

        .hint{
            font-size: 12px;
            color: var(--muted-2);
            margin-top: -4px;
        }

        .copied-message{
            font-size: 12px;
            color: rgba(96, 165, 250, .95);
            opacity: 0;
            transition: opacity .25s ease;
        }
        .copied-message.visible{ opacity: 1; }

        .divider{
            height: 1px;
            background: rgba(255,255,255,.10);
            margin: 2px 0;
        }

        .callout{
            display:flex;
            gap: 10px;
            padding: 12px 12px;
            border-radius: 16px;
            border: 1px solid rgba(251, 191, 36, .22);
            background: rgba(251, 191, 36, .10);
            color: rgba(255,255,255,.86);
            font-size: 13px;
            line-height: 1.45;
        }
        .callout svg{ flex: 0 0 auto; margin-top: 2px; }

        .csv{
            display:grid;
            gap: 10px;
        }

        .file{
            display:grid;
            gap: 8px;
            padding: 12px 12px;
            border-radius: 16px;
            border: 1px dashed rgba(255,255,255,.18);
            background: rgba(255,255,255,.05);
        }

        input[type="file"]{
            color: rgba(255,255,255,.75);
        }

        .progress-wrap{
            display:none;
            gap: 10px;
        }

        .progress-bar{
            width:100%;
            height: 22px;
            border-radius: 999px;
            overflow:hidden;
            position:relative;
            border: 1px solid rgba(255,255,255,.12);
            background: rgba(255,255,255,.06);
        }
        .progress-bar-fill{
            height:100%;
            width:0%;
            background: rgba(96, 165, 250, .75);
            transition: width .3s ease;
        }
        .progress-bar-text{
            position:absolute;
            inset:0;
            display:grid;
            place-items:center;
            font-size: 12px;
            font-weight: 800;
            color: rgba(255,255,255,.92);
            text-shadow: 0 2px 10px rgba(0,0,0,.35);
        }

        .status{
            font-size: 13px;
            color: var(--muted);
        }

        .analytics-result{
            padding: 12px 12px;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,.12);
            background: rgba(255,255,255,.06);
            font-size: 14px;
            font-weight: 800;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap: 12px;
        }

        .footer{
            margin-top: 14px;
            display:flex;
            justify-content:space-between;
            gap: 12px;
            flex-wrap: wrap;
            color: rgba(255,255,255,.55);
            font-size: 12px;
        }

        .kbd{
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 12px;
            padding: 2px 6px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,.14);
            background: rgba(255,255,255,.06);
            color: rgba(255,255,255,.75);
        }
    </style>
    <link rel="preconnect" href="https://challenges.cloudflare.com">
    <script
        src="https://challenges.cloudflare.com/turnstile/v0/api.js"
        async
        defer
    ></script>
</head>

<body>
<div class="wrap">
    <div class="header">
        <div class="brand">
            <div class="logo" aria-hidden="true">
                <img src="./img/logo.webp" alt="" onerror="this.style.display='none'; this.parentElement.textContent='SL'; this.parentElement.style.fontWeight='800';" />
            </div>
            <div>
                <h1><h1>a<span style="color:orange">zip</span>link</h1></h1>
                <p>Shorten links, bulk upload CSVs, and check visit analytics.</p>
            </div>
        </div>

        <div class="pill" title="Tip">
            <span class="kbd">Enter</span> to shorten
        </div>
    </div>

    <div class="grid">
        <!-- Left: Shorten + CSV -->
        <div class="card">
            <div class="card-head">
                <div>
                    <p class="card-title">Shorten a URL</p>
                    <p class="card-sub">Paste a destination URL and generate a short link.</p>
                </div>
            </div>

            <div class="card-body">
                <div class="split">
                    <input type="text" id="longUrl" class="input" placeholder="Enter a long URL here" autocomplete="off" />
                    <button class="btn btn-primary" onclick="shortenUrl()">
                        <!-- link icon -->
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M10 13a5 5 0 0 1 0-7l1.5-1.5a5 5 0 0 1 7 7L17 13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M14 11a5 5 0 0 1 0 7L12.5 19.5a5 5 0 1 1-7-7L7 11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Get ⚡️ZipLink
                    </button>
                </div>

                <div
                    class="cf-turnstile"
                    data-sitekey="0x4AAAAAACLWHN55A61n34C7"
                    data-theme="light"
                    data-size="normal"
                    data-callback="onSuccess"
                ></div>
                <div id="shortUrlDisplay" class="short-url" onclick="copyToClipboard()" role="button" tabindex="0"></div>
                <div class="hint">Click the short link to copy.</div>
                <div id="copiedMessage" class="copied-message">Copied to clipboard!</div>

                <div class="divider"></div>

                <div class="callout">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 2 1 21h22L12 2Z" stroke="currentColor" stroke-width="2" />
                        <path d="M12 9v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M12 17h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    <div>
                        Bulk uploads will generate an output CSV once processing completes.
                        Keep your file clean: one URL per row, no headers.
                    </div>
                </div>

                <div class="csv">
                    <div class="file">
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
                            <div style="font-weight:800;">Upload a CSV</div>
                            <div style="color: rgba(255,255,255,.55); font-size:12px;">.csv only</div>
                        </div>
                        <input type="file" id="csvFile" accept=".csv" />
                        <button class="btn btn-ghost" onclick="uploadCsv()">
                            <!-- upload icon -->
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 16V4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M7 9l5-5 5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M4 20h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            Upload CSV
                        </button>
                    </div>

                    <div class="progress-wrap" id="progressBarContainer">
                        <div class="progress-bar">
                            <div class="progress-bar-fill" id="progressBarFill"></div>
                            <div class="progress-bar-text" id="progressBarText">0%</div>
                        </div>
                        <div class="status" id="status">Status: Pending</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Analytics -->
        <div class="card">
            <div class="card-head">
                <div>
                    <p class="card-title">Short URL Analytics</p>
                    <p class="card-sub">Paste a short URL to see visit counts.</p>
                </div>
                <span class="pill" title="Note">Eventually consistent</span>
            </div>

            <div class="card-body">
                <input type="text" id="shortUrlInput" class="input" placeholder="Paste your short URL here..." autocomplete="off" />
                <div class="row">
                    <button class="btn btn-primary" onclick="fetchAnalytics()">
                        <!-- chart icon -->
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 19V5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M4 19h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M8 15v-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M12 15V7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M16 15v-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Get Analytics
                    </button>

                    <button class="btn btn-ghost" onclick="refreshAnalytics()">
                        <!-- refresh icon -->
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M20 12a8 8 0 1 1-2.34-5.66" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M20 4v6h-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Refresh
                    </button>
                </div>

                <div class="analytics-result" id="analyticsResult">
                    <span>Visits</span>
                    <span style="color: rgba(96,165,250,.95);">0</span>
                </div>

                <div class="hint">Counts can lag by up to a minute.</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div>© <span id="year"></span> RushOrbit Labs</div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <span class="pill">Click-to-copy enabled</span>
            <span class="pill">CSV jobs via Pusher</span>
        </div>
    </div>
</div>

<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script>
    document.getElementById('year').textContent = new Date().getFullYear();

    // Enter-to-shorten
    document.getElementById('longUrl').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') shortenUrl();
    });

    // Click-to-copy via keyboard too
    document.getElementById('shortUrlDisplay').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') copyToClipboard();
    });

    async function shortenUrl() {
        const longUrl = document.getElementById('longUrl').value.trim();
        if (!longUrl) {
            alert('Please enter a URL to shorten.');
            return;
        }

        try {
            const response = await fetch('/shorten/v1/urls', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ long_url: longUrl }),
            });

            if (!response.ok) {
                const error = await response.json().catch(() => ({}));
                throw new Error(error.message || 'Something went wrong');
            }

            const data = await response.json();
            revealShortUrl(data.data.short_url);
        } catch (error) {
            alert(`Error: ${error.message}`);
        }
    }

    async function uploadCsv() {
        const fileInput = document.getElementById('csvFile');
        const file = fileInput.files[0];

        if (!file) {
            alert('Please select a CSV file to upload.');
            return;
        }

        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await fetch('/shorten/v1/urls/jobs', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                const error = await response.json().catch(() => ({}));
                throw new Error(error.message || 'Failed to upload CSV file.');
            }

            const data = await response.json();
            showProgressBar();
            subscribeToJobUpdates(data.data.id);
        } catch (error) {
            console.log(error);
            alert(`Error: ${error.message}`);
        }
    }

    function revealShortUrl(shortUrl) {
        const el = document.getElementById('shortUrlDisplay');
        el.textContent = `${shortUrl}`;
        el.classList.add('visible');
    }

    function copyToClipboard() {
        const shortUrlDisplay = document.getElementById('shortUrlDisplay');
        const copiedMessage = document.getElementById('copiedMessage');

        if (!shortUrlDisplay.textContent) return;

        navigator.clipboard.writeText(shortUrlDisplay.textContent).then(() => {
            copiedMessage.classList.add('visible');
            setTimeout(() => copiedMessage.classList.remove('visible'), 2000);
        }).catch(err => {
            alert('Failed to copy to clipboard.');
            console.error(err);
        });
    }

    function subscribeToJobUpdates(jobId) {
        const pusher = new Pusher('69537eb215d61df4074a', {
            cluster: 'mt1',
            encrypted: true
        });

        const channel = pusher.subscribe(`jobs.${jobId}`);

        channel.bind('job.progress', function(data) {
            const { status, processed, total_rows } = data;

            const statusElement = document.getElementById('status');
            const progressBarFill = document.getElementById('progressBarFill');
            const progressBarText = document.getElementById('progressBarText');

            statusElement.textContent = `Status: ${status.charAt(0).toUpperCase() + status.slice(1)}`;

            if (status === 'in-progress' || status === 'completed') {
                const progress = (processed / total_rows) * 100;
                progressBarFill.style.width = `${progress}%`;
                progressBarText.textContent = `${Math.round(progress)}%`;
            }

            if (status === 'completed') {
                downloadFile(jobId);
            } else if (status === 'failed') {
                alert('CSV processing failed. Please try again.');
            }
        });
    }

    function showProgressBar() {
        document.getElementById('progressBarContainer').style.display = 'grid';
    }

    function downloadFile(jobId) {
        const url = `/shorten/v1/urls/jobs/download/${jobId}`;
        const anchor = document.createElement('a');
        anchor.href = url;
        anchor.download = `job_${jobId}_output.csv`;
        document.body.appendChild(anchor);
        anchor.click();
        document.body.removeChild(anchor);
    }

    async function fetchAnalytics() {
        const input = document.getElementById('shortUrlInput').value.trim();
        const shortUrlPath = extractShortUrlPath(input);

        if (!shortUrlPath) {
            alert('Please enter a valid short URL.');
            return;
        }

        try {
            const response = await fetch(`/analytics/v1/metrics/${shortUrlPath}`);
            if (response.status === 404) {
                updateAnalyticsResult(0);
                return;
            }

            if (!response.ok) {
                throw new Error('Failed to fetch analytics.');
            }

            const data = await response.json();
            updateAnalyticsResult(data.data.count);
        } catch (error) {
            alert(`Error: ${error.message}`);
        }
    }

    function refreshAnalytics() {
        fetchAnalytics();
    }

    function extractShortUrlPath(url) {
        const match = url.match(/\/r\/(.+)$/);
        return match ? match[1] : null;
    }

    function updateAnalyticsResult(count) {
        const el = document.getElementById('analyticsResult');
        el.innerHTML = `<span>Visits</span><span style="color: rgba(96,165,250,.95);">${count}</span>`;
    }
</script>
<script>
    !function(t,e){var o,n,p,r;e.__SV||(window.posthog && window.posthog.__loaded)||(window.posthog=e,e._i=[],e.init=function(i,s,a){function g(t,e){var o=e.split(".");2==o.length&&(t=t[o[0]],e=o[1]),t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}}(p=t.createElement("script")).type="text/javascript",p.crossOrigin="anonymous",p.async=!0,p.src=s.api_host.replace(".i.posthog.com","-assets.i.posthog.com")+"/static/array.js",(r=t.getElementsByTagName("script")[0]).parentNode.insertBefore(p,r);var u=e;for(void 0!==a?u=e[a]=[]:a="posthog",u.people=u.people||[],u.toString=function(t){var e="posthog";return"posthog"!==a&&(e+="."+a),t||(e+=" (stub)"),e},u.people.toString=function(){return u.toString(1)+".people (stub)"},o="init ts ns yi rs os Qr es capture Hi calculateEventProperties hs register register_once register_for_session unregister unregister_for_session fs getFeatureFlag getFeatureFlagPayload isFeatureEnabled reloadFeatureFlags updateFlags updateEarlyAccessFeatureEnrollment getEarlyAccessFeatures on onFeatureFlags onSurveysLoaded onSessionId getSurveys getActiveMatchingSurveys renderSurvey displaySurvey cancelPendingSurvey canRenderSurvey canRenderSurveyAsync identify setPersonProperties group resetGroups setPersonPropertiesForFlags resetPersonPropertiesForFlags setGroupPropertiesForFlags resetGroupPropertiesForFlags reset get_distinct_id getGroups get_session_id get_session_replay_url alias set_config startSessionRecording stopSessionRecording sessionRecordingStarted captureException startExceptionAutocapture stopExceptionAutocapture loadToolbar get_property getSessionProperty vs us createPersonProfile cs Yr ps opt_in_capturing opt_out_capturing has_opted_in_capturing has_opted_out_capturing get_explicit_consent_status is_capturing clear_opt_in_out_capturing ls debug O ds getPageViewId captureTraceFeedback captureTraceMetric Vr".split(" "),n=0;n<o.length;n++)g(u,o[n]);e._i.push([i,s,a])},e.__SV=1)}(document,window.posthog||[]);
    posthog.init('phc_lWjOvjWuccTUVj2ZfurYhkjVHDpuHBrsyuTl8Bjsamx', {
        api_host: 'https://us.i.posthog.com',
        defaults: '2025-11-30',
        person_profiles: 'identified_only', // or 'always' to create profiles for anonymous users as well
    })
</script>
</body>
</html>
