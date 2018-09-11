<?php

require __DIR__.'/vendor/autoload.php';

use Guzzle\Http\Client;

$client = new Client('http://localhost:8000', [
    'request.options' => [
        'exceptions' => true,
    ]
]);

/*----------------------------------------------------------*/

$nickname = 'ObjectOrienter'.rand(0, 999);
$data = [
    'nickname' => $nickname,
    'avatarNumber' => 5,
    'powerLevel' => '0',
    'tagLine' => 'a test dev!'
];

$request = $client->post('/api/programmers',
    ['Authorization' => 'token ABCDEF'],
    json_encode($data)
);
$response = $request->send();

$programmerUrl = $response->getHeader('Location');
$request = $client->get($programmerUrl);
$response = $request->send();

echo $response;
echo "\n\n";