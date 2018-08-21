<?php
require_once "./config.php";
require_once "./s3_config.php";
$response = array();


$token = $_GET['token'];
$category  = $_GET['c'];
$id  = $_GET['id'];
$name  = $_GET['n'];
$folder ='';
if ($category === 'u') {
	$folder = 'users/';
}
else if ($category === 'p') {
	$folder = 'positions/';
}

$keyName = $folder . $id . '_' . $name;

$file = $_FILES["fileToUpload"]['tmp_name'];

	// Add it to S3
	try {
		// Uploaded:
		$s3->putObject(
			array(
				'Bucket'=>$bucketName,
				'Key' =>  $keyName,
				'SourceFile' => $file,
				'ACL'          => 'public-read',
				'StorageClass' => 'REDUCED_REDUNDANCY'
			)
		);
	} catch (S3Exception $e) {
		$response['ret'] = 1;
		$response['error'] = $e->getMessage();
	} catch (Exception $e) {
		$response['ret'] = 1;
		$response['error'] = $e->getMessage();
	}
	// Now that you have it working, I recommend adding some checks on the files.
	// Example: Max size, allowed file types, etc.

$response['ret'] = 0;
$response['url'] = 'https://'. $bucketName . '.s3.us-east-2.amazonaws.com/'. $keyName;
header('Content-Type: application/json');
echo json_encode($response);
?>