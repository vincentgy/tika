<?php
include_once "./models/user.php";
include_once "./models/geometry.php";
include_once "./models/job.php";

$response = array();
$data = json_decode(file_get_contents('php://input'), true);
error_log(print_r($data, true));
$req = $data["param"];

if(isset($req['a'])) {
    switch ($req['a']) {
        case 'ul':// user login
            if (USER::checkuser($req['e'], $req['p']) === true) {
                $response['ret'] = 0;
            }
            else {
                $response['ret'] = 1;
            }
        break;
        case 'ur':// user register
            if (USER::adduser($req['e'], $req['p'], $req['n']) === true) {
                $response['ret'] = 0;
            }
            else {
                $response['ret'] = 1;
            }
        break;
        case 'jc':// job categories
            $categories = JOB::getcategories();
            if ($categories !== false) {
                $response['ret'] = 0;
                $response['data'] = json_encode($categories);
            }
            else {
                $response['ret'] = 1;
            }
        break;
        case 'cl':// current location
            $address = Geometry::covertToAddress($req['lat'], $req['lng']);
            if ($address !== false) {
                $response['ret'] = 0;
                $response['data'] = json_encode($address);
            }
            else {
                $response['ret'] = 1;
            }
        break;
    default:
        $response['ret'] = -1;
   }
}
else {
}

echo json_encode($response);
?>
