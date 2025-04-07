@echo off

rem Switch to D: drive (assuming the app is on D:)
D:

rem Navigate to the Laravel project directory
cd "D:\App laravel\les cafes ISMAILIA app" || (
    echo Directory not found: D:\App laravel\les cafes ISMAILIA app
    pause
    exit /b
)

rem Start XAMPP's MySQL and Apache services via command line
echo Starting Apache and MySQL services via XAMPP...

rem Start MySQL and Apache using XAMPP
start "" "C:\xampp\apache_start.bat"
start "" "C:\xampp\mysql_start.bat"

rem Wait for a few seconds to ensure MySQL and Apache have started
timeout /t 10 /nobreak

rem Start phpMyAdmin in the default browser
start "" "http://localhost/phpmyadmin"

rem Start the Laravel development server
start cmd /k "php artisan serve --port=8002"

rem Wait a few seconds to ensure the server has started
timeout /t 5 /nobreak

rem Navigate to the Vite project directory and run npm run dev
cd "D:\App laravel\les cafes ISMAILIA app\frontend" || (
    echo Directory not found: D:\App laravel\les cafes ISMAILIA app\frontend
    pause
    exit /b
)

rem Run npm run dev
start cmd /k "npm run dev"

rem Wait a few seconds to ensure the frontend server has started
timeout /t 5 /nobreak

# rem Open Chrome at the specified port
start "Chrome" "C:\Program Files\Google\Chrome\Application\chrome.exe" http://localhost:3002
