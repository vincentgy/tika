<?php
class Location {		
    public $latitude;		
    public $longitude;		

    function __construct($lat, $lon) {		
        $this->latitude = $lat;
        $this->longitude = $lon;
    }
}

class Address {
    public $country;
    public $city;
    public $formatted_address;

    function __construct($Formatted_address) {
        $this->formatted_address = $Formatted_address;
    }
}
?>

