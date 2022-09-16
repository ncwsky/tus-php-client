<?php
/**
 * Uploads files in 5mb chunk.
 */

require __DIR__ . '/../../vendor/autoload.php';
header('Content-Type: text/html;charset=utf-8');
use TusPhp\Exception\TusException;
use TusPhp\Exception\FileException;

function is_utf8($word){
    $regx = '(['.chr(228).'-'.chr(233).']{1}['.chr(128).'-'.chr(191).']{1}['.chr(128).'-'.chr(191).']{1})';
    if (preg_match('/^'.$regx.'{1}/',$word) == true || preg_match('/'.$regx.'{1}$/',$word) == true || preg_match('/'.$regx.'{2,}/',$word) == true){
        return true;
    }else{
        return false;
    }
}

$client = new \TusPhp\Tus\Client('http://192.168.0.219:7700/');
$client->setApiPath('/index/files');
// Alert: Sanitize all inputs properly in production code
if ( ! empty($_FILES)) {
    $fileMeta  = $_FILES['tus_file'];
    $fileMeta['path'] = '';
    $key = 'tus-' . $fileMeta['name'] . '-' . (isset($fileMeta['type']) ? $fileMeta['type'] : '') . '-' . $fileMeta['size'] . '-' . (isset($fileMeta['lastModified']) ? $fileMeta['lastModified'] : '0') . '-' . $fileMeta['path'];
    $uploadKey = sha1($key);

    $hashKey = hash_file('sha1', $fileMeta['tmp_name']);

    $client->setMetadata($fileMeta);
    try {
        $client->setChecksumAlgorithm('sha1')->setChecksum($hashKey);
        $client->setKey($uploadKey)->file($fileMeta['tmp_name'], $fileMeta['name']);

        $bytesUploaded = $client->upload(5000000); // Chunk of 5 mb

        echo json_encode([
            'status' => 'uploading',
            'bytes_uploaded' => $bytesUploaded,
            'upload_key' => $uploadKey
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'bytes_uploaded' => -1,
            'upload_key' => '',
            'error' => $e->getMessage(),
            'e'=> $e->getFile().':'.$e->getLine().'###'.$e->getTraceAsString(),
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'bytes_uploaded' => -1,
        'error' => 'No input!',
    ]);
}
