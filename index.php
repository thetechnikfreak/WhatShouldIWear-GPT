<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatShouldIWear GPT</title>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.locatecontrol/dist/L.Control.Locate.min.css" />

    <!-- Custom Styles -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f0f0f0;
        }
        .container {
            width: 80%;
            max-width: 800px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        #map {
            height: 300px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            font-weight: bold;
            margin-bottom: 8px;
        }
        input[type="text"] {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s ease-in-out;
        }
        input[type="text"]:focus {
            outline: none;
            border-color: #4CAF50;
        }
        button {
            padding: 12px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease-in-out;
        }
        button:hover {
            background-color: #45a049;
        }
        textarea {
            width: 100%;
            min-height: 100px;
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            resize: none;
            overflow-y: hidden; /* Hide scrollbars initially */
        }
    </style>
</head>
<body onload="initMap()">

    <div class="container">
        <h1>WhatShouldIWear GPT</h1>

        <div id="map"></div>

        <form id="activityForm" action="" method="post">
            <input type="hidden" id="latitude" name="latitude" readonly>
            <input type="hidden" id="longitude" name="longitude" readonly>
            <label for="whatdo">What do you want to do:</label>
            <input type="text" id="whatdo" name="whatdo" placeholder="Enter activity...">
            <button type="button" id="sendButton" onclick="generateResponse()">Submit</button>
        </form>

        <textarea id="responseData" name="responseData" placeholder="AI Response will appear here..." readonly></textarea>
    </div>

    <!-- Leaflet and Plugins Scripts -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script src="https://unpkg.com/leaflet.locatecontrol/dist/L.Control.Locate.min.js"></script>

    <script>
        const OPENAI_API_KEY = "sk-pxNju75vKyXJVhjySvLCTdKxluNcijXvQDgFmpRRjZIWgAqe"; // The API key
        var messages = [{ role: 'system', content: 'You are WhatShouldIWear GPT, an expert virtual stylist here to help people choose the perfect outfit. Your recommendations will be shown to the user.' }];
        var sentMessages = [];

        document.getElementById('whatdo').addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                generateResponse();
            }
        });

        async function fetchWeatherData(lat, lon) {
            try {
                const response = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current_weather=true`);
                const data = await response.json();
                console.log(data);
                return data;
            } catch (error) {
                console.error('Error fetching weather data:', error);
                return null;
            }
        }

        async function generateResponse() {
    const latitude = document.getElementById('latitude').value;
    const longitude = document.getElementById('longitude').value;
    const weather = await fetchWeatherData(latitude, longitude);
    const activity = document.getElementById('whatdo').value;

    if (weather !== null) {
        var inputText = `You are WhatShouldIWear GPT, an expert virtual stylist here to help people choose the perfect outfit. The user plans to do ${activity}.`;

        // Append weather information
        inputText += ` The current weather is ${weather.temperature}°C with ${weather.weathercode} weather condition. Wind speed is ${weather.windspeed} km/h from ${weather.winddirection}°.`;

        messages.push({ role: "user", content: inputText });
        sentMessages.push({ role: "user", content: inputText });
        console.log(messages);
        console.log(sentMessages);

        try {
            const response = await fetch('https://api.chatanywhere.tech/v1/chat/completions', {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${OPENAI_API_KEY}`,
                },
                body: JSON.stringify({
                    model: "gpt-3.5-turbo",
                    messages: sentMessages,
                    temperature: 1.0,
                    weather: {
                        temperature: weather.temperature,
                        weathercode: weather.weathercode,
                        windspeed: weather.windspeed,
                        winddirection: weather.winddirection
                    }
                }),
            });
            const responseData = await response.json();
            console.log(responseData);
            document.getElementById('responseData').value = responseData.choices[0].message.content; // Adjust according to the actual structure of responseData

            // Auto-adjust textarea height based on content
            adjustTextareaHeight('responseData');
        } catch (error) {
            console.error('Error fetching AI response:', error);
        }
    } else {
        console.error('Could not get weather data.');
    }
}

async function fetchWeatherData(lat, lon) {
    try {
        const response = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current_weather=true`);
        const data = await response.json();
        console.log(data);
        return data.current_weather;
    } catch (error) {
        console.error('Error fetching weather data:', error);
        return null;
    }
}


        // Function to adjust textarea height based on content
        function adjustTextareaHeight(id) {
            const textarea = document.getElementById(id);
            textarea.style.height = 'auto'; // Reset height to auto to properly calculate scrollHeight
            textarea.style.height = textarea.scrollHeight + 'px'; // Set height to scrollHeight of textarea content
        }

        function initMap() {
            var map = L.map('map').setView([51.505, -0.09], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            var marker = L.marker([51.505, -0.09], {
                draggable: true
            }).addTo(map);

            marker.on('dragend', function(e) {
                document.getElementById('latitude').value = marker.getLatLng().lat;
                document.getElementById('longitude').value = marker.getLatLng().lng;
            });

            var geocoder = L.Control.geocoder({
                defaultMarkGeocode: false
            })
            .on('markgeocode', function(e) {
                var latlng = e.geocode.center;
                map.setView(latlng, map.getZoom());
                marker.setLatLng(latlng);
                document.getElementById('latitude').value = latlng.lat;
                document.getElementById('longitude').value = latlng.lng;
            })
            .addTo(map);

            L.control.locate().addTo(map);
        }
    </script>
</body>
</html>
