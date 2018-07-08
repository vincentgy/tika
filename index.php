<?php
include_once "./models/user.php";
include_once "./models/geometry.php";
include_once "./models/job.php";

$req = json_decode($_POST["param"], true);

$response = array();
if(isset($req['a'])) {
    switch ($req['a']) {
        case 'ul':// user login
            if (USER::checkuser($req['e'], $req['p']) === true) {
                $response['ret'] = '0';
            }
            else {
                $response['ret'] = '1';
            }
        break;
        case 'ur':// user register
            if (USER::adduser($req['e'], $req['p'], $req['n']) === true) {
                $response['ret'] = '0';
            }
            else {
                $response['ret'] = '1';
            }
        break;
        case 'jl':// job list
            $categories = JOB::getcategories();
            if ($address !== false) {
                $response['ret'] = '0';
                $response['data'] = json_encode($categories);
            }
            else {
                $response['ret'] = '1';
            }
        break;
        case 'cl':// current location
            $address = Geometry::covertToAddress($req['lat'], $req['lng']);
            if ($address !== false) {
                $response['ret'] = '0';
                $response['data'] = json_encode($address);
            }
            else {
                $response['ret'] = '1';
            }
        break;
    default:
   }
}
else {
}

echo json_encode($response);
?>
