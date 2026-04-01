<?php
/**
 * Aviation Tools for Cisco IP Phones
 * 
 * A comprehensive flight tracking and airport information module designed for 
 * Cisco IP Phone XML Services (e.g. Cisco 7962). 
 * 
 * Features:
 * - Live Flight Radar via ADSB.lol
 * - Flight Status & Routing via FlightRadar24
 * - Live Airport Boards via AviationStack
 * - Aircraft Photos via PlaneSpotters
 * - Airport Information via AirportDB

 */
define('APP_TIMEZONE', 'Europe/Berlin');
date_default_timezone_set(APP_TIMEZONE);

// =============================================================================
// CONFIGURATION & SEEDS
// =============================================================================

// API Keys (Please insert your own keys before deploying)
define('AIRPORTDB_TOKEN', 'YOUR_AIRPORTDB_API_KEY');
define('AVIATIONSTACK_KEY', 'YOUR_AVIATIONSTACK_API_KEY');
define('HOME_LAT', 0.0); // Home latitude placeholder
define('HOME_LON', 0.0); // Home longitude placeholder

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$server_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

$action = isset($_GET['action']) ? $_GET['action'] : 'menu';

// =============================================================================
// I. MAIN ENTRY MENU
// =============================================================================
if ($action === 'menu') {
    header("Content-Type: text/xml");
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<CiscoIPPhoneMenu>' . "\n";
    echo '  <Title>Aviation Tools</Title>' . "\n";
    echo '  <Prompt>Please select an option:</Prompt>' . "\n";
    echo '  <MenuItem>' . "\n";
    echo '    <Name>Flight Radar</Name>' . "\n";
    echo '    <URL>' . htmlspecialchars($server_url . '?action=input_flight') . '</URL>' . "\n";
    echo '  </MenuItem>' . "\n";
    echo '  <MenuItem>' . "\n";
    echo '    <Name>Airport Info</Name>' . "\n";
    echo '    <URL>' . htmlspecialchars($server_url . '?action=input_airport') . '</URL>' . "\n";
    echo '  </MenuItem>' . "\n";
    echo '  <MenuItem>' . "\n";
    echo '    <Name>Nearby Radar</Name>' . "\n";
    echo '    <URL>' . htmlspecialchars($server_url . '?action=show_home_map_xml') . '</URL>' . "\n";
    echo '  </MenuItem>' . "\n";
    echo '  <MenuItem>' . "\n";
    echo '    <Name>Aircraft Photo</Name>' . "\n";
    echo '    <URL>' . htmlspecialchars($server_url . '?action=input_photo') . '</URL>' . "\n";
    echo '  </MenuItem>' . "\n";
    echo '</CiscoIPPhoneMenu>';
    exit;
}

// =============================================================================
// II. INPUT MENUS (User Prompts)
// =============================================================================
if ($action === 'input_flight') {
    header("Content-Type: text/xml");
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<CiscoIPPhoneInput>' . "\n";
    echo '  <Title>Flight Radar</Title>' . "\n";
    echo '  <Prompt>Flight/Callsign (e.g. LH123):</Prompt>' . "\n";
    echo '  <URL>' . htmlspecialchars($server_url . '?action=show_flight_xml') . '</URL>' . "\n";
    echo '  <InputItem>' . "\n";
    echo '    <DisplayName>Flight Number</DisplayName>' . "\n";
    echo '    <QueryStringParam>flight</QueryStringParam>' . "\n";
    echo '    <DefaultValue></DefaultValue>' . "\n";
    echo '    <InputFlags>A</InputFlags>' . "\n";
    echo '  </InputItem>' . "\n";
    echo '</CiscoIPPhoneInput>';
    exit;
}

if ($action === 'input_photo') {
    header("Content-Type: text/xml");
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<CiscoIPPhoneInput>' . "\n";
    echo '  <Title>Aircraft Photos</Title>' . "\n";
    echo '  <Prompt>Registration (e.g. DAIMA):</Prompt>' . "\n";
    echo '  <URL>' . htmlspecialchars($server_url . '?action=show_photo_xml') . '</URL>' . "\n";
    echo '  <InputItem>' . "\n";
    echo '    <DisplayName>Registration</DisplayName>' . "\n";
    echo '    <QueryStringParam>reg</QueryStringParam>' . "\n";
    echo '    <DefaultValue></DefaultValue>' . "\n";
    echo '    <InputFlags>A</InputFlags>' . "\n";
    echo '  </InputItem>' . "\n";
    echo '</CiscoIPPhoneInput>';
    exit;
}

if ($action === 'input_airport') {
    header("Content-Type: text/xml");
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<CiscoIPPhoneInput>' . "\n";
    echo '  <Title>Airport Info</Title>' . "\n";
    echo '  <Prompt>ICAO Code (e.g. EDDH):</Prompt>' . "\n";
    echo '  <URL>' . htmlspecialchars($server_url . '?action=show_airport_xml') . '</URL>' . "\n";
    echo '  <InputItem>' . "\n";
    echo '    <DisplayName>ICAO Code</DisplayName>' . "\n";
    echo '    <QueryStringParam>icao</QueryStringParam>' . "\n";
    echo '    <DefaultValue></DefaultValue>' . "\n";
    echo '    <InputFlags>U</InputFlags>' . "\n";
    echo '  </InputItem>' . "\n";
    echo '</CiscoIPPhoneInput>';
    exit;
}

// =============================================================================
// III. XML CONTROLLERS (Routing & Action Mapping)
// =============================================================================
if ($action === 'show_flight_xml' || $action === 'show_flight_details_xml' || $action === 'show_flight_map_xml') {
    $flight = isset($_GET['flight']) ? strtoupper(trim($_GET['flight'])) : '';
    $view = isset($_GET['view']) ? $_GET['view'] : 'overview';
    $hex = isset($_GET['hex']) ? strtolower(trim($_GET['hex'])) : '';

    // Live update every 30 seconds!
    header("Refresh: 30; url=" . $server_url . "?action=" . $action . "&flight=" . urlencode($flight) . "&view=" . urlencode($view) . "&hex=" . urlencode($hex));
    header("Content-Type: text/xml");

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<CiscoIPPhoneImageFile>' . "\n";

    if ($action === 'show_flight_map_xml') {
        echo '  <Title>Map: ' . htmlspecialchars($flight) . '</Title>' . "\n";
        echo '  <Prompt>View: ' . htmlspecialchars($view) . '</Prompt>' . "\n";
        echo '  <URL>' . htmlspecialchars($server_url . '?action=render_flight_map&flight=' . urlencode($flight) . '&view=' . urlencode($view) . '&hex=' . urlencode($hex)) . '</URL>' . "\n";

        echo '  <SoftKeyItem>' . "\n";
        echo '    <Name>Route</Name>' . "\n";
        echo '    <URL>' . htmlspecialchars($server_url . '?action=show_flight_map_xml&flight=' . urlencode($flight) . '&view=overview&hex=' . urlencode($hex)) . '</URL>' . "\n";
        echo '    <Position>1</Position>' . "\n";
        echo '  </SoftKeyItem>' . "\n";

        echo '  <SoftKeyItem>' . "\n";
        echo '    <Name>Plane</Name>' . "\n";
        echo '    <URL>' . htmlspecialchars($server_url . '?action=show_flight_map_xml&flight=' . urlencode($flight) . '&view=plane&hex=' . urlencode($hex)) . '</URL>' . "\n";
        echo '    <Position>2</Position>' . "\n";
        echo '  </SoftKeyItem>' . "\n";

        echo '  <SoftKeyItem>' . "\n";
        echo '    <Name>Target</Name>' . "\n";
        echo '    <URL>' . htmlspecialchars($server_url . '?action=show_flight_map_xml&flight=' . urlencode($flight) . '&view=target&hex=' . urlencode($hex)) . '</URL>' . "\n";
        echo '    <Position>3</Position>' . "\n";
        echo '  </SoftKeyItem>' . "\n";

        echo '  <SoftKeyItem>' . "\n";
        echo '    <Name>Back</Name>' . "\n";
        echo '    <URL>' . htmlspecialchars($server_url . '?action=show_flight_xml&flight=' . urlencode($flight) . '&hex=' . urlencode($hex)) . '</URL>' . "\n";
        echo '    <Position>4</Position>' . "\n";
        echo '  </SoftKeyItem>' . "\n";

    } else {
        echo '  <Title>Flight: ' . htmlspecialchars($flight) . '</Title>' . "\n";
        echo '  <Prompt>Last Update: ' . date('H:i:s') . '</Prompt>' . "\n";

        $render_action = ($action === 'show_flight_details_xml') ? 'render_flight_details' : 'render_flight';
        echo '  <URL>' . htmlspecialchars($server_url . '?action=' . $render_action . '&flight=' . urlencode($flight) . '&hex=' . urlencode($hex)) . '</URL>' . "\n";

        echo '  <SoftKeyItem>' . "\n";
        echo '    <Name>Map</Name>' . "\n";
        echo '    <URL>' . htmlspecialchars($server_url . '?action=show_flight_map_xml&flight=' . urlencode($flight) . '&view=overview&hex=' . urlencode($hex)) . '</URL>' . "\n";
        echo '    <Position>1</Position>' . "\n";
        echo '  </SoftKeyItem>' . "\n";

        if ($action === 'show_flight_details_xml') {
            echo '  <SoftKeyItem>' . "\n";
            echo '    <Name>Radar</Name>' . "\n";
            echo '    <URL>' . htmlspecialchars($server_url . '?action=show_flight_xml&flight=' . urlencode($flight) . '&hex=' . urlencode($hex)) . '</URL>' . "\n";
            echo '    <Position>2</Position>' . "\n";
            echo '  </SoftKeyItem>' . "\n";
        } else {
            echo '  <SoftKeyItem>' . "\n";
            echo '    <Name>Details</Name>' . "\n";
            echo '    <URL>' . htmlspecialchars($server_url . '?action=show_flight_details_xml&flight=' . urlencode($flight) . '&hex=' . urlencode($hex)) . '</URL>' . "\n";
            echo '    <Position>2</Position>' . "\n";
            echo '  </SoftKeyItem>' . "\n";
        }

        echo '  <SoftKeyItem>' . "\n";
        echo '    <Name>Photo</Name>' . "\n";
        echo '    <URL>' . htmlspecialchars($server_url . '?action=show_flight_photo_xml&flight=' . urlencode($flight) . '&hex=' . urlencode($hex)) . '</URL>' . "\n";
        echo '    <Position>3</Position>' . "\n";
        echo '  </SoftKeyItem>' . "\n";

        echo '  <SoftKeyItem>' . "\n";
        if ($action === 'show_flight_details_xml') {
            echo '    <Name>Back</Name>' . "\n";
            echo '    <URL>' . htmlspecialchars($server_url . '?action=show_flight_xml&flight=' . urlencode($flight) . '&hex=' . urlencode($hex)) . '</URL>' . "\n";
        } else {
            echo '    <Name>Exit</Name>' . "\n";
            echo '    <URL>' . htmlspecialchars($server_url . '?action=menu') . '</URL>' . "\n";
        }
        echo '    <Position>4</Position>' . "\n";
        echo '  </SoftKeyItem>' . "\n";
    }

    echo '</CiscoIPPhoneImageFile>';
    exit;
}

if ($action === 'show_flight_photo_xml') {
    $flight = isset($_GET['flight']) ? strtoupper(trim($_GET['flight'])) : '';
    $hex = isset($_GET['hex']) ? strtolower(trim($_GET['hex'])) : '';
    header("Content-Type: text/xml");
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<CiscoIPPhoneImageFile>' . "\n";
    echo '  <Title>Photo ' . htmlspecialchars($flight) . '</Title>' . "\n";
    echo '  <Prompt>Full Screen</Prompt>' . "\n";
    echo '  <LocationX>0</LocationX>' . "\n";
    echo '  <LocationY>0</LocationY>' . "\n";
    echo '  <URL>' . htmlspecialchars($server_url . '?action=show_flight_photo_img&flight=' . urlencode($flight) . '&hex=' . urlencode($hex)) . '</URL>' . "\n";
    echo '  <SoftKeyItem>' . "\n";
    echo '    <Name>Back</Name>' . "\n";
    echo '    <URL>' . htmlspecialchars($server_url . '?action=show_flight_xml&flight=' . urlencode($flight) . '&hex=' . urlencode($hex)) . '</URL>' . "\n";
    echo '    <Position>4</Position>' . "\n";
    echo '  </SoftKeyItem>' . "\n";
    echo '</CiscoIPPhoneImageFile>';
    exit;
}

if ($action === 'show_photo_xml') {
    $reg = isset($_GET['reg']) ? strtoupper(trim($_GET['reg'])) : '';
    header("Content-Type: text/xml");
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<CiscoIPPhoneImageFile>' . "\n";
    echo '  <Title>PHOTO ' . htmlspecialchars($reg) . '</Title>' . "\n";
    echo '  <Prompt>Full Screen View</Prompt>' . "\n";
    echo '  <LocationX>0</LocationX>' . "\n";
    echo '  <LocationY>0</LocationY>' . "\n";
    echo '  <URL>' . htmlspecialchars($server_url . '?action=render_photo&reg=' . urlencode($reg)) . '</URL>' . "\n";
    echo '  <SoftKeyItem>' . "\n";
    echo '    <Name>Back</Name>' . "\n";
    echo '    <URL>' . htmlspecialchars($server_url . '?action=input_photo') . '</URL>' . "\n";
    echo '    <Position>1</Position>' . "\n";
    echo '  </SoftKeyItem>' . "\n";
    echo '  <SoftKeyItem>' . "\n";
    echo '    <Name>Exit</Name>' . "\n";
    echo '    <URL>' . htmlspecialchars($server_url . '?action=menu') . '</URL>' . "\n";
    echo '    <Position>4</Position>' . "\n";
    echo '  </SoftKeyItem>' . "\n";
    echo '</CiscoIPPhoneImageFile>';
    exit;
}

// --- NEW ACTION: Nearby Flights Radar Map ---
if ($action === 'show_home_map_xml') {
    $z = isset($_GET['z']) ? intval($_GET['z']) : 10;

    $z_in = min(16, $z + 1);
    $z_out = max(5, $z - 1);

    header("Content-Type: text/xml");
    header("Refresh: 30; url=" . $server_url . '?action=show_home_map_xml&z=' . $z);
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<CiscoIPPhoneImageFile>' . "\n";
    echo '  <Title>Home Radar (Z=' . $z . ')</Title>' . "\n";
    echo '  <Prompt>Local Radar</Prompt>' . "\n";
    echo '  <URL>' . htmlspecialchars($server_url . '?action=render_home_map&z=' . $z) . '</URL>' . "\n";
    echo '  <SoftKeyItem><Name>Out</Name><URL>' . htmlspecialchars($server_url . '?action=show_home_map_xml&z=' . $z_out) . '</URL><Position>1</Position></SoftKeyItem>' . "\n";
    echo '  <SoftKeyItem><Name>In</Name><URL>' . htmlspecialchars($server_url . '?action=show_home_map_xml&z=' . $z_in) . '</URL><Position>2</Position></SoftKeyItem>' . "\n";
    echo '  <SoftKeyItem><Name>Select</Name><URL>' . htmlspecialchars($server_url . '?action=show_nearby_flieger_list_xml') . '</URL><Position>3</Position></SoftKeyItem>' . "\n";
    echo '  <SoftKeyItem><Name>Menu</Name><URL>' . htmlspecialchars($server_url . '?action=menu') . '</URL><Position>4</Position></SoftKeyItem>' . "\n";
    echo '</CiscoIPPhoneImageFile>';
    exit;
}

// --- NEW ACTION: Nearby Flights Menu (Radar) ---
if ($action === 'show_nearby_flieger_list_xml') {
    header("Content-Type: text/xml");
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<CiscoIPPhoneMenu>' . "\n";
    echo '  <Title>Flights @Home</Title>' . "\n";
    echo '  <Prompt>Nearby (75km):</Prompt>' . "\n";

    $range_km = 75;
    $dLat = $range_km / 111.1;
    $dLon = $range_km / (111.1 * cos(deg2rad(HOME_LAT)));
    $lamin = HOME_LAT - $dLat;
    $lamax = HOME_LAT + $dLat;
    $lomin = HOME_LON - $dLon;
    $lomax = HOME_LON + $dLon;

    $url = "https://api.adsb.lol/v2/lat/" . HOME_LAT . "/lon/" . HOME_LON . "/dist/" . round($range_km / 1.852);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'AviationTools/1.0');
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    $res = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $nearby_flights = [];
    $error_msg = "";

    if ($http_code === 200 && $res) {
        $data = json_decode($res, true);
        if (isset($data['ac']) && is_array($data['ac']) && count($data['ac']) > 0) {
            foreach ($data['ac'] as $st) {
                $callsign = isset($st['flight']) ? trim($st['flight']) : '';
                if (empty($callsign) && isset($st['r']))
                    $callsign = trim($st['r']);
                if (empty($callsign))
                    continue;

                $lon = isset($st['lon']) ? $st['lon'] : null;
                $lat = isset($st['lat']) ? $st['lat'] : null;

                if ($lat !== null && $lon !== null) {
                    $dist = getDistance(HOME_LAT, HOME_LON, $lat, $lon);
                    if ($dist <= $range_km) {
                        $altVal = "N/A";
                        if (isset($st['alt_baro']) && is_numeric($st['alt_baro'])) {
                            $altVal = round($st['alt_baro']);
                        } elseif (isset($st['alt_geom']) && is_numeric($st['alt_geom'])) {
                            $altVal = round($st['alt_geom']);
                        } elseif (isset($st['alt_baro']) && $st['alt_baro'] === 'ground') {
                            $altVal = 0;
                        }

                        $nearby_flights[] = [
                            'callsign' => $callsign,
                            'hex' => isset($st['hex']) ? $st['hex'] : '',
                            'dist' => $dist,
                            'alt' => $altVal
                        ];
                    }
                }
            }
        } else {
            $error_msg = "No aircraft in range";
        }
    } elseif ($http_code === 429) {
        $error_msg = "Limit reached (ADSB.lol)";
    } else {
        $error_msg = "API Error (Code: $http_code)";
    }

    if (count($nearby_flights) === 0) {
        echo '  <MenuItem>' . "\n";
        echo '    <Name>' . htmlspecialchars($error_msg ? $error_msg : "No Aircraft") . '</Name>' . "\n";
        echo '    <URL>' . htmlspecialchars($server_url . '?action=menu') . '</URL>' . "\n";
        echo '  </MenuItem>' . "\n";
        if ($http_code === 429) {
            echo '  <MenuItem>' . "\n";
            echo '    <Name>... please wait 10s ...</Name>' . "\n";
            echo '    <URL>' . htmlspecialchars($server_url . '?action=show_nearby_flieger_list_xml') . '</URL>' . "\n";
            echo '  </MenuItem>' . "\n";
        }
    } else {
        // Sort by distance
        usort($nearby_flights, function ($a, $b) {
            return $a['dist'] <=> $b['dist'];
        });

        // Top 15
        $count = 0;
        foreach ($nearby_flights as $fl) {
            $alt_str = is_numeric($fl['alt']) ? round($fl['alt']) . "ft" : "N/A";
            $name = sprintf("%-8s (%.1fkm) %s", $fl['callsign'], $fl['dist'], $alt_str);
            echo '  <MenuItem>' . "\n";
            echo '    <Name>' . htmlspecialchars($name) . '</Name>' . "\n";
            echo '    <URL>' . htmlspecialchars($server_url . '?action=show_flight_xml&flight=' . urlencode($fl['callsign']) . '&hex=' . urlencode($fl['hex'])) . '</URL>' . "\n";
            echo '  </MenuItem>' . "\n";
            if (++$count >= 15)
                break;
        }
    }

    echo '  <SoftKeyItem>' . "\n";
    echo '    <Name>Waehlen</Name>' . "\n";
    echo '    <URL>SoftKey:Select</URL>' . "\n";
    echo '    <Position>1</Position>' . "\n";
    echo '  </SoftKeyItem>' . "\n";

    echo '  <SoftKeyItem>' . "\n";
    echo '    <Name>Update</Name>' . "\n";
    echo '    <URL>' . htmlspecialchars($server_url . '?action=show_nearby_flieger_list_xml') . '</URL>' . "\n";
    echo '    <Position>2</Position>' . "\n";
    echo '  </SoftKeyItem>' . "\n";

    echo '  <SoftKeyItem>' . "\n";
    echo '    <Name>Back</Name>' . "\n";
    echo '    <URL>' . htmlspecialchars($server_url . '?action=show_home_map_xml') . '</URL>' . "\n";
    echo '    <Position>4</Position>' . "\n";
    echo '  </SoftKeyItem>' . "\n";

    echo '</CiscoIPPhoneMenu>';
    exit;
}

// =============================================================================
// IV. DATA FETCHING & API INTERFACES
// =============================================================================

function resolve_flight_info($flight_query, $hex_query = '')
{
    if ($hex_query)
        $queries[] = ['type' => 'hex', 'q' => $hex_query];
    $queries[] = ['type' => 'text', 'q' => $flight_query];

    foreach ($queries as $item) {
        $q = $item['q'];
        if (!$q)
            continue;
        $search_url = "https://www.flightradar24.com/v1/search/web/find?query=" . urlencode($q) . "&limit=10";

        $ch = curl_init($search_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36");
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $search_res = curl_exec($ch);
        $search_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $search_data = json_decode($search_res, true);
        if ($search_http === 200 && isset($search_data['results']) && count($search_data['results']) > 0) {
            foreach ($search_data['results'] as $res) {
                $match = false;
                $res_callsign = isset($res['detail']['callsign']) ? $res['detail']['callsign'] : '';
                $res_flight = isset($res['detail']['flight']) ? $res['detail']['flight'] : '';
                $res_reg = isset($res['detail']['reg']) ? $res['detail']['reg'] : '';
                $res_hex = isset($res['id']) ? $res['id'] : '';

                if ($item['type'] === 'hex') {
                    if (strcasecmp($res_hex, $q) === 0)
                        $match = true;
                } else {
                    if (strcasecmp($res_callsign, $q) === 0 || strcasecmp($res_flight, $q) === 0 || strcasecmp($res_reg, $q) === 0)
                        $match = true;
                }

                if ($match) {
                    return [
                        'live_id' => ($res['type'] === 'live') ? $res['id'] : null,
                        'callsign' => ($res_callsign ? $res_callsign : $flight_query),
                        'flight' => ($res_flight ? $res_flight : $flight_query),
                        'reg' => $res_reg,
                        'debug' => "Exact Match (" . $res['type'] . "): " . $q
                    ];
                }
            }
        }
    }
    // Final fallback: return raw
    return ['live_id' => null, 'callsign' => $flight_query, 'flight' => $flight_query, 'reg' => '', 'debug' => 'No Search Result'];
}


// Calculation of Distance between two Coordinates (Haversine formula)
function getDistance($lat1, $lon1, $lat2, $lon2)
{
    $earth_radius = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earth_radius * $c;
}


// --- AIRPORT DATA (AirportDB.io) ---
function get_airport_data($icao)
{
    if (!$icao || strlen($icao) < 3)
        return false;
    $url = "https://airportdb.io/api/v1/airport/" . strtoupper(trim($icao)) . "?apiToken=" . AIRPORTDB_TOKEN;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'CiscoPhoneApp/1.0');
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200 || !$res)
        return false;
    $data = json_decode($res, true);
    if (!$data || !isset($data['icao_code']))
        return false;
    return $data;
}

// --- AIRPORT BOARD (AviationStack) ---
function get_airport_board($iata, $type = 'dep')
{
    if (!AVIATIONSTACK_KEY || AVIATIONSTACK_KEY === '')
        return false;
    $param = ($type === 'arr') ? 'arr_iata' : 'dep_iata';
    $url = "http://api.aviationstack.com/v1/flights?access_key=" . AVIATIONSTACK_KEY . "&" . $param . "=" . strtoupper(trim($iata)) . "&limit=20";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'CiscoPhoneApp/1.0');
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    $res = curl_exec($ch);
    curl_close($ch);
    if (!$res)
        return false;
    $data = json_decode($res, true);
    if (!$data || !isset($data['data']))
        return false;
    return $data['data'];
}

function get_planespotters_image($reg)
{
    if (!$reg || $reg == 'N/A')
        return false;
    $api_url = "https://api.planespotters.net/pub/photos/reg/" . urlencode($reg);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (CiscoPhoneApp/1.0)");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    if (isset($data['photos']) && count($data['photos']) > 0) {
        return [
            'url' => $data['photos'][0]['thumbnail_large']['src'],
            'photographer' => isset($data['photos'][0]['photographer']) ? $data['photos'][0]['photographer'] : 'Unknown'
        ];
    }
    return false;
}

function truncate_text($text, $max_len)
{
    if (strlen($text) > $max_len) {
        return substr($text, 0, $max_len - 3) . '...';
    }
    return $text;
}

// Sunrise/Sunset calculation from coordinates
function get_sunrise_sunset($lat, $lon)
{
    if (!$lat || !$lon)
        return false;
    $info = @date_sun_info(time(), $lat, $lon);
    if (!$info || !isset($info['sunrise']) || !isset($info['sunset']))
        return false;
    if ($info['sunrise'] === true || $info['sunset'] === true)
        return false;
    return [
        'sunrise' => date('H:i', $info['sunrise']),
        'sunset' => date('H:i', $info['sunset']),
        'dawn' => isset($info['civil_twilight_begin']) ? date('H:i', $info['civil_twilight_begin']) : '--:--',
        'dusk' => isset($info['civil_twilight_end']) ? date('H:i', $info['civil_twilight_end']) : '--:--',
        'day_length_h' => round(($info['sunset'] - $info['sunrise']) / 3600, 1),
    ];
}

// Map OSM Tile Helper
function lonToX($lon, $z)
{
    return (($lon + 180) / 360) * pow(2, $z);
}
function latToY($lat, $z)
{
    return (1 - log(tan(deg2rad($lat)) + 1 / cos(deg2rad($lat))) / pi()) / 2 * pow(2, $z);
}
// Intermediate Point on a Great Circle arc
function getGreatCirclePoint($lat1, $lon1, $lat2, $lon2, $f)
{
    if ($lat1 == $lat2 && $lon1 == $lon2)
        return [$lat1, $lon1];
    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);
    $dot = sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos($lon1 - $lon2);
    $dot = max(-1, min(1, $dot));
    $d = acos($dot);
    if ($d == 0)
        return [rad2deg($lat1), rad2deg($lon1)];
    $a = sin((1 - $f) * $d) / sin($d);
    $b = sin($f * $d) / sin($d);
    $x = $a * cos($lat1) * cos($lon1) + $b * cos($lat2) * cos($lon2);
    $y = $a * cos($lat1) * sin($lon1) + $b * cos($lat2) * sin($lon2);
    $z = $a * sin($lat1) + $b * sin($lat2);
    $lat = atan2($z, sqrt($x * $x + $y * $y));
    $lon = atan2($y, $x);
    return [rad2deg($lat), rad2deg($lon)];
}

function get_adsblol_data($icao24)
{
    if (!$icao24)
        return false;
    $url = "https://api.adsb.lol/v2/icao/" . strtolower(trim($icao24));
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'AviationTools/1.0');
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    $res = curl_exec($ch);
    curl_close($ch);
    if ($res) {
        $data = json_decode($res, true);
        if (isset($data['ac'][0])) {
            $st = $data['ac'][0];
            $alt = isset($st['alt_baro']) && is_numeric($st['alt_baro']) ? $st['alt_baro'] : (isset($st['alt_geom']) && is_numeric($st['alt_geom']) ? $st['alt_geom'] : 0);
            $vel = isset($st['gs']) ? $st['gs'] : 0;
            $hdg = isset($st['track']) ? $st['track'] : (isset($st['true_heading']) ? $st['true_heading'] : (isset($st['mag_heading']) ? $st['mag_heading'] : 0));

            return [
                'lon' => isset($st['lon']) ? $st['lon'] : null,
                'lat' => isset($st['lat']) ? $st['lat'] : null,
                'alt' => $alt,
                'vel' => $vel,
                'hdg' => $hdg
            ];
        }
    }
    return false;
}

function get_adsblol_traffic_bbox($minLat, $maxLat, $minLon, $maxLon)
{
    $clat = ($minLat + $maxLat) / 2;
    $clon = ($minLon + $maxLon) / 2;
    $dist = ceil(getDistance($minLat, $minLon, $maxLat, $maxLon) / 2 * 0.539957);
    $url = "https://api.adsb.lol/v2/lat/" . $clat . "/lon/" . $clon . "/dist/" . $dist;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'AviationTools/1.0');
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    $res = curl_exec($ch);
    curl_close($ch);
    if ($res) {
        $data = json_decode($res, true);
        if (isset($data['ac']) && is_array($data['ac'])) {
            $flights = [];
            foreach ($data['ac'] as $st) {
                if (isset($st['lon']) && isset($st['lat'])) {
                    $callsign = isset($st['flight']) ? trim($st['flight']) : '';
                    if (empty($callsign) && isset($st['r']))
                        $callsign = trim($st['r']);
                    $alt = isset($st['alt_baro']) && is_numeric($st['alt_baro']) ? $st['alt_baro'] : (isset($st['alt_geom']) && is_numeric($st['alt_geom']) ? $st['alt_geom'] : 0);
                    $vel = isset($st['gs']) ? $st['gs'] : 0;
                    $hdg = isset($st['track']) ? $st['track'] : (isset($st['true_heading']) ? $st['true_heading'] : (isset($st['mag_heading']) ? $st['mag_heading'] : 0));

                    $flights[] = [
                        'icao24' => isset($st['hex']) ? $st['hex'] : '',
                        'callsign' => $callsign,
                        'lon' => $st['lon'],
                        'lat' => $st['lat'],
                        'alt' => $alt,
                        'vel' => $vel,
                        'hdg' => $hdg
                    ];
                }
            }
            return $flights;
        }
    }
    return false;
}

// Calculation of Bearing between two Coordinates
function getBearing($lat1, $lon1, $lat2, $lon2)
{
    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);
    $dLon = $lon2 - $lon1;
    $y = sin($dLon) * cos($lat2);
    $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dLon);
    $brng = atan2($y, $x);
    return (rad2deg($brng) + 360) % 360;
}

// Simple drawing of an airplane icon
function drawAirplaneIcon($img, $cx, $cy, $bearing, $lineColor, $fillColor)
{
    $b = deg2rad($bearing);
    $f_x = sin($b);
    $f_y = -cos($b);
    $r_x = cos($b);
    $r_y = sin($b);
    $len = 10;
    $width = 8;
    $nose_x = $cx + $f_x * $len;
    $nose_y = $cy + $f_y * $len;
    $rw_x = $cx - $f_x * ($len * 0.4) + $r_x * $width;
    $rw_y = $cy - $f_y * ($len * 0.4) + $r_y * $width;
    $lw_x = $cx - $f_x * ($len * 0.4) - $r_x * $width;
    $lw_y = $cy - $f_y * ($len * 0.4) - $r_y * $width;
    $tail_x = $cx - $f_x * ($len * 0.6);
    $tail_y = $cy - $f_y * ($len * 0.6);
    $poly = [
        (int) $nose_x,
        (int) $nose_y,
        (int) $rw_x,
        (int) $rw_y,
        (int) $tail_x,
        (int) $tail_y,
        (int) $lw_x,
        (int) $lw_y
    ];
    imagefilledpolygon($img, $poly, 4, $fillColor);
    imagepolygon($img, $poly, 4, $lineColor);
}

// =============================================================================
// V. IMAGE RENDERING ENGINES
// =============================================================================

// --- PURE PHOTO RENDERER ---
if ($action === 'render_photo') {
    $reg = isset($_GET['reg']) ? strtoupper(trim($_GET['reg'])) : '';
    $photo_data = get_planespotters_image($reg);

    $dst_img = imagecreatetruecolor(298, 144);
    $black = imagecolorallocate($dst_img, 0, 0, 0);
    $white = imagecolorallocate($dst_img, 255, 255, 255);
    imagefill($dst_img, 0, 0, $white);

    if ($photo_data) {
        $imgStr = @file_get_contents($photo_data['url']);
        if ($imgStr) {
            $src_img = @imagecreatefromstring($imgStr);
            if ($src_img) {
                $w_orig = imagesx($src_img);
                $h_orig = imagesy($src_img);
                $ratio_orig = $w_orig / $h_orig;
                $ratio_target = 298 / 144;

                if ($ratio_orig > $ratio_target) {
                    $crop_w = (int) ($h_orig * $ratio_target);
                    $crop_h = $h_orig;
                    $crop_x = (int) (($w_orig - $crop_w) / 2);
                    $crop_y = 0;
                } else {
                    $crop_w = $w_orig;
                    $crop_h = (int) ($w_orig / $ratio_target);
                    $crop_x = 0;
                    $crop_y = (int) (($h_orig - $crop_h) / 2);
                }

                $scaled_img = imagecreatetruecolor(298, 144);
                imagecopyresampled($scaled_img, $src_img, 0, 0, $crop_x, $crop_y, 298, 144, $crop_w, $crop_h);
                imagedestroy($src_img);

                imagecopy($dst_img, $scaled_img, 0, 0, 0, 0, 298, 144);
                imagedestroy($scaled_img);
            } else {
                imagestring($dst_img, 3, 10, 60, "Image error", $black);
            }
        } else {
            imagestring($dst_img, 3, 10, 60, "Image offline", $black);
        }
    } else {
        imagestring($dst_img, 3, 10, 60, "No photo found for:", $black);
        imagestring($dst_img, 3, 10, 80, $reg, $black);
    }

    imagefilter($dst_img, IMG_FILTER_GRAYSCALE);
    imagefilter($dst_img, IMG_FILTER_CONTRAST, -15);
    imagetruecolortopalette($dst_img, false, 4);
    header("Content-Type: image/png");
    imagepng($dst_img);
    imagedestroy($dst_img);
    exit;
}

// --- FLIGHT RADAR & MAP RENDERER ---
if ($action === 'render_flight' || $action === 'render_flight_details' || $action === 'render_flight_map' || $action === 'show_flight_photo_img') {
    $flight_query = isset($_GET['flight']) ? strtoupper(trim($_GET['flight'])) : '';
    $hex_query = isset($_GET['hex']) ? strtolower(trim($_GET['hex'])) : '';
    $is_details = ($action === 'render_flight_details');
    $is_map = ($action === 'render_flight_map');
    $is_zoom_photo = ($action === 'show_flight_photo_img');
    $view = isset($_GET['view']) ? $_GET['view'] : 'overview';

    // UNIVERSAL SEARCH (v1 API)
    $search_info = resolve_flight_info($flight_query, $hex_query);
    $live_flight_id = $search_info['live_id'];
    $target_callsign = $search_info['callsign'];
    $target_flight = $search_info['flight'];
    $target_reg = $search_info['reg'];

    // 2. Fetch Detailed Data
    if ($target_reg) {
        $api_url = "https://api.flightradar24.com/common/v1/flight/list.json?query=" . urlencode($target_reg) . "&fetchBy=reg&page=1&limit=15";
    } else {
        $api_url = "https://api.flightradar24.com/common/v1/flight/list.json?query=" . urlencode($target_flight) . "&fetchBy=flight&page=1&limit=15";
    }
    // If we have a live flight ID, we can optionally use it for more precision later, 
    // but the list.json with the IATA flight number is still the best source for Route/History.

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    $flight = $target_callsign; // display callsign as primary

    if ($is_zoom_photo) {
        $found_reg = "";
        if (isset($data['result']['response']['data'][0]['aircraft']['registration'])) {
            $found_reg = $data['result']['response']['data'][0]['aircraft']['registration'];
        }
        // Redirect to render_photo if found
        if ($found_reg) {
            $photo_url = $server_url . "?action=render_photo&reg=" . urlencode($found_reg);
            header("Location: $photo_url");
            exit;
        } else {
            // Error image
            $dst_img = imagecreatetruecolor(298, 144);
            $white = imagecolorallocate($dst_img, 255, 255, 255);
            $black = imagecolorallocate($dst_img, 0, 0, 0);
            imagefill($dst_img, 0, 0, $white);
            imagestring($dst_img, 3, 10, 60, "Fahrzeug-Reg nicht gefunden", $black);
            header("Content-Type: image/png");
            imagepng($dst_img);
            imagedestroy($dst_img);
            exit;
        }
    }

    if ($is_zoom_photo) {
        $found_reg = "";
        if (isset($data['result']['response']['data'][0]['aircraft']['registration'])) {
            $found_reg = $data['result']['response']['data'][0]['aircraft']['registration'];
        }
        // Redirect to render_photo if found
        if ($found_reg) {
            $photo_url = $server_url . "?action=render_photo&reg=" . urlencode($found_reg);
            header("Location: $photo_url");
            exit;
        } else {
            // Error image
            $dst_img = imagecreatetruecolor(298, 144);
            $white = imagecolorallocate($dst_img, 255, 255, 255);
            $black = imagecolorallocate($dst_img, 0, 0, 0);
            imagefill($dst_img, 0, 0, $white);
            imagestring($dst_img, 3, 10, 60, "Aircraft Reg not found", $black);
            header("Content-Type: image/png");
            imagepng($dst_img);
            imagedestroy($dst_img);
            exit;
        }
    }

    $canvas_w = 298;
    $canvas_h = 144;
    $dst_img = imagecreatetruecolor($canvas_w, $canvas_h);

    $white = imagecolorallocate($dst_img, 255, 255, 255);
    $gray_light = imagecolorallocate($dst_img, 180, 180, 180);
    $gray_dark = imagecolorallocate($dst_img, 100, 100, 100);
    $black = imagecolorallocate($dst_img, 0, 0, 0);

    imagefill($dst_img, 0, 0, $white);

    if (isset($data['result']['response']['data']) && count($data['result']['response']['data']) > 0) {
        $flight_data = null;
        $best_diff = PHP_INT_MAX;
        $now = time();
        foreach ($data['result']['response']['data'] as $f) {
            if (isset($f['status']['live']) && $f['status']['live'] === true) {
                $flight_data = $f;
                break;
            }
            $sched_dep = isset($f['time']['scheduled']['departure']) ? $f['time']['scheduled']['departure'] : 0;
            if ($sched_dep > 0) {
                $diff = abs($now - $sched_dep);
                if ($diff < $best_diff) {
                    $best_diff = $diff;
                    $flight_data = $f;
                }
            }
        }
        if (!$flight_data)
            $flight_data = $data['result']['response']['data'][0];

        $origin = isset($flight_data['airport']['origin']['code']['iata']) ? $flight_data['airport']['origin']['code']['iata'] : 'N/A';
        $destination = isset($flight_data['airport']['destination']['code']['iata']) ? $flight_data['airport']['destination']['code']['iata'] : 'N/A';
        $latA = isset($flight_data['airport']['origin']['position']['latitude']) ? $flight_data['airport']['origin']['position']['latitude'] : 0;
        $lonA = isset($flight_data['airport']['origin']['position']['longitude']) ? $flight_data['airport']['origin']['position']['longitude'] : 0;
        $latB = isset($flight_data['airport']['destination']['position']['latitude']) ? $flight_data['airport']['destination']['position']['latitude'] : 0;
        $lonB = isset($flight_data['airport']['destination']['position']['longitude']) ? $flight_data['airport']['destination']['position']['longitude'] : 0;

        $status_text = isset($flight_data['status']['text']) ? $flight_data['status']['text'] : 'Unknown';
        $ac_model = isset($flight_data['aircraft']['model']['code']) ? $flight_data['aircraft']['model']['code'] : 'TBA';
        $ac_model_full = isset($flight_data['aircraft']['model']['text']) ? $flight_data['aircraft']['model']['text'] : '';
        $ac_reg = isset($flight_data['aircraft']['registration']) ? $flight_data['aircraft']['registration'] : 'N/A';
        $airline = isset($flight_data['airline']['name']) ? $flight_data['airline']['name'] : 'Unknown Airline';

        $time_dep_real = isset($flight_data['time']['real']['departure']) ? $flight_data['time']['real']['departure'] : 0;
        $time_dep_sched = isset($flight_data['time']['scheduled']['departure']) ? $flight_data['time']['scheduled']['departure'] : 0;
        $time_arr_est = isset($flight_data['time']['estimated']['arrival']) ? $flight_data['time']['estimated']['arrival'] : 0;
        $time_arr_sched = isset($flight_data['time']['scheduled']['arrival']) ? $flight_data['time']['scheduled']['arrival'] : 0;

        $time_dep = $time_dep_real ? $time_dep_real : $time_dep_sched;
        $time_arr = $time_arr_est ? $time_arr_est : $time_arr_sched;

        $progress = 0;
        $is_scheduled_only = false;

        if (!$time_dep_real && $time_dep_sched > $now) {
            $is_scheduled_only = true;
            $progress = 0;
        } else if ($time_arr > $time_dep && $time_dep > 0) {
            $total_time = $time_arr - $time_dep;
            $elapsed = max(0, $now - $time_dep);
            $progress = min(1, $elapsed / $total_time);
        }
        $heading_dyn = getBearing($latA, $lonA, $latB, $lonB);
        if ($progress > 0 && $progress < 1) {
            $planeCoordsCurr = getGreatCirclePoint($latA, $lonA, $latB, $lonB, $progress);
            $planeCoordsNext = getGreatCirclePoint($latA, $lonA, $latB, $lonB, min(1, $progress + 0.005));
            $heading_dyn = getBearing($planeCoordsCurr[0], $planeCoordsCurr[1], $planeCoordsNext[0], $planeCoordsNext[1]);
        }

        $altitude = 0;
        $speed = 0;
        if ($progress > 0 && $progress < 1) {
            if ($progress < 0.1) {
                $altitude = sin(($progress / 0.1) * (M_PI / 2)) * 36000;
                $speed = 250 + ($progress / 0.1) * 200;
            } else if ($progress >= 0.1 && $progress <= 0.85) {
                $altitude = 36000;
                $speed = 450;
            } else {
                $desc_prog = ($progress - 0.85) / 0.15;
                $altitude = cos($desc_prog * (M_PI / 2)) * 36000;
                $speed = 450 - ($desc_prog * 310);
            }
        }

        $hex_code = $hex_query ? $hex_query : (isset($flight_data['aircraft']['hex']) ? $flight_data['aircraft']['hex'] : '');
        $adsblol = get_adsblol_data($hex_code);
        $live_lat = false;
        $live_lon = false;
        $is_simulated = true;

        if ($adsblol) {
            $is_simulated = false;
            if ($adsblol['alt'])
                $altitude = $adsblol['alt'];
            if ($adsblol['vel'])
                $speed = $adsblol['vel'];
            if ($adsblol['hdg'])
                $heading_dyn = ($adsblol['hdg'] + 360) % 360;
            if ($adsblol['lat'] && $adsblol['lon']) {
                $live_lat = $adsblol['lat'];
                $live_lon = $adsblol['lon'];
            }
        }

        if ($time_arr < $now && $time_arr > 0) {
            $progress = 1.0;
        }
        $is_landed = ($progress >= 1.0 || stripos($status_text, 'Landed') !== false);

        if ($is_map) {
            // --- MAP RENDERING ENGINE ---
            // Fallbacks for missing geodata
            if (!$latA || !$latB) {
                imagestring($dst_img, 4, 10, 60, "Missing Geo-Data", $black);
            } else {
                $centerLat = 0;
                $centerLon = 0;
                $z = 4; // Zoom level

                if ($view === 'overview') {
                    // Calculate zoom including Great Circle arc
                    $minLat = min($latA, $latB);
                    $maxLat = max($latA, $latB);
                    $minLon = min($lonA, $lonB);
                    $maxLon = max($lonA, $lonB);
                    for ($f = 0; $f <= 1; $f += 0.1) {
                        $pt = getGreatCirclePoint($latA, $lonA, $latB, $lonB, $f);
                        $minLat = min($minLat, $pt[0]);
                        $maxLat = max($maxLat, $pt[0]);
                        $minLon = min($minLon, $pt[1]);
                        $maxLon = max($maxLon, $pt[1]);
                    }
                    $z = 7;
                    while ($z > 1) {
                        $dx = abs(lonToX($minLon, $z) - lonToX($maxLon, $z)) * 256;
                        $dy = abs(latToY($minLat, $z) - latToY($maxLat, $z)) * 256;
                        if ($dx < 220 && $dy < 90)
                            break;
                        $z--;
                    }
                    $centerLon = ($minLon + $maxLon) / 2;
                    $centerLat = ($minLat + $maxLat) / 2;
                } else if ($view === 'plane') {
                    $z = 6;
                    if ($altitude < 10000 && $progress > 0)
                        $z = 8;
                    if ($altitude < 5000 && $progress > 0)
                        $z = 10;
                    if ($altitude < 2000 && $progress > 0.8)
                        $z = 12;
                    if ($live_lat !== false) {
                        $centerLat = $live_lat;
                        $centerLon = $live_lon;
                    } else {
                        $planeCoords = getGreatCirclePoint($latA, $lonA, $latB, $lonB, $progress);
                        $centerLat = $planeCoords[0];
                        $centerLon = $planeCoords[1];
                    }
                } else if ($view === 'target') {
                    $z = 6;
                    $centerLon = $lonB;
                    $centerLat = $latB;
                }

                $centerX = lonToX($centerLon, $z) * 256;
                $centerY = latToY($centerLat, $z) * 256;

                $minTileX = floor(($centerX - 149) / 256);
                $maxTileX = floor(($centerX + 149) / 256);
                $minTileY = floor(($centerY - 72) / 256);
                $maxTileY = floor(($centerY + 72) / 256);

                $map_w = ($maxTileX - $minTileX + 1) * 256;
                $map_h = ($maxTileY - $minTileY + 1) * 256;
                $map_img = imagecreatetruecolor($map_w, $map_h);
                imagefill($map_img, 0, 0, imagecolorallocate($map_img, 240, 240, 240));

                for ($ty = $minTileY; $ty <= $maxTileY; $ty++) {
                    for ($tx = $minTileX; $tx <= $maxTileX; $tx++) {
                        $url = "https://tile.openstreetmap.org/$z/$tx/$ty.png";
                        $ch = curl_init($url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_USERAGENT, 'AviationTools/1.0');
                        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                        $tile_data = curl_exec($ch);
                        curl_close($ch);
                        if ($tile_data) {
                            $tile = @imagecreatefromstring($tile_data);
                            if ($tile) {
                                imagecopy($map_img, $tile, ($tx - $minTileX) * 256, ($ty - $minTileY) * 256, 0, 0, 256, 256);
                                imagedestroy($tile);
                            }
                        }
                    }
                }

                // Calculate lines and positions
                $x1 = lonToX($lonA, $z) * 256 - ($minTileX * 256);
                $y1 = latToY($latA, $z) * 256 - ($minTileY * 256);
                $x2 = lonToX($lonB, $z) * 256 - ($minTileX * 256);
                $y2 = latToY($latB, $z) * 256 - ($minTileY * 256);

                if ($live_lat !== false) {
                    $px = lonToX($live_lon, $z) * 256 - ($minTileX * 256);
                    $py = latToY($live_lat, $z) * 256 - ($minTileY * 256);
                } else {
                    $planeCoords = getGreatCirclePoint($latA, $lonA, $latB, $lonB, $progress);
                    $px = lonToX($planeCoords[1], $z) * 256 - ($minTileX * 256);
                    $py = latToY($planeCoords[0], $z) * 256 - ($minTileY * 256);
                }

                $lineColor = imagecolorallocate($map_img, 50, 50, 50);

                // Dynamic point density based on actual pixel distance in selected zoom level
                $distPix = sqrt(pow($x2 - $x1, 2) + pow($y2 - $y1, 2));
                $steps = max(40, (int) ($distPix / 5)); // One point every 5 pixels

                // Draw authentic Great Circle route
                for ($i = 0; $i <= $steps; $i++) {
                    $f = $i / $steps;
                    $pt = getGreatCirclePoint($latA, $lonA, $latB, $lonB, $f);
                    $cx = lonToX($pt[1], $z) * 256 - ($minTileX * 256);
                    $cy = latToY($pt[0], $z) * 256 - ($minTileY * 256);
                    imagefilledellipse($map_img, $cx, $cy, 3, 3, $lineColor);
                }

                // Mark start and destination
                $planeWhite = imagecolorallocate($map_img, 255, 255, 255);
                imagefilledrectangle($map_img, $x1 - 4, $y1 - 4, $x1 + 4, $y1 + 4, $lineColor);
                imagefilledrectangle($map_img, $x2 - 4, $y2 - 4, $x2 + 4, $y2 + 4, $lineColor);

                $thickColor = imagecolorallocate($map_img, 0, 0, 0);
                if ($progress >= 1)
                    $heading_dyn = getBearing($latA, $lonA, $latB, $lonB); // landed
                drawAirplaneIcon($map_img, $px, $py, $heading_dyn, $thickColor, $planeWhite);

                // Viewport crop
                $localCenterX = $centerX - ($minTileX * 256);
                $localCenterY = $centerY - ($minTileY * 256);
                imagecopy($dst_img, $map_img, 0, 0, $localCenterX - 149, $localCenterY - 72, 298, 144);
                imagedestroy($map_img);

                // No contrast filter, as it interferes with OSM rendering.

                // Text Overlay Header
                imagefilledrectangle($dst_img, 0, 0, 298, 14, $white);
                imageline($dst_img, 0, 15, 298, 15, $black);
                imagestring($dst_img, 3, 5, 1, "MAP: " . $flight . " ($origin->$destination) Z=" . $z, $black);

                // Telemetry Info-Box on map
                if ($view === 'plane' || $view === 'overview') {
                    $box_w = 85;
                    $box_h = 45;
                    $box_x = 2;
                    $box_y = 96;
                    imagefilledrectangle($dst_img, $box_x, $box_y, $box_x + $box_w, $box_y + $box_h, $white);
                    imagerectangle($dst_img, $box_x, $box_y, $box_x + $box_w, $box_y + $box_h, $black);
                    imagestring($dst_img, 2, $box_x + 3, $box_y + 2, "ALT: " . str_pad((int) round($altitude), 5, "0", STR_PAD_LEFT) . "ft", $black);
                    imagestring($dst_img, 2, $box_x + 3, $box_y + 12, "SPD: " . (int) round($speed) . "kts", $black);
                    imagestring($dst_img, 2, $box_x + 3, $box_y + 22, "HDG: " . str_pad((int) round($heading_dyn), 3, "0", STR_PAD_LEFT) . "deg", $black);
                    imagestring($dst_img, 2, $box_x + 3, $box_y + 32, $is_simulated ? "*SIMULATED*" : "LIVE ADSB", $is_simulated ? $gray_dark : $black);
                }
            }

        } else if ($is_details) {
            // --- DETAILS VIEW ---
            imagestring($dst_img, 4, 10, 5, $flight . " Details", $black);
            imageline($dst_img, 0, 22, $canvas_w, 22, $gray_dark);

            imagestring($dst_img, 3, 10, 25, "Airline: " . truncate_text($airline, 30), $black);
            imagestring($dst_img, 3, 10, 40, "Status:  " . truncate_text($status_text, 30), $black);
            imagestring($dst_img, 3, 10, 55, "Aircraft: " . truncate_text($ac_model_full ? $ac_model_full : $ac_model, 30), $black);
            imagestring($dst_img, 3, 10, 70, "Reg:      " . $ac_reg, $black);

            imagestring($dst_img, 2, 10, 85, $is_simulated ? "Telemetry (Simulated):" : "Live Telemetry (ADSB):", $gray_dark);
            imagestring($dst_img, 3, 10, 95, "ALT:" . (int) round($altitude) . "ft  SPD:" . (int) round($speed) . "kt  HDG:" . (int) round($heading_dyn) . "deg", $black);

            $dep_s = $time_dep_sched ? date('H:i', $time_dep_sched) : '--:--';
            $dep_r = $time_dep_real ? date('H:i', $time_dep_real) : '--:--';
            $arr_s = $time_arr_sched ? date('H:i', $time_arr_sched) : '--:--';
            $arr_e = $time_arr_est ? date('H:i', $time_arr_est) : '--:--';

            imagestring($dst_img, 2, 10, 110, "Departure:", $gray_dark);
            imagestring($dst_img, 3, 10, 120, "S:" . $dep_s . " R:" . $dep_r, $black);

            imagestring($dst_img, 2, 150, 110, "Arrival:", $gray_dark);
            imagestring($dst_img, 3, 150, 120, "S:" . $arr_s . " E:" . $arr_e, $black);

        } else {
            // --- MAIN RADAR VIEW ---
            $origin_city = isset($flight_data['airport']['origin']['region']['city']) ? $flight_data['airport']['origin']['region']['city'] : '';
            $dest_city = isset($flight_data['airport']['destination']['region']['city']) ? $flight_data['airport']['destination']['region']['city'] : '';

            // --- CLASSIC HEADER (Black on White + Line) ---
            $header_str = $flight . ($target_reg ? " [" . $target_reg . "]" : "");
            imagestring($dst_img, 4, 10, 5, $header_str, $black);

            // Local Arrival Time (Maintained in black)
            if ($time_arr > 0) {
                $arr_label = ($is_landed) ? "ARR " : "EST ";
                imagestring($dst_img, 2, 220, 7, $arr_label . date('H:i', $time_arr), $gray_dark);
            }

            imageline($dst_img, 0, 22, $canvas_w, 22, $gray_dark);

            $tmp_txt_img = imagecreatetruecolor(110, 20);
            imagefill($tmp_txt_img, 0, 0, $white);
            $route_str = $origin . " --> " . $destination;
            imagestring($tmp_txt_img, 5, 0, 0, $route_str, $black);

            $src_w = strlen($route_str) * 9;
            $src_h = 15;
            $scale = 1.6;
            $new_w = round($src_w * $scale);
            $new_h = round($src_h * $scale);

            imagecopyresampled($dst_img, $tmp_txt_img, 10, 26, 0, 0, $new_w, $new_h, $src_w, $src_h);
            imagedestroy($tmp_txt_img);

            $city_len = 10;
            $city_orig = truncate_text($origin_city, $city_len);
            $city_dest = truncate_text($dest_city, $city_len);
            imagestring($dst_img, 2, 12, 48, $city_orig, $gray_dark);
            imagestring($dst_img, 2, 128, 48, $city_dest, $gray_dark);

            imagestring($dst_img, 3, 10, 60, "Model: " . $ac_model, $gray_dark);
            imagestring($dst_img, 3, 10, 72, "Reg:   " . $ac_reg, $gray_dark);

            $mini_w = 90;
            $mini_h = 55;
            $pos_x = 195;
            $pos_y = 28;

            if ($ac_reg && $ac_reg !== 'N/A') {
                $photo_data = get_planespotters_image($ac_reg);
                if ($photo_data) {
                    $image_data = @file_get_contents($photo_data['url']);
                    if ($image_data !== false) {
                        $src_img = @imagecreatefromstring($image_data);
                        if ($src_img !== false) {
                            $src_w = imagesx($src_img);
                            $src_h = imagesy($src_img);
                            $ratio = max($mini_w / $src_w, $mini_h / $src_h);
                            $scaled_w = round($src_w * $ratio);
                            $scaled_h = round($src_h * $ratio);
                            $centered_x = round(($scaled_w - $mini_w) / 2);
                            $centered_y = round(($scaled_h - $mini_h) / 2);

                            $tmp_img_scale = imagecreatetruecolor($scaled_w, $scaled_h);
                            imagecopyresampled($tmp_img_scale, $src_img, 0, 0, 0, 0, $scaled_w, $scaled_h, $src_w, $src_h);

                            imagecopy($dst_img, $tmp_img_scale, $pos_x, $pos_y, $centered_x, $centered_y, $mini_w, $mini_h);
                            imagerectangle($dst_img, $pos_x, $pos_y, $pos_x + $mini_w, $pos_y + $mini_h, $gray_dark);

                            imagedestroy($src_img);
                            imagedestroy($tmp_img_scale);
                        }
                    }
                } else {
                    imagerectangle($dst_img, $pos_x, $pos_y, $pos_x + $mini_w, $pos_y + $mini_h, $gray_light);
                    imagestring($dst_img, 2, $pos_x + 5, $pos_y + 20, "NO PHOTO", $gray_light);
                }
            } else {
                imagerectangle($dst_img, $pos_x, $pos_y, $pos_x + $mini_w, $pos_y + $mini_h, $gray_light);
                imagestring($dst_img, 2, $pos_x + 5, $pos_y + 20, "PENDING", $gray_light);
            }

            $bar_x = 10;
            $bar_y = 96;
            $bar_w = 278;
            $bar_h = 16;

            if ($is_scheduled_only) {
                imagerectangle($dst_img, $bar_x, $bar_y, $bar_x + $bar_w, $bar_y + $bar_h, $gray_light);
                imagestring($dst_img, 3, $bar_x + 50, $bar_y + 1, "WAITING FOR DEPARTURE", $gray_dark);
            } else if ($is_landed) {
                imagefilledrectangle($dst_img, $bar_x, $bar_y, $bar_x + $bar_w, $bar_y + $bar_h, $black);
                imagestring($dst_img, 3, $bar_x + 105, $bar_y + 1, "LANDED", $white);
            } else {
                imagerectangle($dst_img, $bar_x, $bar_y, $bar_x + $bar_w, $bar_y + $bar_h, $black);
                if ($progress > 0) {
                    $fill_w = round($bar_w * $progress);
                    if ($fill_w > 0) {
                        imagefilledrectangle($dst_img, $bar_x + 1, $bar_y + 1, $bar_x + $fill_w - 1, $bar_y + $bar_h - 1, $black);
                        imagefilledellipse($dst_img, $bar_x + $fill_w, $bar_y + ($bar_h / 2), 6, 6, $white);
                        imagefilledellipse($dst_img, $bar_x + $fill_w, $bar_y + ($bar_h / 2), 4, 4, $black);
                    }
                }
            }

            $dep_str = $time_dep ? date('H:i', $time_dep) : '--:--';
            $arr_str = $time_arr ? date('H:i', $time_arr) : '--:--';

            imagestring($dst_img, 3, $bar_x, $bar_y + 20, "DEP: " . $dep_str, $gray_dark);
            imagestring($dst_img, 3, $bar_x + $bar_w - 80, $bar_y + 20, "ARR: " . $arr_str, $gray_dark);
        }

    } else {
        // --- ERROR HANDLING & DIAGNOSTICS ---
        $dst_img = imagecreatetruecolor(298, 144);
        $white = imagecolorallocate($dst_img, 255, 255, 255);
        $black = imagecolorallocate($dst_img, 0, 0, 0);
        $gray = imagecolorallocate($dst_img, 120, 120, 120);
        imagefill($dst_img, 0, 0, $white);

        imagestring($dst_img, 4, 10, 10, "Flight not found", $black);
        imageline($dst_img, 10, 30, 280, 30, $gray);

        imagestring($dst_img, 2, 10, 45, "Input:  " . substr($flight_query, 0, 20), $black);
        imagestring($dst_img, 2, 10, 60, "Hex:    " . ($hex_query ? $hex_query : 'None'), $black);
        imagestring($dst_img, 2, 10, 80, "Mapped: " . substr($target_flight, 0, 20) . " (" . ($target_reg ? $target_reg : 'NoReg') . ")", $black);
        imagestring($dst_img, 1, 10, 105, "Debug: " . ($search_info['debug'] ?? 'N/A'), $gray);
        imagestring($dst_img, 1, 10, 120, "API: list.json?query=" . substr($target_reg ? $target_reg : $target_flight, 0, 20), $gray);

        imagestring($dst_img, 2, 10, 132, "Please check callsign!", $black);

        header("Content-Type: image/png");
        imagepng($dst_img);
        imagedestroy($dst_img);
        exit;
    }

    imagefilter($dst_img, IMG_FILTER_GRAYSCALE);
    imagetruecolortopalette($dst_img, false, 4);

    header("Content-Type: image/png");
    imagepng($dst_img);
    imagedestroy($dst_img);
    exit;
}

if ($action === 'show_flight_photo_img') {
    $flight = isset($_GET['flight']) ? strtoupper(trim($_GET['flight'])) : '';

    // Get flight data from FR24 (same logic as main renderer)
    $api_url = "https://api.flightradar24.com/common/v1/flight/list.json?query=" . urlencode($flight) . "&fetchBy=flight&page=1&limit=15";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    $flight_data = null;
    if (isset($data['result']['response']['data']) && count($data['result']['response']['data']) > 0) {
        $best_diff = PHP_INT_MAX;
        $now = time();
        foreach ($data['result']['response']['data'] as $f) {
            if (isset($f['status']['live']) && $f['status']['live'] === true) {
                $flight_data = $f;
                break;
            }
            if (!isset($f['time']['scheduled']['departure']))
                continue;
            $diff = abs($now - $f['time']['scheduled']['departure']);
            if ($diff < $best_diff) {
                $best_diff = $diff;
                $flight_data = $f;
            }
        }
    }

    $dst_img = imagecreatetruecolor(298, 144);
    $black = imagecolorallocate($dst_img, 0, 0, 0);
    $white = imagecolorallocate($dst_img, 255, 255, 255);
    imagefill($dst_img, 0, 0, $white);

    if (!$flight_data) {
        imagestring($dst_img, 3, 10, 60, "Flight not found.", $black);
    } else {
        $ac_reg = isset($flight_data['aircraft']['registration']) ? $flight_data['aircraft']['registration'] : '';

        $thumbInfo = get_planespotters_image($ac_reg);
        if ($thumbInfo) {
            $imgStr = @file_get_contents($thumbInfo['url']);
            $ac_img = false;
            if ($imgStr) {
                $ac_img = @imagecreatefromstring($imgStr);
            }
            if ($ac_img) {
                $w_orig = imagesx($ac_img);
                $h_orig = imagesy($ac_img);
                $ratio_orig = $w_orig / $h_orig;
                $ratio_target = 298 / 144;

                if ($ratio_orig > $ratio_target) {
                    $crop_w = (int) ($h_orig * $ratio_target);
                    $crop_h = $h_orig;
                    $crop_x = (int) (($w_orig - $crop_w) / 2);
                    $crop_y = 0;
                } else {
                    $crop_w = $w_orig;
                    $crop_h = (int) ($w_orig / $ratio_target);
                    $crop_x = 0;
                    $crop_y = (int) (($h_orig - $crop_h) / 2);
                }

                $scaled_img = imagecreatetruecolor(298, 144);
                imagecopyresampled($scaled_img, $ac_img, 0, 0, $crop_x, $crop_y, 298, 144, $crop_w, $crop_h);
                imagedestroy($ac_img);

                imagecopy($dst_img, $scaled_img, 0, 0, 0, 0, 298, 144);
                imagedestroy($scaled_img);
            } else {
                imagestring($dst_img, 3, 10, 60, "Image loading error", $black);
            }
        } else {
            imagestring($dst_img, 3, 10, 60, "No photo available", $black);
        }
    }

    imagefilter($dst_img, IMG_FILTER_GRAYSCALE);
    imagefilter($dst_img, IMG_FILTER_CONTRAST, -15);
    imagetruecolortopalette($dst_img, false, 4);
    header("Content-Type: image/png");
    imagepng($dst_img);
    imagedestroy($dst_img);
    exit;
}

// =============================================================================
// FLUGHAFEN-MODUL XML MENÜS
// =============================================================================

// --- XML VIEWER: Airport Overview (Startbildschirm) ---
if ($action === 'show_airport_xml') {
    $icao = isset($_GET['icao']) ? strtoupper(trim($_GET['icao'])) : '';
    header("Content-Type: text/xml");
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<CiscoIPPhoneImageFile>' . "\n";
    echo '  <Title>' . htmlspecialchars($icao) . ' Info</Title>' . "\n";
    echo '  <Prompt>Airport Overview</Prompt>' . "\n";
    echo '  <URL>' . htmlspecialchars($server_url . '?action=render_airport_overview&icao=' . urlencode($icao)) . '</URL>' . "\n";

    // Softkeys for Overview Screen
    echo '  <SoftKeyItem>' . "\n";
    echo '    <Name>Menu</Name>' . "\n";
    echo '    <URL>' . htmlspecialchars($server_url . '?action=show_airport_menu_xml&icao=' . urlencode($icao)) . '</URL>' . "\n";
    echo '    <Position>1</Position>' . "\n";
    echo '  </SoftKeyItem>' . "\n";

    echo '  <SoftKeyItem>' . "\n";
    echo '    <Name>Runways</Name>' . "\n";
    echo '    <URL>' . htmlspecialchars($server_url . '?action=show_airport_runways_xml&icao=' . urlencode($icao) . '&page=0') . '</URL>' . "\n";
    echo '    <Position>2</Position>' . "\n";
    echo '  </SoftKeyItem>' . "\n";

    echo '  <SoftKeyItem>' . "\n";
    echo '    <Name>Board</Name>' . "\n";
    echo '    <URL>' . htmlspecialchars($server_url . '?action=show_airport_board_xml&icao=' . urlencode($icao) . '&type=dep&page=0') . '</URL>' . "\n";
    echo '    <Position>3</Position>' . "\n";
    echo '  </SoftKeyItem>' . "\n";

    echo '  <SoftKeyItem>' . "\n";
    echo '    <Name>Exit</Name>' . "\n";
    echo '    <URL>' . htmlspecialchars($server_url . '?action=menu') . '</URL>' . "\n";
    echo '    <Position>4</Position>' . "\n";
    echo '  </SoftKeyItem>' . "\n";

    echo '</CiscoIPPhoneImageFile>';
    exit;
}

// --- XML VIEWER: Airport Hauptmenü (Bypass 4-Softkey Limit) ---
if ($action === 'show_airport_menu_xml') {
    $icao = isset($_GET['icao']) ? strtoupper(trim($_GET['icao'])) : '';
    header("Content-Type: text/xml");
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<CiscoIPPhoneMenu>' . "\n";
    echo '  <Title>' . htmlspecialchars($icao) . ' Menu</Title>' . "\n";
    echo '  <Prompt>Please select:</Prompt>' . "\n";

    echo '  <MenuItem>' . "\n";
    echo '    <Name>Overview</Name>' . "\n";
    echo '    <URL>' . htmlspecialchars($server_url . '?action=show_airport_xml&icao=' . urlencode($icao)) . '</URL>' . "\n";
    echo '  </MenuItem>' . "\n";

    echo '  <MenuItem>' . "\n";
    echo '    <Name>Sun &amp; Weather</Name>' . "\n";
    echo '    <URL>' . htmlspecialchars($server_url . '?action=show_airport_weather_xml&icao=' . urlencode($icao)) . '</URL>' . "\n";
    echo '  </MenuItem>' . "\n";

    echo '  <MenuItem>' . "\n";
    echo '    <Name>Runways</Name>' . "\n";
    echo '    <URL>' . htmlspecialchars($server_url . '?action=show_airport_runways_xml&icao=' . urlencode($icao) . '&page=0') . '</URL>' . "\n";
    echo '  </MenuItem>' . "\n";

    echo '  <MenuItem>' . "\n";
    echo '    <Name>Frequencies</Name>' . "\n";
    echo '    <URL>' . htmlspecialchars($server_url . '?action=show_airport_freqs_xml&icao=' . urlencode($icao) . '&page=0') . '</URL>' . "\n";
    echo '  </MenuItem>' . "\n";

    echo '  <MenuItem>' . "\n";
    echo '    <Name>Navaids</Name>' . "\n";
    echo '    <URL>' . htmlspecialchars($server_url . '?action=show_airport_navaids_xml&icao=' . urlencode($icao) . '&page=0') . '</URL>' . "\n";
    echo '  </MenuItem>' . "\n";

    echo '  <MenuItem>' . "\n";
    echo '    <Name>Departures (FIDS)</Name>' . "\n";
    echo '    <URL>' . htmlspecialchars($server_url . '?action=show_airport_board_xml&icao=' . urlencode($icao) . '&type=dep&page=0') . '</URL>' . "\n";
    echo '  </MenuItem>' . "\n";

    echo '  <MenuItem>' . "\n";
    echo '    <Name>Arrivals (FIDS)</Name>' . "\n";
    echo '    <URL>' . htmlspecialchars($server_url . '?action=show_airport_board_xml&icao=' . urlencode($icao) . '&type=arr&page=0') . '</URL>' . "\n";
    echo '  </MenuItem>' . "\n";

    echo '  <MenuItem>' . "\n";
    echo '    <Name>Map</Name>' . "\n";
    echo '    <URL>' . htmlspecialchars($server_url . '?action=show_airport_map_xml&icao=' . urlencode($icao) . '&z=12') . '</URL>' . "\n";
    echo '  </MenuItem>' . "\n";

    echo '  <SoftKeyItem>' . "\n";
    echo '    <Name>Select</Name>' . "\n";
    echo '    <URL>SoftKey:Select</URL>' . "\n";
    echo '    <Position>1</Position>' . "\n";
    echo '  </SoftKeyItem>' . "\n";

    echo '  <SoftKeyItem>' . "\n";
    echo '    <Name>Back</Name>' . "\n";
    echo '    <URL>' . htmlspecialchars($server_url . '?action=show_airport_xml&icao=' . urlencode($icao)) . '</URL>' . "\n";
    echo '    <Position>4</Position>' . "\n";
    echo '  </SoftKeyItem>' . "\n";

    echo '</CiscoIPPhoneMenu>';
    exit;
}

// --- XML VIEWER: Weather & Sun ---
if ($action === 'show_airport_weather_xml') {
    $icao = isset($_GET['icao']) ? strtoupper(trim($_GET['icao'])) : '';
    header("Content-Type: text/xml");
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<CiscoIPPhoneImageFile>' . "\n";
    echo '  <Title>' . htmlspecialchars($icao) . ' Weather</Title>' . "\n";
    echo '  <Prompt>Sun &amp; Timezone</Prompt>' . "\n";
    echo '  <URL>' . htmlspecialchars($server_url . '?action=render_airport_weather&icao=' . urlencode($icao)) . '</URL>' . "\n";
    echo '  <SoftKeyItem><Name>Menu</Name><URL>' . htmlspecialchars($server_url . '?action=show_airport_menu_xml&icao=' . urlencode($icao)) . '</URL><Position>1</Position></SoftKeyItem>' . "\n";
    echo '  <SoftKeyItem><Name>Back</Name><URL>' . htmlspecialchars($server_url . '?action=show_airport_xml&icao=' . urlencode($icao)) . '</URL><Position>4</Position></SoftKeyItem>' . "\n";
    echo '</CiscoIPPhoneImageFile>';
    exit;
}

// --- XML VIEWER: Airport Map (Zoomable) ---
if ($action === 'show_airport_map_xml') {
    $icao = isset($_GET['icao']) ? strtoupper(trim($_GET['icao'])) : '';
    $z = isset($_GET['z']) ? intval($_GET['z']) : 12;

    $z_in = min(16, $z + 1);
    $z_out = max(5, $z - 1);

    header("Content-Type: text/xml");
    header("Refresh: 30; url=" . $server_url . '?action=show_airport_map_xml&icao=' . urlencode($icao) . '&z=' . $z);
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<CiscoIPPhoneImageFile>' . "\n";
    echo '  <Title>' . htmlspecialchars($icao) . ' Map (Z=' . $z . ')</Title>' . "\n";
    echo '  <Prompt>Map: ' . $icao . '</Prompt>' . "\n";
    echo '  <URL>' . htmlspecialchars($server_url . '?action=render_airport_map&icao=' . urlencode($icao) . '&z=' . $z) . '</URL>' . "\n";
    echo '  <SoftKeyItem><Name>Out</Name><URL>' . htmlspecialchars($server_url . '?action=show_airport_map_xml&icao=' . urlencode($icao) . '&z=' . $z_out) . '</URL><Position>1</Position></SoftKeyItem>' . "\n";
    echo '  <SoftKeyItem><Name>In</Name><URL>' . htmlspecialchars($server_url . '?action=show_airport_map_xml&icao=' . urlencode($icao) . '&z=' . $z_in) . '</URL><Position>2</Position></SoftKeyItem>' . "\n";
    echo '  <SoftKeyItem><Name>Update</Name><URL>SoftKey:Update</URL><Position>3</Position></SoftKeyItem>' . "\n";
    echo '  <SoftKeyItem><Name>Back</Name><URL>' . htmlspecialchars($server_url . '?action=show_airport_menu_xml&icao=' . urlencode($icao)) . '</URL><Position>4</Position></SoftKeyItem>' . "\n";
    echo '</CiscoIPPhoneImageFile>';
    exit;
}

// --- XML VIEWER: Navaids (blätterbar) ---
if ($action === 'show_airport_navaids_xml') {
    $icao = isset($_GET['icao']) ? strtoupper(trim($_GET['icao'])) : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
    $ap = get_airport_data($icao);
    $navaid_count = ($ap && isset($ap['navaids'])) ? count($ap['navaids']) : 0;
    $navaid_pages = max(1, ceil($navaid_count / 6));
    if ($page < 0)
        $page = 0;
    if ($navaid_count > 0 && $page >= $navaid_pages)
        $page = $navaid_pages - 1;
    header("Content-Type: text/xml");
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<CiscoIPPhoneImageFile>' . "\n";
    echo '  <Title>' . htmlspecialchars($icao) . ' Navaids</Title>' . "\n";
    echo '  <Prompt>Page ' . ($page + 1) . '/' . $navaid_pages . '</Prompt>' . "\n";
    echo '  <URL>' . htmlspecialchars($server_url . '?action=render_airport_navaids&icao=' . urlencode($icao) . '&page=' . $page) . '</URL>' . "\n";
    if ($page > 0) {
        echo '  <SoftKeyItem><Name>Prev</Name><URL>' . htmlspecialchars($server_url . '?action=show_airport_navaids_xml&icao=' . urlencode($icao) . '&page=' . ($page - 1)) . '</URL><Position>1</Position></SoftKeyItem>' . "\n";
    }
    if ($page < $navaid_pages - 1) {
        echo '  <SoftKeyItem><Name>Next</Name><URL>' . htmlspecialchars($server_url . '?action=show_airport_navaids_xml&icao=' . urlencode($icao) . '&page=' . ($page + 1)) . '</URL><Position>2</Position></SoftKeyItem>' . "\n";
    }
    echo '  <SoftKeyItem><Name>Menu</Name><URL>' . htmlspecialchars($server_url . '?action=show_airport_menu_xml&icao=' . urlencode($icao)) . '</URL><Position>3</Position></SoftKeyItem>' . "\n";
    echo '  <SoftKeyItem><Name>Back</Name><URL>' . htmlspecialchars($server_url . '?action=show_airport_xml&icao=' . urlencode($icao)) . '</URL><Position>4</Position></SoftKeyItem>' . "\n";
    echo '</CiscoIPPhoneImageFile>';
    exit;
}

// --- XML VIEWER: Runways (blätterbar) ---
if ($action === 'show_airport_runways_xml') {
    $icao = isset($_GET['icao']) ? strtoupper(trim($_GET['icao'])) : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
    $ap = get_airport_data($icao);
    $total_rwy = ($ap && isset($ap['runways'])) ? count($ap['runways']) : 0;
    if ($page < 0)
        $page = 0;
    if ($total_rwy > 0 && $page >= $total_rwy)
        $page = $total_rwy - 1;
    header("Content-Type: text/xml");
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<CiscoIPPhoneImageFile>' . "\n";
    echo '  <Title>' . htmlspecialchars($icao) . ' Runways</Title>' . "\n";
    echo '  <Prompt>Runway ' . ($page + 1) . '/' . max(1, $total_rwy) . '</Prompt>' . "\n";
    echo '  <URL>' . htmlspecialchars($server_url . '?action=render_airport_runways&icao=' . urlencode($icao) . '&page=' . $page) . '</URL>' . "\n";
    if ($page > 0) {
        echo '  <SoftKeyItem><Name>Prev</Name><URL>' . htmlspecialchars($server_url . '?action=show_airport_runways_xml&icao=' . urlencode($icao) . '&page=' . ($page - 1)) . '</URL><Position>1</Position></SoftKeyItem>' . "\n";
    }
    if ($page < $total_rwy - 1) {
        echo '  <SoftKeyItem><Name>Next</Name><URL>' . htmlspecialchars($server_url . '?action=show_airport_runways_xml&icao=' . urlencode($icao) . '&page=' . ($page + 1)) . '</URL><Position>2</Position></SoftKeyItem>' . "\n";
    }
    echo '  <SoftKeyItem><Name>Menu</Name><URL>' . htmlspecialchars($server_url . '?action=show_airport_menu_xml&icao=' . urlencode($icao)) . '</URL><Position>3</Position></SoftKeyItem>' . "\n";
    echo '  <SoftKeyItem><Name>Back</Name><URL>' . htmlspecialchars($server_url . '?action=show_airport_xml&icao=' . urlencode($icao)) . '</URL><Position>4</Position></SoftKeyItem>' . "\n";
    echo '</CiscoIPPhoneImageFile>';
    exit;
}

// --- XML VIEWER: Frequencies (blätterbar) ---
if ($action === 'show_airport_freqs_xml') {
    $icao = isset($_GET['icao']) ? strtoupper(trim($_GET['icao'])) : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
    $ap = get_airport_data($icao);
    $freq_count = ($ap && isset($ap['freqs'])) ? count($ap['freqs']) : 0;
    $freq_pages = max(1, ceil($freq_count / 6));
    if ($page < 0)
        $page = 0;
    if ($freq_count > 0 && $page >= $freq_pages)
        $page = $freq_pages - 1;
    header("Content-Type: text/xml");
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<CiscoIPPhoneImageFile>' . "\n";
    echo '  <Title>' . htmlspecialchars($icao) . ' Frequencies</Title>' . "\n";
    echo '  <Prompt>Page ' . ($page + 1) . '/' . $freq_pages . '</Prompt>' . "\n";
    echo '  <URL>' . htmlspecialchars($server_url . '?action=render_airport_freqs&icao=' . urlencode($icao) . '&page=' . $page) . '</URL>' . "\n";
    if ($page > 0) {
        echo '  <SoftKeyItem><Name>Prev</Name><URL>' . htmlspecialchars($server_url . '?action=show_airport_freqs_xml&icao=' . urlencode($icao) . '&page=' . ($page - 1)) . '</URL><Position>1</Position></SoftKeyItem>' . "\n";
    }
    if ($page < $freq_pages - 1) {
        echo '  <SoftKeyItem><Name>Next</Name><URL>' . htmlspecialchars($server_url . '?action=show_airport_freqs_xml&icao=' . urlencode($icao) . '&page=' . ($page + 1)) . '</URL><Position>2</Position></SoftKeyItem>' . "\n";
    }
    echo '  <SoftKeyItem><Name>Menu</Name><URL>' . htmlspecialchars($server_url . '?action=show_airport_menu_xml&icao=' . urlencode($icao)) . '</URL><Position>3</Position></SoftKeyItem>' . "\n";
    echo '  <SoftKeyItem><Name>Back</Name><URL>' . htmlspecialchars($server_url . '?action=show_airport_xml&icao=' . urlencode($icao)) . '</URL><Position>4</Position></SoftKeyItem>' . "\n";
    echo '</CiscoIPPhoneImageFile>';
    exit;
}

// --- XML VIEWER: Departure/Arrival Board (blätterbar + umschaltbar) ---
if ($action === 'show_airport_board_xml') {
    $icao = isset($_GET['icao']) ? strtoupper(trim($_GET['icao'])) : '';
    $type = isset($_GET['type']) ? $_GET['type'] : 'dep';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 0;

    $ap = get_airport_data($icao);
    $iata = ($ap && isset($ap['iata_code'])) ? $ap['iata_code'] : '';

    $board_total = 0;
    if ($iata && AVIATIONSTACK_KEY) {
        $flights = get_airport_board($iata, $type);
        $board_total = ($flights) ? count($flights) : 0;
    }
    $board_pages = max(1, ceil($board_total / 5));
    if ($page < 0)
        $page = 0;
    if ($board_total > 0 && $page >= $board_pages)
        $page = $board_pages - 1;

    $label = ($type === 'arr') ? 'Arrivals' : 'Departures';
    $toggle = ($type === 'arr') ? 'dep' : 'arr';
    $toggle_label = ($type === 'arr') ? 'Departure' : 'Arrival';

    header("Content-Type: text/xml");
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<CiscoIPPhoneImageFile>' . "\n";
    echo '  <Title>' . htmlspecialchars($icao) . ' ' . $label . '</Title>' . "\n";
    echo '  <Prompt>Page ' . ($page + 1) . '/' . $board_pages . '</Prompt>' . "\n";
    echo '  <URL>' . htmlspecialchars($server_url . '?action=render_airport_board&icao=' . urlencode($icao) . '&type=' . $type . '&page=' . $page) . '</URL>' . "\n";

    echo '  <SoftKeyItem><Name>' . $toggle_label . '</Name><URL>' . htmlspecialchars($server_url . '?action=show_airport_board_xml&icao=' . urlencode($icao) . '&type=' . $toggle . '&page=0') . '</URL><Position>1</Position></SoftKeyItem>' . "\n";

    if ($page < $board_pages - 1) {
        echo '  <SoftKeyItem><Name>Next</Name><URL>' . htmlspecialchars($server_url . '?action=show_airport_board_xml&icao=' . urlencode($icao) . '&type=' . $type . '&page=' . ($page + 1)) . '</URL><Position>2</Position></SoftKeyItem>' . "\n";
    }

    echo '  <SoftKeyItem><Name>Menu</Name><URL>' . htmlspecialchars($server_url . '?action=show_airport_menu_xml&icao=' . urlencode($icao)) . '</URL><Position>3</Position></SoftKeyItem>' . "\n";
    echo '  <SoftKeyItem><Name>Back</Name><URL>' . htmlspecialchars($server_url . '?action=show_airport_xml&icao=' . urlencode($icao)) . '</URL><Position>4</Position></SoftKeyItem>' . "\n";
    echo '</CiscoIPPhoneImageFile>';
    exit;
}

// =============================================================================
// FLUGHAFEN RENDER ENGINES
// =============================================================================

// --- RENDER: Airport Overview ---
if ($action === 'render_airport_overview') {
    $icao = isset($_GET['icao']) ? strtoupper(trim($_GET['icao'])) : '';
    $ap = get_airport_data($icao);

    $dst_img = imagecreatetruecolor(298, 144);
    $white = imagecolorallocate($dst_img, 255, 255, 255);
    $black = imagecolorallocate($dst_img, 0, 0, 0);
    $gray = imagecolorallocate($dst_img, 120, 120, 120);
    $dark = imagecolorallocate($dst_img, 60, 60, 60);
    $gray_light = imagecolorallocate($dst_img, 200, 200, 200);

    // Komplett weißer Hintergrund
    imagefill($dst_img, 0, 0, $white);

    if (!$ap) {
        imagestring($dst_img, 4, 10, 5, "Error", $black);
        imageline($dst_img, 0, 22, 298, 22, $gray);
        imagestring($dst_img, 3, 10, 30, "Airport not found:", $black);
        imagestring($dst_img, 5, 10, 50, $icao, $black);
        imagestring($dst_img, 2, 10, 80, "Check ICAO code or API key.", $gray);
    } else {
        $name = isset($ap['name']) ? $ap['name'] : '';
        $iata = isset($ap['iata_code']) ? $ap['iata_code'] : '--';
        $country = isset($ap['country_code']) ? $ap['country_code'] : '';
        $city = isset($ap['municipality']) ? $ap['municipality'] : '';
        $elev = isset($ap['elevation_ft']) ? $ap['elevation_ft'] : '--';
        $lat = isset($ap['latitude_deg']) ? round($ap['latitude_deg'], 3) : '--';
        $lon = isset($ap['longitude_deg']) ? round($ap['longitude_deg'], 3) : '--';
        $rwy_count = isset($ap['runways']) ? count($ap['runways']) : 0;
        $freq_count = isset($ap['freqs']) ? count($ap['freqs']) : 0;

        $country_name = isset($ap['country']['name']) ? $ap['country']['name'] : $country;

        // --- FLIGHT RADAR STYLE HEADER ---
        $header_str = $icao . (!empty($iata) ? " / " . $iata : "");
        imagestring($dst_img, 4, 10, 5, $header_str, $black);
        imagestring($dst_img, 2, 260, 7, "[" . substr($country, 0, 3) . "]", $gray);
        imageline($dst_img, 0, 22, 298, 22, $gray);

        // Name und Ort (Groß / Wichtig)
        $short_name = truncate_text($name, 28);
        imagestring($dst_img, 4, 10, 28, $short_name, $black);
        $ort_str = ($city ? $city . ", " : "") . $country_name;
        imagestring($dst_img, 3, 10, 48, truncate_text($ort_str, 35), $dark);

        // Suble separator line
        imageline($dst_img, 10, 68, 288, 68, $gray_light);

        // --- INFO GRID ---
        // Spalte 1
        $col1_x = 10;
        imagestring($dst_img, 2, $col1_x, 75, "ELEVATION", $gray);
        imagestring($dst_img, 3, $col1_x, 85, $elev . " ft", $black);

        imagestring($dst_img, 2, $col1_x, 105, "LATITUDE", $gray);
        imagestring($dst_img, 3, $col1_x, 115, $lat, $black);

        // Spalte 2
        $col2_x = 110;
        imagestring($dst_img, 2, $col2_x, 75, "RUNWAYS", $gray);
        imagestring($dst_img, 3, $col2_x, 85, $rwy_count, $black);

        imagestring($dst_img, 2, $col2_x, 105, "LONGITUDE", $gray);
        imagestring($dst_img, 3, $col2_x, 115, $lon, $black);

        // Column 3
        $col3_x = 210;
        imagestring($dst_img, 2, $col3_x, 75, "FREQUENCIES", $gray);
        imagestring($dst_img, 3, $col3_x, 85, $freq_count, $black);

        imagestring($dst_img, 2, $col3_x, 105, "NAVAIDS", $gray);
        $navaids_cnt = isset($ap['navaids']) ? count($ap['navaids']) : 0;
        imagestring($dst_img, 3, $col3_x, 115, $navaids_cnt, $black);
    }

    imagefilter($dst_img, IMG_FILTER_GRAYSCALE);
    imagetruecolortopalette($dst_img, false, 4);
    header("Content-Type: image/png");
    imagepng($dst_img);
    imagedestroy($dst_img);
    exit;
}

// --- BILD-RENDERER: Airport Map ---
if ($action === 'render_airport_map') {
    $icao = isset($_GET['icao']) ? strtoupper(trim($_GET['icao'])) : '';
    $z = isset($_GET['z']) ? intval($_GET['z']) : 13;
    $ap = get_airport_data($icao);

    $dst_img = imagecreatetruecolor(298, 144);
    $white = imagecolorallocate($dst_img, 255, 255, 255);
    $black = imagecolorallocate($dst_img, 0, 0, 0);
    $gray = imagecolorallocate($dst_img, 120, 120, 120);

    imagefill($dst_img, 0, 0, $white);

    if (!$ap || !isset($ap['latitude_deg']) || !isset($ap['longitude_deg'])) {
        imagestring($dst_img, 4, 10, 60, "No coordinates found", $black);
    } else {
        $centerLat = (float) $ap['latitude_deg'];
        $centerLon = (float) $ap['longitude_deg'];
        $iata = isset($ap['iata_code']) ? $ap['iata_code'] : '';

        // Calculate Tile Center
        $centerX = lonToX($centerLon, $z) * 256;
        $centerY = latToY($centerLat, $z) * 256;

        $minTileX = floor(($centerX - 149) / 256);
        $maxTileX = floor(($centerX + 149) / 256);
        $minTileY = floor(($centerY - 72) / 256);
        $maxTileY = floor(($centerY + 72) / 256);

        $map_w = ($maxTileX - $minTileX + 1) * 256;
        $map_h = ($maxTileY - $minTileY + 1) * 256;
        $map_img = imagecreatetruecolor($map_w, $map_h);
        imagefill($map_img, 0, 0, imagecolorallocate($map_img, 240, 240, 240));

        for ($ty = $minTileY; $ty <= $maxTileY; $ty++) {
            for ($tx = $minTileX; $tx <= $maxTileX; $tx++) {
                $url = "https://tile.openstreetmap.org/$z/$tx/$ty.png";
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, 'AviationTools/1.0');
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                $tile_data = curl_exec($ch);
                curl_close($ch);
                if ($tile_data) {
                    $tile = @imagecreatefromstring($tile_data);
                    if ($tile) {
                        imagecopy($map_img, $tile, ($tx - $minTileX) * 256, ($ty - $minTileY) * 256, 0, 0, 256, 256);
                        imagedestroy($tile);
                    }
                }
            }
        }

        // Draw crosshair for airport
        $px = $centerX - ($minTileX * 256);
        $py = $centerY - ($minTileY * 256);

        $thickColor = imagecolorallocate($map_img, 0, 0, 0);
        $crossWhite = imagecolorallocate($map_img, 255, 255, 255);
        $ringColor = imagecolorallocate($map_img, 100, 100, 100);

        // --- RADIUS RINGS ---
        $meters_per_pixel = 156543.03392 * cos(deg2rad($centerLat)) / pow(2, $z);
        $px_per_km = 1000 / $meters_per_pixel;
        $rings = [5, 10, 25, 50, 75, 100, 200, 300, 400, 500, 600, 700, 800, 900, 1000];
        foreach ($rings as $r_km) {
            $r_px = $r_km * $px_per_km;
            if ($r_px > 10 && $r_px < 600) {
                imageellipse($map_img, $px, $py, $r_px * 2, $r_px * 2, $ringColor);
                $lw = strlen($r_km . "km") * 5;
                imagefilledrectangle($map_img, $px - ($lw / 2) - 1, $py - $r_px - 8, $px + ($lw / 2) + 1, $py - $r_px, $crossWhite);
                imagestring($map_img, 1, $px - ($lw / 2), $py - $r_px - 8, $r_km . "km", $thickColor);
            }
        }

        imagefilledellipse($map_img, $px, $py, 8, 8, $thickColor);
        imagefilledellipse($map_img, $px, $py, 4, 4, $crossWhite);

        // --- LIVE TRAFFIC RADAR (zoom-adaptive range) ---
        if ($z <= 7) {
            $degRange = 5.0;
        } elseif ($z <= 9) {
            $degRange = 2.0;
        } elseif ($z <= 11) {
            $degRange = 1.0;
        } else {
            $degRange = 0.5;
        }
        $flights = get_adsblol_traffic_bbox($centerLat - $degRange, $centerLat + $degRange, $centerLon - $degRange, $centerLon + $degRange);
        if ($flights) {
            foreach ($flights as $f) {
                $fx = lonToX($f['lon'], $z) * 256 - ($minTileX * 256);
                $fy = latToY($f['lat'], $z) * 256 - ($minTileY * 256);

                // Draw only if within our rendered map tiles
                if ($fx > -30 && $fy > -30 && $fx < $map_w + 30 && $fy < $map_h + 30) {
                    drawAirplaneIcon($map_img, $fx, $fy, $f['hdg'], $thickColor, $crossWhite);
                    if ($f['callsign']) {
                        $cw = strlen($f['callsign']) * 5;
                        imagefilledrectangle($map_img, $fx + 6, $fy + 6, $fx + 6 + $cw + 2, $fy + 6 + 9, $crossWhite);
                        imagerectangle($map_img, $fx + 6, $fy + 6, $fx + 6 + $cw + 2, $fy + 6 + 9, $thickColor);
                        imagestring($map_img, 1, $fx + 7, $fy + 7, $f['callsign'], $thickColor);
                    }
                }
            }
        }

        // Viewport ausschneiden
        $localCenterX = $centerX - ($minTileX * 256);
        $localCenterY = $centerY - ($minTileY * 256);
        imagecopy($dst_img, $map_img, 0, 0, $localCenterX - 149, $localCenterY - 72, 298, 144);
        imagedestroy($map_img);

        // --- FLIGHT RADAR STYLE HEADER ---
        imagefilledrectangle($dst_img, 0, 0, 298, 22, $white); // clear top area behind text
        imagestring($dst_img, 4, 10, 5, "MAP: " . $icao . (!empty($iata) ? " / " . $iata : ""), $black);
        imagestring($dst_img, 2, 240, 7, "[Z=" . $z . "]", $gray);
        imageline($dst_img, 0, 23, 298, 23, $gray);
    }

    imagefilter($dst_img, IMG_FILTER_GRAYSCALE);
    imagetruecolortopalette($dst_img, false, 4);
    header("Content-Type: image/png");
    imagepng($dst_img);
    imagedestroy($dst_img);
    exit;
}

// --- BILD-RENDERER: Home Map (Umkreis Radar) ---
if ($action === 'render_home_map') {
    $z = isset($_GET['z']) ? intval($_GET['z']) : 10;

    $dst_img = imagecreatetruecolor(298, 144);
    $white = imagecolorallocate($dst_img, 255, 255, 255);
    $black = imagecolorallocate($dst_img, 0, 0, 0);
    $gray = imagecolorallocate($dst_img, 120, 120, 120);

    imagefill($dst_img, 0, 0, $white);

    $centerLat = (float) HOME_LAT;
    $centerLon = (float) HOME_LON;

    // Berechne Tile Center
    $centerX = lonToX($centerLon, $z) * 256;
    $centerY = latToY($centerLat, $z) * 256;

    $minTileX = floor(($centerX - 149) / 256);
    $maxTileX = floor(($centerX + 149) / 256);
    $minTileY = floor(($centerY - 72) / 256);
    $maxTileY = floor(($centerY + 72) / 256);

    $map_w = ($maxTileX - $minTileX + 1) * 256;
    $map_h = ($maxTileY - $minTileY + 1) * 256;
    $map_img = imagecreatetruecolor($map_w, $map_h);
    imagefill($map_img, 0, 0, imagecolorallocate($map_img, 240, 240, 240));

    for ($ty = $minTileY; $ty <= $maxTileY; $ty++) {
        for ($tx = $minTileX; $tx <= $maxTileX; $tx++) {
            $url = "https://tile.openstreetmap.org/$z/$tx/$ty.png";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'AviationTools/1.0');
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            $tile_data = curl_exec($ch);
            curl_close($ch);
            if ($tile_data) {
                $tile = @imagecreatefromstring($tile_data);
                if ($tile) {
                    imagecopy($map_img, $tile, ($tx - $minTileX) * 256, ($ty - $minTileY) * 256, 0, 0, 256, 256);
                    imagedestroy($tile);
                }
            }
        }
    }

    // Draw crosshair for home
    $px = $centerX - ($minTileX * 256);
    $py = $centerY - ($minTileY * 256);

    $thickColor = imagecolorallocate($map_img, 0, 0, 0);
    $crossWhite = imagecolorallocate($map_img, 255, 255, 255);
    $ringColor = imagecolorallocate($map_img, 100, 100, 100);

    // --- RADIUS RINGS ---
    $meters_per_pixel = 156543.03392 * cos(deg2rad($centerLat)) / pow(2, $z);
    $px_per_km = 1000 / $meters_per_pixel;
    $rings = [5, 10, 25, 50, 75, 100, 200, 300, 400, 500, 600, 700, 800, 900, 1000];
    foreach ($rings as $r_km) {
        $r_px = $r_km * $px_per_km;
        if ($r_px > 10 && $r_px < 600) {
            imageellipse($map_img, $px, $py, $r_px * 2, $r_px * 2, $ringColor);
            $lw = strlen($r_km . "km") * 5;
            imagefilledrectangle($map_img, $px - ($lw / 2) - 1, $py - $r_px - 8, $px + ($lw / 2) + 1, $py - $r_px, $crossWhite);
            imagestring($map_img, 1, $px - ($lw / 2), $py - $r_px - 8, $r_km . "km", $thickColor);
        }
    }

    imagefilledellipse($map_img, $px, $py, 8, 8, $thickColor);
    imagefilledellipse($map_img, $px, $py, 4, 4, $crossWhite);

    // --- LIVE TRAFFIC RADAR (zoom-adaptive range) ---
    if ($z <= 7) {
        $degRange = 5.0;
    } elseif ($z <= 9) {
        $degRange = 2.0;
    } elseif ($z <= 11) {
        $degRange = 1.0;
    } else {
        $degRange = 0.8;
    }
    $flights = get_adsblol_traffic_bbox($centerLat - $degRange, $centerLat + $degRange, $centerLon - $degRange, $centerLon + $degRange);
    if ($flights) {
        foreach ($flights as $f) {
            $fx = lonToX($f['lon'], $z) * 256 - ($minTileX * 256);
            $fy = latToY($f['lat'], $z) * 256 - ($minTileY * 256);

            if ($fx > -30 && $fy > -30 && $fx < $map_w + 30 && $fy < $map_h + 30) {
                drawAirplaneIcon($map_img, $fx, $fy, $f['hdg'], $thickColor, $crossWhite);
                if ($f['callsign']) {
                    $cw = strlen($f['callsign']) * 5;
                    imagefilledrectangle($map_img, $fx + 6, $fy + 6, $fx + 6 + $cw + 2, $fy + 6 + 9, $crossWhite);
                    imagerectangle($map_img, $fx + 6, $fy + 6, $fx + 6 + $cw + 2, $fy + 6 + 9, $thickColor);
                    imagestring($map_img, 1, $fx + 7, $fy + 7, $f['callsign'], $thickColor);
                }
            }
        }
    }

    // Viewport crop
    $localCenterX = $centerX - ($minTileX * 256);
    $localCenterY = $centerY - ($minTileY * 256);
    imagecopy($dst_img, $map_img, 0, 0, $localCenterX - 149, $localCenterY - 72, 298, 144);
    imagedestroy($map_img);

    // --- FLIGHT RADAR STYLE HEADER ---
    imagefilledrectangle($dst_img, 0, 0, 298, 22, $white);
    imagestring($dst_img, 4, 10, 5, "RADAR: HOME", $black);
    imagestring($dst_img, 2, 240, 7, "[Z=" . $z . "]", $gray);
    imageline($dst_img, 0, 23, 298, 23, $gray);


    imagefilter($dst_img, IMG_FILTER_GRAYSCALE);
    imagetruecolortopalette($dst_img, false, 4);
    header("Content-Type: image/png");
    imagepng($dst_img);
    imagedestroy($dst_img);
    exit;
}

// --- RENDER: Airport Weather & Sun ---
if ($action === 'render_airport_weather') {
    $icao = isset($_GET['icao']) ? strtoupper(trim($_GET['icao'])) : '';
    $ap = get_airport_data($icao);

    $dst_img = imagecreatetruecolor(298, 144);
    $white = imagecolorallocate($dst_img, 255, 255, 255);
    $black = imagecolorallocate($dst_img, 0, 0, 0);
    $gray = imagecolorallocate($dst_img, 120, 120, 120);
    $dark = imagecolorallocate($dst_img, 60, 60, 60);
    $gray_light = imagecolorallocate($dst_img, 220, 220, 220);
    imagefill($dst_img, 0, 0, $white);

    // FLIGHT RADAR STYLE HEADER
    imagestring($dst_img, 4, 10, 5, $icao . " Weather & Sun", $black);
    imageline($dst_img, 0, 22, 298, 22, $gray);

    if (!$ap || !isset($ap['latitude_deg'])) {
        imagestring($dst_img, 4, 10, 60, "No geo data", $black);
    } else {
        $lat = $ap['latitude_deg'];
        $lon = $ap['longitude_deg'];
        $sun = get_sunrise_sunset($lat, $lon);

        if ($sun) {
            // Draw a stylized "Sun"
            $sun_cx = 150;
            $sun_cy = 75;
            $sun_radius = 25;
            // Horizon line
            imageline($dst_img, 30, $sun_cy + 15, 270, $sun_cy + 15, $dark);
            // "Sun" semi-circle
            imagearc($dst_img, $sun_cx, $sun_cy + 15, 60, 60, 180, 0, $black);
            // Rays
            for ($ang = 190; $ang <= 350; $ang += 20) {
                $r1 = 33;
                $r2 = 42;
                $rad = deg2rad($ang);
                $x1 = $sun_cx + cos($rad) * $r1;
                $y1 = $sun_cy + 15 + sin($rad) * $r1;
                $x2 = $sun_cx + cos($rad) * $r2;
                $y2 = $sun_cy + 15 + sin($rad) * $r2;
                imageline($dst_img, $x1, $y1, $x2, $y2, $gray);
            }

            // Text left and right of the sun
            imagestring($dst_img, 4, 30, $sun_cy - 10, $sun['sunrise'], $black);
            imagestring($dst_img, 2, 40, $sun_cy - 25, "RISE", $gray);

            imagestring($dst_img, 4, 215, $sun_cy - 10, $sun['sunset'], $black);
            imagestring($dst_img, 2, 220, $sun_cy - 25, "SET", $gray);

            // Day length bottom
            $tages_str = "Day duration: " . $sun['day_length_h'] . " h";
            $len_px = strlen($tages_str) * 7;
            imagestring($dst_img, 3, (298 - $len_px) / 2, 105, $tages_str, $black);

            // Local timezone hint
            imagestring($dst_img, 2, 10, 130, "(Times in system timezone)", $gray);
        } else {
            imagestring($dst_img, 4, 10, 50, "Polar Zone", $black);
            imagestring($dst_img, 3, 10, 70, "No clear sunrise/", $dark);
            imagestring($dst_img, 3, 10, 85, "sunset at this time.", $dark);
        }
    }

    imagefilter($dst_img, IMG_FILTER_GRAYSCALE);
    imagetruecolortopalette($dst_img, false, 4);
    header("Content-Type: image/png");
    imagepng($dst_img);
    imagedestroy($dst_img);
    exit;
}

// --- RENDER: Airport Navaids ---
if ($action === 'render_airport_navaids') {
    $icao = isset($_GET['icao']) ? strtoupper(trim($_GET['icao'])) : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
    $ap = get_airport_data($icao);

    $dst_img = imagecreatetruecolor(298, 144);
    $white = imagecolorallocate($dst_img, 255, 255, 255);
    $black = imagecolorallocate($dst_img, 0, 0, 0);
    $gray = imagecolorallocate($dst_img, 120, 120, 120);
    $dark = imagecolorallocate($dst_img, 60, 60, 60);
    imagefill($dst_img, 0, 0, $white);

    // FLIGHT RADAR STYLE HEADER
    imagestring($dst_img, 4, 10, 5, $icao . " Navaids", $black);
    imageline($dst_img, 0, 22, 298, 22, $gray);

    if (!$ap || !isset($ap['navaids']) || count($ap['navaids']) === 0) {
        imagestring($dst_img, 4, 10, 60, "No Navaid data", $black);
    } else {
        $navaids = $ap['navaids'];
        $per_page = 6;
        $total_pages = ceil(count($navaids) / $per_page);
        if ($page >= $total_pages)
            $page = $total_pages - 1;
        if ($page < 0)
            $page = 0;

        $slice = array_slice($navaids, $page * $per_page, $per_page);

        imagestring($dst_img, 2, 250, 7, "[" . ($page + 1) . "/" . $total_pages . "]", $gray);

        // Header Table
        imagestring($dst_img, 2, 5, 26, "ID", $gray);
        imagestring($dst_img, 2, 40, 26, "TYPE", $gray);
        imagestring($dst_img, 2, 85, 26, "FREQ", $gray);
        imagestring($dst_img, 2, 145, 26, "NAME", $gray);
        imageline($dst_img, 5, 38, 298, 38, $gray);

        $y = 42;
        foreach ($slice as $nav) {
            $id = isset($nav['ident']) ? $nav['ident'] : '';
            $type = isset($nav['type']) ? $nav['type'] : '';
            $freq = isset($nav['frequency_khz']) ? $nav['frequency_khz'] : '';
            if ($freq >= 10000) {
                // VOR/DME etc. sind in kHz oft zB 114200 -> 114.2 MHz
                $freq_str = number_format($freq / 1000, 2, '.', '') . " M";
            } else {
                $freq_str = $freq . " k"; // NDB in kHz
            }
            $name = isset($nav['name']) ? $nav['name'] : '';

            imagestring($dst_img, 3, 5, $y, str_pad(substr($id, 0, 4), 4), $black);
            imagestring($dst_img, 3, 40, $y, str_pad(substr($type, 0, 6), 6), $dark);
            imagestring($dst_img, 3, 85, $y, $freq_str, $black);
            imagestring($dst_img, 2, 145, $y + 1, substr($name, 0, 24), $gray);

            $y += 16;
            if ($y > 125)
                break;
        }
    }

    imagefilter($dst_img, IMG_FILTER_GRAYSCALE);
    imagetruecolortopalette($dst_img, false, 4);
    header("Content-Type: image/png");
    imagepng($dst_img);
    imagedestroy($dst_img);
    exit;
}

// --- RENDER: Runways ---
if ($action === 'render_airport_runways') {
    $icao = isset($_GET['icao']) ? strtoupper(trim($_GET['icao'])) : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
    $ap = get_airport_data($icao);

    $dst_img = imagecreatetruecolor(298, 144);
    $white = imagecolorallocate($dst_img, 255, 255, 255);
    $black = imagecolorallocate($dst_img, 0, 0, 0);
    $gray = imagecolorallocate($dst_img, 120, 120, 120);
    $dark = imagecolorallocate($dst_img, 60, 60, 60);
    $gray_light = imagecolorallocate($dst_img, 200, 200, 200);
    imagefill($dst_img, 0, 0, $white);

    if (!$ap || !isset($ap['runways']) || count($ap['runways']) === 0) {
        imagestring($dst_img, 4, 10, 60, "No runway data", $black);
    } else {
        $runways = $ap['runways'];
        $total = count($runways);
        if ($page >= $total)
            $page = $total - 1;
        if ($page < 0)
            $page = 0;
        $rwy = $runways[$page];

        $le = isset($rwy['le_ident']) ? $rwy['le_ident'] : '??';
        $he = isset($rwy['he_ident']) ? $rwy['he_ident'] : '??';
        $length_ft = isset($rwy['length_ft']) ? intval($rwy['length_ft']) : 0;
        $width_ft = isset($rwy['width_ft']) ? intval($rwy['width_ft']) : 0;
        $surface = isset($rwy['surface']) ? $rwy['surface'] : 'N/A';
        $lighted = isset($rwy['lighted']) ? ($rwy['lighted'] ? 'Yes' : 'No') : 'N/A';
        $le_hdg = isset($rwy['le_heading_degT']) ? round($rwy['le_heading_degT']) : '--';
        $he_hdg = isset($rwy['he_heading_degT']) ? round($rwy['he_heading_degT']) : '--';

        $length_m = round($length_ft * 0.3048);
        $width_m = round($width_ft * 0.3048);
        $surf_map = ['ASP' => 'Asphalt', 'CON' => 'Concrete', 'GRS' => 'Grass', 'GRE' => 'Gravel'];
        $surface_nice = isset($surf_map[$surface]) ? $surf_map[$surface] : $surface;

        // FLIGHT RADAR STYLE HEADER
        imagestring($dst_img, 4, 10, 5, "RWY " . $le . " / " . $he . " (" . $icao . ")", $black);
        imagestring($dst_img, 2, 250, 7, "[" . ($page + 1) . "/" . $total . "]", $gray);
        imageline($dst_img, 0, 22, 298, 22, $gray);

        // --- STYLIZED RUNWAY GRAPHIC ---
        // Runway is always drawn horizontally, with high quality text.
        $rwy_y = 35;
        $rwy_h = 24;
        imagefilledrectangle($dst_img, 30, $rwy_y, 268, $rwy_y + $rwy_h, $dark);
        // Sidelines
        imageline($dst_img, 30, $rwy_y, 268, $rwy_y, $black);
        imageline($dst_img, 30, $rwy_y + $rwy_h, 268, $rwy_y + $rwy_h, $black);
        // Centerline
        for ($i = 45; $i < 255; $i += 16) {
            imageline($dst_img, $i, $rwy_y + ($rwy_h / 2), $i + 8, $rwy_y + ($rwy_h / 2), $white);
        }
        // Thresholds
        for ($s = $rwy_y + 4; $s < $rwy_y + $rwy_h - 2; $s += 4) {
            imageline($dst_img, 36, $s, 36, $s + 2, $white);
            imageline($dst_img, 262, $s, 262, $s + 2, $white);
        }

        // Threshold Designator
        imagestring($dst_img, 2, 8, $rwy_y + 5, str_pad($le, 3, " ", STR_PAD_RIGHT), $black);
        imagestring($dst_img, 2, 273, $rwy_y + 5, str_pad($he, 3, " ", STR_PAD_LEFT), $black);

        // Heading arrows (virtual)
        imagestring($dst_img, 2, 45, $rwy_y + 5, str_pad($le_hdg, 3, "0", STR_PAD_LEFT) . "*", $white);
        imagestring($dst_img, 2, 235, $rwy_y + 5, str_pad($he_hdg, 3, "0", STR_PAD_LEFT) . "*", $white);

        // --- TECHNICAL DATA TABLE ---
        $y = $rwy_y + $rwy_h + 10;

        // Left box
        imagerectangle($dst_img, 8, $y, 145, 138, $gray_light);
        imagestring($dst_img, 2, 12, $y + 4, "DIMENSIONS:", $gray);
        imagestring($dst_img, 3, 12, $y + 16, "L: " . $length_m . "m (" . $length_ft . "ft)", $black);
        imagestring($dst_img, 3, 12, $y + 31, "W: " . $width_m . "m (" . $width_ft . "ft)", $black);
        imagestring($dst_img, 3, 12, $y + 46, "S: " . $surface_nice, $dark);

        // Right box
        imagerectangle($dst_img, 150, $y, 290, 138, $gray_light);
        imagestring($dst_img, 2, 154, $y + 4, "EQUIPMENT:", $gray);
        imagestring($dst_img, 3, 154, $y + 16, "Light: " . $lighted, $black);

        $le_ils_bool = isset($rwy['le_ils']);
        $he_ils_bool = isset($rwy['he_ils']);

        $ils_str = '';
        if ($le_ils_bool && $he_ils_bool)
            $ils_str = $le . " & " . $he;
        elseif ($le_ils_bool)
            $ils_str = $le . " ONLY";
        elseif ($he_ils_bool)
            $ils_str = $he . " ONLY";
        else
            $ils_str = "N/A";

        imagestring($dst_img, 3, 154, $y + 31, "ILS:", $dark);
        imagestring($dst_img, 3, 185, $y + 31, $ils_str, $black);
    }

    imagefilter($dst_img, IMG_FILTER_GRAYSCALE);
    imagetruecolortopalette($dst_img, false, 4);
    header("Content-Type: image/png");
    imagepng($dst_img);
    imagedestroy($dst_img);
    exit;
}

// --- RENDER: Frequencies ---
if ($action === 'render_airport_freqs') {
    $icao = isset($_GET['icao']) ? strtoupper(trim($_GET['icao'])) : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
    $ap = get_airport_data($icao);

    $dst_img = imagecreatetruecolor(298, 144);
    $white = imagecolorallocate($dst_img, 255, 255, 255);
    $black = imagecolorallocate($dst_img, 0, 0, 0);
    $gray = imagecolorallocate($dst_img, 120, 120, 120);
    $dark = imagecolorallocate($dst_img, 60, 60, 60);
    $gray_light = imagecolorallocate($dst_img, 220, 220, 220);
    imagefill($dst_img, 0, 0, $white);

    if (!$ap || !isset($ap['freqs']) || count($ap['freqs']) === 0) {
        imagestring($dst_img, 4, 10, 60, "No frequency data", $black);
    } else {
        $freqs = $ap['freqs'];
        $per_page = 6;
        $total_pages = ceil(count($freqs) / $per_page);
        if ($page >= $total_pages)
            $page = $total_pages - 1;
        if ($page < 0)
            $page = 0;

        $slice = array_slice($freqs, $page * $per_page, $per_page);

        // FLIGHT RADAR STYLE HEADER
        imagestring($dst_img, 4, 10, 5, $icao . " COMM Freqs", $black);
        imagestring($dst_img, 2, 250, 7, "[" . ($page + 1) . "/" . $total_pages . "]", $gray);
        imageline($dst_img, 0, 22, 298, 22, $gray);

        // Column Headers
        imagestring($dst_img, 2, 5, 26, "TYPE", $gray);
        imagestring($dst_img, 2, 45, 26, "FREQ (MHz)", $gray);
        imagestring($dst_img, 2, 125, 26, "DESCRIPTION", $gray);
        imageline($dst_img, 5, 38, 293, 38, $gray);

        $y = 42;
        foreach ($slice as $freq) {
            $type = isset($freq['type']) ? $freq['type'] : '';
            $mhz = isset($freq['frequency_mhz']) ? $freq['frequency_mhz'] : '';
            $desc = isset($freq['description']) ? $freq['description'] : '';

            imagestring($dst_img, 3, 5, $y, str_pad(substr($type, 0, 5), 5), $dark);
            imagestring($dst_img, 3, 45, $y, $mhz, $black);
            imagestring($dst_img, 2, 125, $y + 1, substr($desc, 0, 25), $gray);

            // Dotted line separator for premium look
            for ($x = 5; $x < 293; $x += 6) {
                imagesetpixel($dst_img, $x, $y + 14, $gray_light);
            }

            $y += 16;
            if ($y > 125)
                break;
        }
    }

    imagefilter($dst_img, IMG_FILTER_GRAYSCALE);
    imagetruecolortopalette($dst_img, false, 4);
    header("Content-Type: image/png");
    imagepng($dst_img);
    imagedestroy($dst_img);
    exit;
}

// --- RENDER: Departure/Arrival Board ---
if ($action === 'render_airport_board') {
    $icao = isset($_GET['icao']) ? strtoupper(trim($_GET['icao'])) : '';
    $type = isset($_GET['type']) ? $_GET['type'] : 'dep';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 0;

    $ap = get_airport_data($icao);
    $iata = ($ap && isset($ap['iata_code'])) ? $ap['iata_code'] : '';

    $dst_img = imagecreatetruecolor(298, 144);
    $white = imagecolorallocate($dst_img, 255, 255, 255);
    $black = imagecolorallocate($dst_img, 0, 0, 0);
    $gray = imagecolorallocate($dst_img, 120, 120, 120);
    $dark = imagecolorallocate($dst_img, 60, 60, 60);
    $gray_light = imagecolorallocate($dst_img, 220, 220, 220);
    imagefill($dst_img, 0, 0, $white);

    $label = ($type === 'arr') ? 'ARRIVALS' : 'DEPARTURES';

    // FLIGHT RADAR STYLE HEADER
    imagestring($dst_img, 4, 10, 5, $icao . " " . $label, $black);
    imageline($dst_img, 0, 22, 298, 22, $gray);

    if (!$iata || $iata === '') {
        imagestring($dst_img, 3, 10, 60, "No IATA code available", $black);
    } else if (!AVIATIONSTACK_KEY || AVIATIONSTACK_KEY === '') {
        imagestring($dst_img, 3, 10, 30, "AviationStack API-Key missing!", $black);
        imagestring($dst_img, 2, 10, 50, "Get it for free: aviationstack.com", $gray);
    } else {
        $flights = get_airport_board($iata, $type);
        if (!$flights || count($flights) === 0) {
            imagestring($dst_img, 3, 10, 60, "No flights found", $black);
        } else {
            $per_page = 5;
            $total_pages = ceil(count($flights) / $per_page);
            if ($page >= $total_pages)
                $page = $total_pages - 1;
            if ($page < 0)
                $page = 0;
            $slice = array_slice($flights, $page * $per_page, $per_page);

            imagestring($dst_img, 2, 250, 7, "[" . ($page + 1) . "/" . $total_pages . "]", $gray);

            // Column Headers (FIDS Style)
            imagestring($dst_img, 2, 5, 26, "FLIGHT", $gray);
            imagestring($dst_img, 2, 55, 26, ($type === 'arr' ? "FROM" : "TO"), $gray);
            imagestring($dst_img, 2, 100, 26, "TIME", $gray);
            imagestring($dst_img, 2, 140, 26, "STATUS", $gray);
            imageline($dst_img, 5, 38, 298, 38, $gray);

            $y = 42;
            foreach ($slice as $fl) {
                // Flight number
                $fnum = '';
                if (isset($fl['flight']['iata']))
                    $fnum = $fl['flight']['iata'];
                elseif (isset($fl['flight']['icao']))
                    $fnum = $fl['flight']['icao'];

                // Destination/Origin
                $dest = '';
                if ($type === 'arr') {
                    $dest = isset($fl['departure']['iata']) ? $fl['departure']['iata'] : '---';
                } else {
                    $dest = isset($fl['arrival']['iata']) ? $fl['arrival']['iata'] : '---';
                }

                // Time
                $time = '--:--';
                $time_raw = ($type === 'arr') ?
                    (isset($fl['arrival']['scheduled']) ? $fl['arrival']['scheduled'] : '') :
                    (isset($fl['departure']['scheduled']) ? $fl['departure']['scheduled'] : '');
                if ($time_raw) {
                    $ts = strtotime($time_raw);
                    if ($ts > 0)
                        $time = date('H:i', $ts);
                }

                $status = isset($fl['flight_status']) ? ucfirst($fl['flight_status']) : '---';
                $status_short = substr($status, 0, 10);

                // Render row
                imagestring($dst_img, 2, 5, $y, str_pad(substr($fnum, 0, 7), 7), $black);
                imagestring($dst_img, 2, 55, $y, $dest, $dark);
                imagestring($dst_img, 2, 100, $y, $time, $black);

                // Padded status for FIDS look
                imagefilledrectangle($dst_img, 138, $y - 1, 138 + (strlen($status_short) * 6) + 4, $y + 12, $black);
                imagestring($dst_img, 2, 140, $y, $status_short, $white);

                // Dotted line separator
                for ($x = 5; $x < 293; $x += 6) {
                    imagesetpixel($dst_img, $x, $y + 15, $gray_light);
                }

                $y += 18;
                if ($y > 125)
                    break;
            }
        }
    }

    imagefilter($dst_img, IMG_FILTER_GRAYSCALE);
    imagetruecolortopalette($dst_img, false, 4);
    header("Content-Type: image/png");
    imagepng($dst_img);
    imagedestroy($dst_img);
    exit;
}

header("Location: " . $server_url . '?action=menu');
exit;

