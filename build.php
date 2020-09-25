<?php

require 'common.php';

$servername = "localhost";
$username = "root";
$password = "r00t";

// Create connection
try {
    $pdo = new PDO("mysql:host=$servername;dbname=blog", $username, $password);
    // set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
$stmt = $pdo->query("
        SELECT users.id as 'user_id', users.name as 'user_name', 
            users.username as 'user_username', users.email as 'user_email', 
            posts.id as 'post_id', posts.value as 'post_value',
            comments.id as 'comment_id', comments.value as 'comment_value' 
        FROM users
        LEFT JOIN posts on posts.user_id = users.id
        LEFT JOIN comments on comments.post_id = posts.id
        limit 1000  
");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

try {
    $response = $client->indices()->delete(['index' => MAIN_INDEX]);
} catch (exception $e) {
    //if running this first time, index will not exist
}

$params = [
    'index' => MAIN_INDEX,
    'body' => [
        'mappings' => [
            'properties' => [
                'user_id'     => [
                    "type"  => "keyword", //one word.. text is mult
                ],
                'user_name'     => [
                    "type"  => "text",
                     "boost"=> 2
                ],
                'user_username' => [
                    'type' => 'keyword',
                    "boost"=> 2
                ],
                'user_email'    => [
                    'type'  => 'keyword',
                ],
                'post_value'    => [
                    'type'  => 'text',
                ],
                'comment_value'    => [
                    'type'  => 'text',
                ],
            ]
        ]
    ]
];

$response = $client->indices()->create($params);

//populate data
foreach($results as $index => $data){
    $request = [
        'index' => MAIN_INDEX,
        'id' => $index,
        'body' => $data
    ];

    $response = $client->index($request);
}