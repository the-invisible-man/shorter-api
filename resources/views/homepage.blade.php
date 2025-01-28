<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShortLink</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(145deg, #1e3c5a, #112840);
            color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: #12263a;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.4);
            text-align: center;
            width: 90%;
            max-width: 500px;
        }

        .logo-placeholder {
            width: 120px;
            height: 120px;
            background: #1e3c5a;
            border: 2px dashed #f0f0f0;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.2em;
            color: #f0f0f0;
            background: url('./img/logo.webp') no-repeat center;
            background-size: contain;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: none;
            border-radius: 8px;
            background: #1e3c5a;
            color: #f0f0f0;
            font-size: 1em;
        }

        input[type="text"]:focus {
            outline: none;
            box-shadow: 0 0 5px #3fa9f5;
        }

        button {
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            background: #3fa9f5;
            color: #f0f0f0;
            font-size: 1em;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #2b87cc;
        }

        .csv-upload {
            margin-top: 20px;
            text-align: left;
        }

        .instructions {
            font-size: 0.9em;
            margin-bottom: 10px;
        }

        .csv-upload input[type="file"] {
            display: block;
            margin: 10px 0;
        }

        .short-url {
            margin-top: 20px;
            padding: 10px;
            background: #1e3c5a;
            border-radius: 8px;
            font-size: 1.5em;
            text-align: center;
            color: #3fa9f5;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.5s ease, transform 0.5s ease;
            cursor: pointer;
        }

        .short-url.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .copied-message {
            margin-top: 10px;
            font-size: 0.9em;
            color: #3fa9f5;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .copied-message.visible {
            opacity: 1;
        }

        .progress-bar-container {
            margin-top: 20px;
            text-align: center;
            display: none;
        }

        .progress-bar {
            width: 100%;
            background: #1e3c5a;
            border-radius: 8px;
            overflow: hidden;
            margin: 10px 0;
            height: 20px;
            position: relative;
        }

        .progress-bar-fill {
            height: 100%;
            background: #3fa9f5;
            width: 0%;
            transition: width 0.3s ease;
        }

        .progress-bar-text {
            position: absolute;
            width: 100%;
            text-align: center;
            top: 0;
            left: 0;
            height: 100%;
            line-height: 20px;
            color: #f0f0f0;
            font-size: 0.9em;
        }

        .status {
            font-size: 1em;
            margin-top: 5px;
        }

        .analytics {
            margin-top: 30px;
            padding: 15px;
            background: #1e3c5a;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .analytics h3 {
            margin-bottom: 10px;
        }

        .analytics .analytics-result {
            font-size: 1.2em;
            margin: 10px 0;
        }

        .analytics small {
            display: block;
            margin-top: 10px;
            font-size: 0.8em;
            color: #a0a0a0;
        }

        .analytics button {
            margin-top: 10px;
        }
    </style>
    <link rel="manifest" href="./manifest.json">
    <link rel="icon" type="image/x-icon" href="./favicon.ico">
</head>
<body>
<div class="container">
    <div class="logo-placeholder"></div>

    <input type="text" id="longUrl" placeholder="Enter a long URL here">
    <button onclick="shortenUrl()">Get ShortLink</button>

    <div id="shortUrlDisplay" class="short-url" onclick="copyToClipboard()"></div>
    <div id="copiedMessage" class="copied-message">Copied to clipboard!</div>

    <div class="csv-upload">
        <div class="instructions">
            <p>Or upload a CSV file with your URLs:</p>
            <ul>
                <li>Each row should have one URL.</li>
                <li>CSV format: no headers, just URLs.</li>
            </ul>
        </div>
        <input type="file" id="csvFile" accept=".csv">
        <button onclick="uploadCsv()">Upload CSV</button>
    </div>

    <div class="progress-bar-container" id="progressBarContainer">
        <div class="progress-bar">
            <div class="progress-bar-fill" id="progressBarFill"></div>
            <div class="progress-bar-text" id="progressBarText">0%</div>
        </div>
        <div class="status" id="status">Status: Pending</div>
    </div>

    <div class="analytics">
        <h3>Short URL Analytics</h3>
        <input type="text" id="shortUrlInput" placeholder="Paste your short URL here...">
        <button onclick="fetchAnalytics()">Get Analytics</button>
        <div class="analytics-result" id="analyticsResult">Visits: 0</div>
        <small>Eventually consistent to the minute</small>
        <button onclick="refreshAnalytics()">Refresh</button>
    </div>
</div>

<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script>
    async function shortenUrl() {
        const longUrl = document.getElementById('longUrl').value;
        if (!longUrl) {
            alert('Please enter a URL to shorten.');
            return;
        }
        // Add your API call logic here
        try {
            const response = await fetch('/shorten/v1/urls', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ long_url: longUrl }),
            });

            if (!response.ok) {
                const error = await response.json();
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
                const error = await response.json();
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
        const shortUrlDisplay = document.getElementById('shortUrlDisplay');
        shortUrlDisplay.textContent = `${shortUrl}`;
        shortUrlDisplay.classList.add('visible');
    }

    function copyToClipboard() {
        const shortUrlDisplay = document.getElementById('shortUrlDisplay');
        const copiedMessage = document.getElementById('copiedMessage');

        navigator.clipboard.writeText(shortUrlDisplay.textContent).then(() => {
            copiedMessage.classList.add('visible');
            setTimeout(() => {
                copiedMessage.classList.remove('visible');
            }, 2000);
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

            // Update status
            statusElement.textContent = `Status: ${status.charAt(0).toUpperCase() + status.slice(1)}`;

            // Update progress bar
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
        const progressBarContainer = document.getElementById('progressBarContainer');
        progressBarContainer.style.display = 'block';
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
        const input = document.getElementById('shortUrlInput').value;
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
        const resultElement = document.getElementById('analyticsResult');
        resultElement.textContent = `Visits: ${count}`;
    }
</script>
</body>
</html>
