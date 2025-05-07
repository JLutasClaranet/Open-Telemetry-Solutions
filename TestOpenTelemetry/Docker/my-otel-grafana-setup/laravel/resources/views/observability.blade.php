<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Observability App</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fc;
            color: #333;
        }

        .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 32px;
            color: #4f5b66;
            margin-bottom: 30px;
            text-align: center;
        }

        .button-group {
            display: flex;
            justify-content: space-around;
            gap: 20px;
            margin-bottom: 30px;
        }

        .button {
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 500;
            text-transform: uppercase;
            border: none;
            border-radius: 6px;
            background-color: #007bff;
            color: #ffffff;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .button:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        .button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .response {
            margin-top: 20px;
            padding: 20px;
            background-color: #f1f3f5;
            border-radius: 6px;
            color: #333;
            font-size: 14px;
            white-space: pre-wrap;
        }

        .response strong {
            color: #007bff;
        }

        .icon {
            width: 20px;
            height: 20px;
            vertical-align: middle;
        }

    </style>
</head>
<body>

    <div class="container">
        <h1>Observability Application</h1>

        <div class="button-group">
            <button id="triggerButton" class="button">
                <img src="https://img.icons8.com/ios-filled/50/ffffff/clock.png" class="icon" alt="Trigger Span Icon"> Trigger Manual Span
            </button>
            <button id="triggerContextButton" class="button">
                <img src="https://img.icons8.com/ios-filled/50/ffffff/stack.png" class="icon" alt="Context Trace Icon"> Trigger Manual Context Trace
            </button>
            <button id="healthButton" class="button">
                <img src="https://img.icons8.com/ios-filled/50/ffffff/heart-with-pulse--v1.png" class="icon" alt="heart-with-pulse--v1 icon"> Health Check
            </button>
        </div>

        <div id="response" class="response">
            <strong>Response:</strong> Please use the buttons above to trigger actions.
        </div>
    </div>

    <script>
        // Trigger a manual span
        document.getElementById('triggerButton').addEventListener('click', function() {
            axios.get('/trigger')
                .then(response => {
                    document.getElementById('response').innerHTML = `<strong>Response:</strong> ${JSON.stringify(response.data)}`;
                })
                .catch(error => {
                    console.error('Error triggering span:', error);
                    document.getElementById('response').innerHTML = '<strong>Error:</strong> Unable to trigger span.';
                });
        });

        // Trigger a trace with context
        document.getElementById('triggerContextButton').addEventListener('click', function() {
            axios.get('/trigger-context')
                .then(response => {
                    document.getElementById('response').innerHTML = `<strong>Response:</strong> ${JSON.stringify(response.data)}`;
                })
                .catch(error => {
                    console.error('Error triggering context trace:', error);
                    document.getElementById('response').innerHTML = '<strong>Error:</strong> Unable to trigger context trace.';
                });
        });

        // Perform health check
        document.getElementById('healthButton').addEventListener('click', function() {
            axios.get('/health')
                .then(response => {
                    document.getElementById('response').innerHTML = `<strong>Health Status:</strong> ${JSON.stringify(response.data)}`;
                })
                .catch(error => {
                    console.error('Error performing health check:', error);
                    document.getElementById('response').innerHTML = '<strong>Error:</strong> Unable to perform health check.';
                });
        });
    </script>

</body>
</html>
