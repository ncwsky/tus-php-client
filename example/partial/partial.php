<?php

require __DIR__ . '/../../vendor/autoload.php';


$client = new \TusPhp\Tus\Client('http://192.168.0.219:8080/');
$client->setApiPath('/tus-php/example/files');


$client = new \TusPhp\Tus\Client('http://192.168.0.219:8040/');
$client->setApiPath('/index/files');

// Alert: Sanitize all inputs properly in production code
if ( ! empty($_FILES)) {
    $fileMeta  = $_FILES['tus_file'];

    $fileMeta = ['tmp_name'=>'C:/Users/n/Desktop/wing-binlog-2.0-pld.zip', 'name'=>'wing-binlog-2.0-pld.zip'];
    $uploadHash = hash_file('md5', $fileMeta['tmp_name'], true);
    $uploadKey = bin2hex($uploadHash);
    try {
        $client->setKey($uploadKey)->file($fileMeta['tmp_name'], 'chunk_a');

        // Upload 50MB starting from 10MB
        $bytesUploaded = $client->seek(10000000)->upload(50000000);
        $partialKey1   = $client->getKey();
        $checksum      = $client->getChecksum();

        // Upload first 10MB
        $bytesUploaded = $client->setFileName('chunk_b')->seek(0)->upload(10000000);
        $partialKey2   = $client->getKey();

        // Upload remaining bytes starting from 60,000,000 bytes (60MB => 50000000 + 10000000)
        $bytesUploaded = $client->setFileName('chunk_c')->seek(60000000)->upload();
        $partialKey3   = $client->getKey();

        $client->setFileName($fileMeta['name'])->setChecksumAlgorithm('md5')->setChecksum($uploadHash)->concat($uploadKey, $partialKey2, $partialKey1, $partialKey3);
        die();
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?state=uploaded');
    } catch (Exception $e) {
        echo $e->getFile(),':',$e->getCode(), ', ', $e->getMessage(), PHP_EOL, $e->getTraceAsString();
        die();
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?state=failed');
    }
}
