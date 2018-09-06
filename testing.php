<?php

require __DIR__.'/vendor/autoload.php';

use Guzzle\Http\Client;

// create our http client (Guzzle)
$client = new Client('http://localhost:8000', [
    'request.options' => [
        'exceptions' => false,
    ]
]);

/*----------------------------------------------------------*/

$nickname = 'ObjectOrienter'.rand(0, 999);
$data = [
    'nickname' => $nickname,
    'avatarNumber' => 5,
    'tagLine' => 'a test dev!'
];
// 1) Create a programmer resource
$request = $client->post('/api/programmers', null, json_encode($data));

$response = $request->send();

$programmerUrl = $response->getHeader('Location');

// echo $response;
// echo "\n\n";
// die;

// 2) GET a programmer resource
//$request = $client->get('/api/programmers/'.$nickname);
$request = $client->get($programmerUrl);

$response = $request->send();

// echo $response;
// echo "\n\n";

// 3) GET a list of all programmers
$request = $client->get('/api/programmers');
$response = $request->send();

echo $response;
echo "\n\n";
