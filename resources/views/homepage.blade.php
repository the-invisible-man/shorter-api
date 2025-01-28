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
    </style>
</head>
<body>
<div class="container">
    <div class="logo-placeholder">ShortLink</div>

    <input type="text" id="longUrl" placeholder="Enter a long URL here...">
    <button onclick="shortenUrl()">Shorten URL</button>

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
</div>

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
            console.log(data);
            // alert(`Shortened URL: ${data.short_url}`);
        } catch (error) {
            alert(`Error: ${error.message}`);
        }
    }

    function uploadCsv() {
        const fileInput = document.getElementById('csvFile');
        const file = fileInput.files[0];
        if (!file) {
            alert('Please select a CSV file to upload.');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(event) {
            const csvContent = event.target.result;
            // Add your CSV processing logic here
            console.log('Uploaded CSV content:', csvContent);
            alert('CSV file uploaded successfully!');
        };

        reader.readAsText(file);
    }
</script>
</body>
</html>
