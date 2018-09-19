 <?php
include_once("location.php");
 
 class Geometry {
    static $google_api_key = 'AIzaSyCxmWtnp8KG0dcnKIQzKoxvqdU3JjbC4GA';
    static function covertToLocation($address) {
        // Get lat and long by address         
        $prepAddr = str_replace(' ','+',$address);
        $geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&sensor=false&key='.Geometry::$google_api_key);
        error_log(print_r($geocode, true));
        $output= json_decode($geocode);

        $latitude = $output->results[0]->geometry->location->lat;
        $longitude = $output->results[0]->geometry->location->lng;
        $location = new Location($latitude, $longitude);
        return $location;
    }

    static function covertToAddress($lat, $lng) {
        // Get lat and long by address
        $geoaddress=file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?latlng='.$lat.','.$lng.'&key='.Geometry::$google_api_key);
        $output= json_decode($geoaddress);
        if ($output->status !== "OK") {
            return false;
        }
        $formatted_address = $output->results[0]->formatted_address;
        $address_components = $output->results[0]->address_components;
        $country = '';
        $city = '';
        foreach($address_components as $com) {
            if ($com->types[0] === 'locality') {
                $city = $com->long_name;
            }
            if ($com->types[0] === 'country') {
                $country = $com->long_name;
            }
        }
        $address = new Address($formatted_address);
        $address->country = $country;
        $address->city = $city;
        return $address;
    }

    static function distance($lat1, $lon1, $lat2, $lon2) {

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $dist = $dist * 60 * 1.1515*1.609344;

        return $dist;
    }
}
?>

