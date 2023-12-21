<?php
require 'vendor/autoload.php';

use App\Client\PrivateApiClient;

$configuration = [
    'pim_url' => 'https://yourinstance.demo.cloud.akeneo.com',
    'admin_username' => 'admin',
    'admin_password' => 'password'
];

$client=new PrivateApiClient($configuration);

$users = $client->getUsers();

foreach($users as $user) {
   echo $user->code.' email:'.$user->email.' user groups:'.implode(',', $user->groups)."\n";
}

