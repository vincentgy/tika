<?php
include_once "./models/user.php";
include_once "./models/geometry.php";
include_once "./models/job.php";
include_once "./models/session.php";
include_once "./models/profile.php";
require_once __DIR__ ."/config.php";

$response = array();
$data = json_decode(file_get_contents('php://input'), true);
error_log(print_r($data, true));
$req = $data["param"];

function get_client_ip_server() {
    $ipaddress = '';
    if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    }
    else if(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else if(array_key_exists('HTTP_X_FORWARDED', $_SERVER)) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    }
    else if(array_key_exists('HTTP_FORWARDED_FOR', $_SERVER)) {
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    }
    else if(array_key_exists('HTTP_FORWARDED', $_SERVER)) {
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    }
    else if(array_key_exists('REMOTE_ADDR', $_SERVER)) {
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    }
    else {
        $ipaddress = 'UNKNOWN';
    }

    return $ipaddress;
}

$user_id = false;

if(isset($req['a'])) {

    if ($req['a'] !== 'ul' && $req['a'] !== 'ur') {
        if (isset($req['token'])) {
            $user_id  = SESSION::getuseridbytoken($link, $req['token']);
            SESSION::updatetoken($link, $req['token']);
        }
    }
    switch ($req['a']) {
        case 'ul':// user login
            if ($r = USER::checkuser($link, $req['e'], $req['p']) !== false) {
                $token = SESSION::addtoken($link, $req['e'], get_client_ip_server());
                $response['ret'] = 0;
                $response['token'] = $token;
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

                $r = JOB::addjob($link, $req['title'], $req['company'], $req['description'], $user_id, $req['type'], $req['pay_type'], $req['minimum_pay'], $req['maximum_pay'], $req['number'], $req['region_id'], $req['district_id'], $req['location'], $req['categories']);
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

                $r = JOB::searchjobs($link, $req['query'], $req['location']);
                if ($r !== false) {
                    $response['ret'] = 0;
                    $response['data'] = $r;
                }
                else {
                    $response['ret'] = 1;
                }
            }
        break;
        case 'gpl':// search job
            $u = $user_id;
            if (isset($req['user_id']) !== false) {
                $u = $req['user_id'];
            }

            $r = JOB::searchjobsbyuser($link, $u);
            if ($r !== false) {
                $response['ret'] = 0;
                $response['data'] = $r;
            }
            else {
                $response['ret'] = 1;
            }
        break;
        case 'ap':// apply position
            if (isset($req['p']) === false ||
                isset($req['u']) === false
                ) {
                $response['ret'] = -2;
            }
            else if (APPLICATION::checkapplication($link, $req['u'], $req['p']) === true) {
                $response['ret'] = 1;
            } else {
                $r = APPLICATION::addapplication($link, $req['u'], $req['p']);
                if ($r !== false) {
                    $response['ret'] = 0;
                }
                else {
                    $response['ret'] = 1;
                }
            }
        break;
        case 'up':// update profile
            if ($user_id === false) {
                $response['ret'] = -2;
            }

            $r = PROFILE::update($link, $user_id, $req['description'], $req['phone'], $req['skills'],  $req['qualifications'], $req['experiences']);
            if ($r !== false) {
                $response['ret'] = 0;
            }
            else {
                $response['ret'] = 1;
            }
        case 'gp':// get profile
            if ($user_id === false) {
                $response['ret'] = -2;
            }

            $r = PROFILE::get($link, $user_id);
            if ($r !== false) {
                $response['ret'] = 0;
                $response['data'] = $r;
            }
            else {
                $response['ret'] = 1;
            }
        break;
        case 'aq':// update qualification
            if (isset($req['degree']) === false ||
                isset($req['school']) === false ||
                isset($req['major']) === false ||
                isset($req['start']) === false ||
                isset($req['end']) === false) {
                $response['ret'] = -2;
            }

            $r = PROFILE::addqualification($link, $user_id, $req['degree'], $req['school'], $req['major'], $req['start'], $req['end']);
            if ($r !== false) {
                $response['ret'] = 0;
            }
            else {
                $response['ret'] = 1;
            }
        break;
        case 'uq':// update qualification
            if (isset($req['id']) === false ||
                isset($req['degree']) === false ||
                isset($req['school']) === false ||
                isset($req['major']) === false ||
                isset($req['start']) === false ||
                isset($req['end']) === false) {
                $response['ret'] = -2;
            }

            $r = PROFILE::updatequalification($link, $req['id'], $req['degree'], $req['school'], $req['major'], $req['start'], $req['end']);
            if ($r !== false) {
                $response['ret'] = 0;
            }
            else {
                $response['ret'] = 1;
            }
        break;
        case 'ae':// update experience
            if (isset($req['place']) === false ||
                isset($req['title']) === false ||
                isset($req['task']) === false ||
                isset($req['start']) === false ||
                isset($req['end']) === false) {
                $response['ret'] = -2;
            }

            $r = PROFILE::addexperience($link, $user_id, $req['place'], $req['title'], $req['task'], $req['start'], $req['end']);
            if ($r !== false) {
                $response['ret'] = 0;
            }
            else {
                $response['ret'] = 1;
            }
        break;
        case 'ue':// update experience
            if (isset($req['id']) === false ||
                isset($req['place']) === false ||
                isset($req['title']) === false ||
                isset($req['task']) === false ||
                isset($req['start']) === false ||
                isset($req['end']) === false) {
                $response['ret'] = -2;
            }

            $r = PROFILE::updateexperience($link, $req['id'], $req['place'], $req['title'], $req['task'], $req['start'], $req['end']);
            if ($r !== false) {
                $response['ret'] = 0;
            }
            else {
                $response['ret'] = 1;
            }
        break;
        case 'gal':// get application list
            if (isset($req['p']) === false) {
                $response['ret'] = -2;
            }

            $r = APPLICATION::updateexperience($link, $req['p']);
            if ($r !== false) {
                $response['ret'] = 0;
                $response['data'] = $r;
            }
            else {
                $response['ret'] = 1;
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
