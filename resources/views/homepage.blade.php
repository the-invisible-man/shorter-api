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
        }

        .progress-bar-fill {
            height: 100%;
            background: #3fa9f5;
            width: 0%;
            transition: width 0.3s ease;
        }

        .status {
            font-size: 1em;
            margin-top: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="logo-placeholder">ShortLink</div>

    <input type="text" id="longUrl" placeholder="Enter a long URL here...">
    <button onclick="shortenUrl()">Shorten URL</button>

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

    <div class="progress-bar-container">
        <div class="progress-bar">
            <div class="progress-bar-fill" id="progressBarFill"></div>
        </div>
        <div class="status" id="status">Status: Pending</div>
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

            // Update status
            statusElement.textContent = `Status: ${status.charAt(0).toUpperCase() + status.slice(1)}`;

            // Update progress bar
            if (status === 'in-progress' || status === 'completed') {
                const progress = (processed / total_rows) * 100;
                progressBarFill.style.width = `${progress}%`;
            }

            if (status === 'completed') {
                alert('CSV processing completed successfully!');
            } else if (status === 'failed') {
                alert('CSV processing failed. Please try again.');
            }
        });
    }

    function showProgressBar() {
        const progressBarContainer = document.getElementById('progressBarContainer');
        progressBarContainer.style.display = 'block';
    }
</script>
</body>
</html>
