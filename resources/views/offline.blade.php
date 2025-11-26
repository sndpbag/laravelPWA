<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline - {{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, {{ config('pwa.theme_color') }} 0%, {{ config('pwa.background_color') }} 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .offline-container {
            text-align: center;
            max-width: 500px;
            background: white;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        }
        
        .offline-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: {{ config('pwa.theme_color') }};
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
        }
        
        .offline-icon svg {
            width: 60px;
            height: 60px;
            fill: white;
        }
        
        h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 15px;
        }
        
        p {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .retry-btn {
            display: inline-block;
            padding: 15px 40px;
            background: {{ config('pwa.theme_color') }};
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            border: none;
            font-size: 16px;
        }
        
        .retry-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        
        .offline-tips {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #eee;
        }
        
        .offline-tips h3 {
            font-size: 16px;
            color: #666;
            margin-bottom: 15px;
        }
        
        .offline-tips ul {
            list-style: none;
            text-align: left;
            display: inline-block;
        }
        
        .offline-tips li {
            padding: 8px 0;
            color: #888;
            font-size: 14px;
        }
        
        .offline-tips li:before {
            content: "✓ ";
            color: {{ config('pwa.theme_color') }};
            font-weight: bold;
            margin-right: 8px;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        @media (max-width: 600px) {
            .offline-container {
                padding: 40px 30px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .offline-icon {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>
<body>
    <div class="offline-container">
        <div class="offline-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
        </div>
        
        <h1>You're Offline</h1>
        <p>No internet connection detected. Please check your network settings and try again.</p>
        
        <button class="retry-btn" onclick="window.location.reload()">
            Retry Connection
        </button>
        
        <div class="offline-tips">
            <h3>Quick Tips:</h3>
            <ul>
                <li>Check your WiFi or mobile data</li>
                <li>Try airplane mode on/off</li>
                <li>Some features may work offline</li>
            </ul>
        </div>
    </div>
    
    <script>
        // Auto-retry when online
        window.addEventListener('online', () => {
            window.location.reload();
        });
        
        // Show connection status
        if (!navigator.onLine) {
            console.log('📡 No internet connection');
        }
    </script>
</body>
</html>