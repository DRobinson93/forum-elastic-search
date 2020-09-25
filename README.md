# forum-elastic-search
Elastic Search for data from a forum

**elastic search needs to be installed to use this**

//install composer reqs:
curl -s http://getcomposer.org/installer | php
php composer.phar install --no-dev

//run build.php to populate elastic search with data

//I used php self server:
php -S localhost:8000

go to localhost:8000 and search and it will hit elastic search


curl -XGET 'localhost:9200/main_search/_search' -d '{
    "query" : {
        "match" : {
            "user_id" : "1"
        }
    }
}' -H 'Content-Type: application/json';
