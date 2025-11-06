
# Flashy - Home assignment

1. Application setup
    * Git
        - git init
        - git branch -M main
        - git remote add origin https://github.com/HaEmailBe/flashy.git
        - git pull origin main

    * Composer
        - composer install
        - composer dump-autoload

    * Laravel Configuration - develop (.env) & testing (.env.testing)
        - config your DB
        - set your APP_URL domain

    * Composer
        - composer install
        - composer dump-autoload

    * PHP artisan
        - php artisan install:api
        - php artisan key:generate
        - php artisan optimize:clear
        - php artisan migrate --pretend ( dry run )
        - php artisan migrate --step

1. API List

    - How to create a link
        1. Request:
            - URL: api/links
            - Method: POST
            - Header:
                'X-Api-Key' (required)
            - Data:
                | name          | validation                                  | Remarks                                                                                            |
                |---------------|---------------------------------------------|----------------------------------------------------------------------------------------------------|
                | target_url    | required,url,max:255                        |                                                                                                    |
                | slug          | unique,regex(a-zA-Z0-9-),maxLength | maxLength = 255 - (int) Str::length(config('cache.prefix') . '-:link-'); // cache table key length |
                | is_active     | required,boolean                            |                                                                                                    |

        1. Response:
            - 201 - created [
                'success' => true,
                'message' => 'Link created successfully',
                'data' => *{$link}*
            ]
            - 401 - Unauthorized [
                'success' => false,
                'message' => 'Unauthorized. Invalid API key.'
            ]
            - 422 - Unprocessable Content [
                'success' => false,
                'message' => 'Validation errors',
                'errors' => [
                    "*{name}*" => [
                        0 => "*{validation message}*"
                    ]]]
            - 429 - Too Many Requests [
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => 59
            ]
        
        1. Flow:
            - Auto-generate slug if missing from request 
            - Add new record to links table
            - When link is active add to cache 
                - key = *{cache.prefix.value}*:link-*{slug}* 
                - value = { id = *{link.id}* , target_url = *{link.target_url}* }

    - How to hit a link
        1. Request:
            - URL: r/{slug}
            - Method: GET

        1. Response:
            - 302 - created [
                'success' => true,
                'message' => 'Link is active and exists in caching/Link is active but not found in cache',
            ] 
            - 400 - Bad Request [
                'success' => false,
                'message' => 'Required parameter 'slug' is missing or empty',
            ]
            - 404 - Not Found [
                'success' => false,
                'message' => 'No redirect, Link is not active/Slug not found in the database'
            ]
        
        1. Flow:
            - When slug is empty return http code 400 
            - Search in the caching
                - If exist 
                    - redirect to target_url
                    - trigger async job ( LinkHit )
                - If not exist search in the database  
                    - If link is active save in cache , redirect, trigger async job 
                    - If not active return 404
                    - If not found return 404
            - async job ( linkHit )
                - add new record to link_hists table
                - trigger event ( LinkHitEvent )
                    - event listener (ClearStatisticKeyFromCache) remove key (*{cache.prefix.value}*:link-statistics-{$slug}) from cache
     
     - Get statistics
        1. Request:
            - URL: /links/{slug}/stats
            - Method: GET

        1. Response:
            - 200 - created [
                'success' => true,
                'message' => "Get statistics from the cache/database",
                'data' => $data
            ] 
            - 400 - Bad Request [
                'success' => false,
                'message' => 'Required parameter 'slug' is missing or empty',
            ]
            - 404 - Not Found [
                'success' => false,
                'message' => 'Slug not found in the database'
            ]
        
        1. Flow:
            - When slug is empty return HTTP 400 
            - Check cache:
                - If found: return HTTP 200 with statistics
                - If not found: query database and calculate statistics  
                    - If found: cache for 60s (TTL), return HTTP 200 with statistics
                    - If not found: return HTTP 404

1. Bot filtering
    1. Flow:
        - Get User-Agent from request header
            - If empty or exists in badBot list, log to file and return HTTP 403 with  "Access denied for this user agent".
            - Else continue

1. Architecture decisions
    - Bot filtering
        - Whitelist good bots -Allow search engines and social media crawlers
        - Log all blocks - Track patterns and adjust filters 
    - Caching (database)
        - Dramatically faster response times
        - Reduced database load
        - Improved scalability - Cached data can be served to thousands of users simultaneously
        - Automatic expiration
    - Async jobs ( linkHit ) 
        - Faster response times
        - Horizontal scaling - Add more queue workers on separate servers 
        - Independent scaling - Scale queue workers separately from your web servers
        - Automatic retries
        - Job timeout protection
        - Built-in monitoring - Track job status, failures, and processing times using Laravel Horizon management tools.
        - Failed job tracking - Laravel logs failed jobs in a database table for review and manual retry if needed.
    - Trigger event ( LinkHitEvent )
        - Better user experience
        - Multiple listeners per event
        - Horizontal scaling - Queue workers can run on separate servers
        - Conditional execution - Listeners can decide whether to execute based on event data
        - Team collaboration - Different developers can work on different listeners independently without conflicts.                    


