<?php
include_once "./models/user.php";
include_once "./models/geometry.php";
include_once "./models/job.php";
include_once "./models/session.php";
require_once __DIR__ ."/config.php";

$response = array();
$data = json_decode(file_get_contents('php://input'), true);
error_log(print_r($data, true));
$req = $data["param"];

function get_client_ip_server() {
    $ipaddress = '';
    if ($_SERVER['HTTP_CLIENT_IP'])
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if($_SERVER['HTTP_X_FORWARDED_FOR'])
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if($_SERVER['HTTP_X_FORWARDED'])
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if($_SERVER['HTTP_FORWARDED_FOR'])
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if($_SERVER['HTTP_FORWARDED'])
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if($_SERVER['REMOTE_ADDR'])
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';

    return $ipaddress;
}

if(isset($req['a'])) {
    switch ($req['a']) {
        case 'ul':// user login
            if ($r = USER::checkuser($link, $req['e'], $req['p']) !== false) {
                SESSION::addtoken($link, $r, get_client_ip_server());
                $response['ret'] = 0;
            }
            else {
                $response['ret'] = 1;
            }
        break;
        case 'ur':// user register
            if (isset($req['e']) === false ||
                isset($req['p']) === false ||
                isset($req['n']) === false) {
                    $response['ret'] = -2;
            }
            else {
                if (USER::adduser($link, $req['e'], $req['p'], $req['n']) === true) {
                    $response['ret'] = 0;
                }
                else {
                    $response['ret'] = 1;
                }
            }
        break;
        case 'jc':// job categories
            $categories = JOB::getcategories($link);
            if ($categories !== false) {
                $response['ret'] = 0;
                $response['data'] = $categories;
            }
            else {
                $response['ret'] = 1;
            }
        break;
        case 'jt':// job types
            $types = JOB::gettypes($link);
            if ($types !== false) {
                $response['ret'] = 0;
                $response['data'] = $types;
            }
            else {
                $response['ret'] = 1;
            }
        break;
        case 'jpt':// job pay types.
            $ptypes = JOB::getpaytypes($link);
            if ($ptypes !== false) {
                $response['ret'] = 0;
                $response['data'] = $ptypes;
            }
            else {
                $response['ret'] = 1;
            }
        break;
        case 'lr':// list regions
            $regions = isset($req['c']) ? JOB::getregionsbycountrycode($link, $req['c']) : JOB::getregionsbycountrycode($link);
            if ($regions !== false) {
                $response['ret'] = 0;
                $response['data'] = $regions;
            }
            else {
                $response['ret'] = 1;
            }
        break;
        case 'ld':// get districts by region id.
            if (isset($req['r']) === false) {
                $response['ret'] = -2;
            }
            else {
                $districts = JOB::getdistrictsbyregion($link, $req['r']);
                if ($districts !== false) {
                    $response['ret'] = 0;
                    $response['data'] = $districts;
                }
                else {
                    $response['ret'] = 1;
                }
            }
        break;
        case 'pj':// post job
            if (isset($req['title']) === false ||
                isset($req['company']) === false ||
                isset($req['user_id']) === false  ||
                isset($req['categories']) === false  ||
                isset($req['type']) === false  ||
                isset($req['pay_type']) === false ||
                isset($req['minimum_pay']) === false ||
                isset($req['region_id']) === false ||
                isset($req['district_id']) === false
                ) {
                $response['ret'] = -2;
            }
            else {

                $r = JOB::addjob($link, $req['title'], $req['company'], $req['description'], $req['user_id'], $req['type'], $req['pay_type'], $req['minimum_pay'], $req['maximum_pay'], $req['number'], $req['region_id'], $req['district_id'], $req['location'], $req['categories']);
                if ($r !== false) {
                    $response['ret'] = 0;
                }
                else {
                    $response['ret'] = 1;
                }
            }
        break;
        case 'sj':// search job
            if (isset($req['query']) === false) {
                $response['ret'] = -2;
            }
            else {

                $r = JOB::searchjobs($link, $req['query']);
                if ($r !== false) {
                    $response['ret'] = 0;
                }
                else {
                    $response['ret'] = 1;
                    $response['data'] = $r;
                }
            }
        break;
        case 'cl':// current location
            $address = Geometry::covertToAddress($req['lat'], $req['lng']);
            if ($address !== false) {
                $response['ret'] = 0;
                $response['data'] = $address;
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

header('Content-Type: application/json');
echo json_encode($response);
?>
