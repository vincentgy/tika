<?php
require_once "./config.php";
require_once "./s3_config.php";
$response = array();


$token = $_GET['token'];
$category  = $_GET['c'];
$id  = $_GET['id'];

$folder ='';
if ($category === 'u') {
	$folder = 'users/';
}
else if ($category === 'p') {
	$folder = 'positions/';
}
else if ($category === 'c') {
	$folder = 'companies/';
}

$file = $_FILES["fileToUpload"]['tmp_name'];
$ext = pathinfo($_FILES["fileToUpload"]['name'], PATHINFO_EXTENSION);
$keyName = $folder . $id . $ext;
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


$url = htmlEntities('https://'. $bucketName . '.s3.us-east-2.amazonaws.com/'. $keyName);
$sql = false;
if ($category === 'u') {
	$sql = 'UPDATE users SET avatar = ? WHERE id = ?';
}
if($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "ss", $url, $id);
    if(mysqli_stmt_execute($stmt)){
        $response['ret'] = 0;
        $response['url'] = $url;
    }
    else {
        $response['ret'] = 1;
    }
    mysqli_stmt_close($stmt);
}
else {
	$response['ret'] = 1;
    $response['error'] = mysqli_error($link);
}

header('Content-Type: application/json');
echo json_encode($response);
?>