<?php

require 'common.php';

if(!empty($_GET)){
    $inputSanitized = filter_var($_GET['searchTxt'], FILTER_SANITIZE_STRING);
    echo $inputSanitized;
    $params = [
        'index' => MAIN_INDEX,
        'body'  => [
            "min_score"=> 0.5,
            'size'=>100,
            "aggs"=> [
                "user_names"=> [
                    "terms"=> [
                        "field"=> "user_username",
                    ]
                ]
            ],
            "_source"=> ["post_value"],
            'query' => [
                "multi_match" => [
                    "query" =>    $inputSanitized,
                    "type"=>       "most_fields",
                    //^3 boosts by 3
                    "fields"=> [ "user_username^4", "user_name^5", "user_email^3", "post_value^2", "comment_value"]
                ]
            ]
        ]
    ];

    $response = $client->search($params);
    echo json_encode([ 'hits'=>$response['hits']['hits'], 'user_names'=>$response['aggregations']['user_names']['buckets'] ]);
}
else{
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <!-- CSS only -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    </head>
    <body>
    <input placeholder="Enter some text" name="name"/>
    <div class="row">
        <div class="col-6">
            <ul id="userResults" class="list-group results"></ul>
        </div>
        <div class="col-6">
            <ul id="postResults" class="list-group results"></ul>
        </div>
    </div>
    </body>
    </html>

    <script>
        $(document).ready(function(){
            $("input").on('keyup', function(){
                const search = $(this).val();
                $.get('index.php?searchTxt="'+search+'"', function (response) {
                    $('.results').html(""); //clear all results areas
                    response.user_names.forEach(user_name => {
                        const val = insertHighlights(search, user_name.key);
                        $('#userResults').append(`<li class="list-group-item">${val}</li>`);
                    });
                    response.hits.forEach(hit => {
                        console.log(hit._source)
                        const d = hit._source;
                        const postHtml=insertHighlights(search, d.post_value);
                        $('#postResults').append(`
                            <li class="list-group-item">
                                Post: ${postHtml}
                            </li>
                        `);
                    });
                }, 'JSON')
            });
        });

        function insertHighlights(search, str){
            return replaceAll(str, search, '<b>'+search+'</b>');
        }

        function replaceAll(str, find, replace) {
            return str.toLowerCase().replace(new RegExp(find.toLowerCase(), 'g'), replace);
        }

    </script>
    <?php
}