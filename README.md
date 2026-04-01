# ✈️ Aviation Tools for Cisco IP Phones

A comprehensive, interactive flight tracking and airport information module specifically designed for Cisco IP Phone XML Services (e.g., Cisco 7942, 7962, and similar models). 

This PHP-based XML application brings live aviation data directly to the monochrome/grayscale screen of your Cisco IP Phone, generating dynamic images, maps, and menus on the fly.

## ✨ Features

*   **Live Flight Radar:** Track live flights with dynamic maps and telemetry (altitude, speed, heading) via ADSB.lol.
*   **Flight Status & Routing:** View detailed flight data, schedules, and route history via FlightRadar24.
*   **Live Airport Boards (FIDS):** Check real-time departure and arrival boards for any airport via AviationStack.
*   **Airport Information:** Look up comprehensive airport data, including runways, communication frequencies, navaids, weather/sun info, and local maps via AirportDB.
*   **Aircraft Photos:** Fetch and view photos of specific aircraft right on your phone screen via PlaneSpotters.net.
*   **Home Radar (Nearby Flights):** View a live radar map of aircraft currently flying near your configured home coordinates.
*   **Custom Image Rendering:** Automatically scales, crops, and optimizes maps, radar views, and aircraft photos for the 298x144 pixel Cisco IP Phone displays.

---

## 📋 Prerequisites

To run this application, you need a web server capable of hosting PHP scripts. The Cisco IP Phone must be able to reach this server via HTTP/HTTPS.

*   **Web Server:** Apache, Nginx, or any lightweight HTTP server.
*   **PHP Version:** PHP 7.4 or higher recommended.
*   **Required PHP Extensions:**
    *   `php-curl` (For fetching data from external APIs)
    *   `php-gd` (For generating and manipulating images for the phone display)
*   **Free API Keys:**
    *   [AirportDB.io](https://airportdb.io/) (For airport data)
    *   [AviationStack.com](https://aviationstack.com/) (For live departure/arrival boards)

---

## 🚀 Installation & Setup

### 1. Download the file
Download `aviation.php` and place it in the public HTML directory of your web server (e.g., `/var/www/html/`).

### 2. Configure the script
Open `aviation.php` in a text editor and locate the configuration section at the very top of the file. You must add your API keys and customize your location:

```php
// --- CONFIGURATION ---
define('APP_TIMEZONE', 'YOUR_TIMEZONE');                 // Set your local timezone (e.g., Europe/Berlin)
define('AIRPORTDB_TOKEN', 'YOUR_AIRPORTDB_API_KEY');     // Insert your AirportDB API token
define('AVIATIONSTACK_KEY', 'YOUR_AVIATIONSTACK_API_KEY'); // Insert your AviationStack API key
define('HOME_LAT', 0.0000);                              // Insert your Home Latitude (for Nearby Radar)
define('HOME_LON', 0.0000);                              // Insert your Home Longitude (for Nearby Radar)
```

### 3. Add to your Cisco IP Phone
You need to point your Cisco IP Phone to the script. This can be done in one of two ways:

**Method A: Via Cisco Unified Communications Manager (CUCM) or FreePBX/Asterisk Endpoint Manager**
1. Navigate to the XML Services configuration in your PBX.
2. Add a new service named "Aviation Tools".
3. Set the Service URL to the location of your script. Example:
   `http://<your-server-ip>/aviation.php`
4. Subscribe your phone to the newly created service.

**Method B: TFTP Server Configuration (Standalone / SIP)**
If you manage your phones manually via a TFTP server, the behavior of the "Services" button (or other line buttons) is defined in the phone's MAC-specific XML configuration file.
1. Open your phone's configuration file (e.g., `SEP<MAC_ADDRESS>.cnf.xml`) on your TFTP server.
2. Locate the `<servicesURL>` tag.
3. Point the tag to a menu file on your HTTP server (e.g., `service-menu.xml`) which links to the PHP script, or point it directly to the script:
   ```xml
   <servicesURL>http://<your-server-ip>/service-menu.xml</servicesURL>
   <!-- OR directly to the script: -->
   <!-- <servicesURL>http://<your-server-ip>/aviation.php</servicesURL> -->
   ```
4. Save the file and reboot the Cisco IP Phone so it can fetch the updated config from the TFTP server. Pressing the Services button will now load your Aviation Tools.

---

## 📡 API Usage & Limits

This application relies on a mix of open APIs and standard APIs. Please be mindful of rate limits:
*   **ADSB.lol / PlaneSpotters:** Public APIs, but please do not abuse them.
*   **FlightRadar24:** Uses public search endpoints. Heavy usage might result in temporary IP bans by FR24.
*   **AviationStack:** The free tier includes a limited amount of API calls per month. Every time you open a departure/arrival board, an API call is made. 
*   **AirportDB:** Generous free tier for retrieving airport data.

---

## 🤝 Credits

*   **Developed by:** Tammo Huth
*   *Partially coded with the assistance of AI.*
