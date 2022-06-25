<?php
/**
 * Created by Ahmed Maher Halima.
 * Email: phpcodertop@gmail.com
 * github: https://github.com/phpcodertop
 * Date: 6/26/2022
 * Time: 12:50 AM
 */

include_once 'vendor/autoload.php';

# https://github.com/RavenHustler/yelpscraper    Alternative repository
# https://yelp.test


$curl = curl_init();

$baseUrl = 'https://www.yelp.com/search/snippet';
$searchQuery = array(
    'find_desc' => 'Restaurants - Delivery',
    'find_loc' => 'San Francisco, CA',
    'request_origin' => 'user',
    'start' => 0,
);

curl_setopt_array($curl, array(
    CURLOPT_URL            => $baseUrl .'?'. http_build_query($searchQuery),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING       => '',
    CURLOPT_MAXREDIRS      => 10,
    CURLOPT_TIMEOUT        => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST  => 'GET',
    CURLOPT_HTTPHEADER     => array(
        'accept: */*',
    ),
));

$response = curl_exec($curl);

curl_close($curl);
try
{
    $response = json_decode($response, false, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    echo $e->getMessage();
}

$results = $response->searchPageProps->mainContentComponentsListProps;

$filteredResults = array_filter($results, static function ($result) {
    return $result->searchResultLayoutType !== 'separator';
});
dd($filteredResults);

