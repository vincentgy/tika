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

    static function getregionbyId($id) {
        $sql = "SELECT * FROM regions WHERE id = ?";
        $rows = [];
        require_once __DIR__ ."/../config.php";
        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $id);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                while ($row=mysqli_fetch_assoc($result)) {
                    $rows[] = $row;
                }
                // Free result set
                mysqli_free_result($result);
            }
        }
        mysqli_close($link);
        $region = false;
        if (count($rows) === 1) {
            $region = $rows[0]['name'];
        }
        return $region;
    }

    static function getdistrictbyId($id) {
        $sql = "SELECT * FROM districts WHERE id = ?";
        $rows = [];
        require_once __DIR__ ."/../config.php";
        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $id);
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                while ($row=mysqli_fetch_assoc($result)) {
                    $rows[] = $row;
                }
                // Free result set
                mysqli_free_result($result);
            }
        }
        mysqli_close($link);
        $district = false;
        if (count($rows) === 1) {
            $region = $rows[0]['name'];
        }
        return $district;
    }

    static function getcountry($country_code) {
        $c = false;
        switch ($country_code) {
            case 'NZ':
                $c = 'New Zealand';
            break;

        }
        return $c;
    }

    static function getaddress($location, $district_id, $region_id, $country_code = 'NZ') {

        return $location . ',' . Address::getdistrictbyId($district_id) . ',' . Address::getregionbyId($region_id) . ',' . Address::getcountry($country_code);
    }
}

?>

