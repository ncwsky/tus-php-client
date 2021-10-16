copy from tus-php

### Installation

Pull the package via composer.
```shell
$ composer require ankitpokhrel/tus-php-client
```

#### Client
The client can be used for creating, resuming and/or deleting uploads.

```php
$client = new \TusPhp\Tus\Client($baseUrl);

// Key is mandatory.
$key = 'your unique key';

$client->setKey($key)->file('/path/to/file', 'filename.ext');

// Create and upload a chunk of 1MB
$bytesUploaded = $client->upload(1000000);

// Resume, $bytesUploaded = 2MB
$bytesUploaded = $client->upload(1000000);

// To upload whole file, skip length param
$client->file('/path/to/file', 'filename.ext')->upload();
```

To check if the file was partially uploaded before, you can use `getOffset` method. It returns false if the upload
isn't there or invalid, returns total bytes uploaded otherwise.

```php
$offset = $client->getOffset(); // 2000000 bytes or 2MB
```

Delete partial upload from the cache.

```php
$client->delete($key);
```

By default, the client uses `/files` as an API path. You can change it with `setApiPath` method.

```php
$client->setApiPath('/api');
```

By default, the server will use `sha256` algorithm to verify the integrity of the upload. If you want to use a different hash algorithm, you can do so by
using `setChecksumAlgorithm` method. To get the list of supported hash algorithms, you can send `OPTIONS` request to the server.

```php
$client->setChecksumAlgorithm('crc32');
```

#### Third Party Client Libraries
##### [Uppy](https://uppy.io/)
Uppy is a sleek, modular file uploader plugin developed by same folks behind tus protocol.
You can use uppy to seamlessly integrate official [tus-js-client](https://github.com/tus/tus-js-client) with tus-php server.
Check out more details in [uppy docs](https://uppy.io/docs/tus/).
```js
uppy.use(Tus, {
  endpoint: 'https://tus-server.yoursite.com/files/', // use your tus endpoint here
  resume: true,
  autoRetry: true,
  retryDelays: [0, 1000, 3000, 5000]
})
```

##### [Tus-JS-Client](https://github.com/tus/tus-js-client)
Tus-php server is compatible with the official [tus-js-client](https://github.com/tus/tus-js-client) Javascript library.
```js
var upload = new tus.Upload(file, {
  endpoint: "/tus",
  retryDelays: [0, 3000, 5000, 10000, 20000],
  metadata: {
    name: file.name,
    type: file.type
  }
})
upload.start()
```

