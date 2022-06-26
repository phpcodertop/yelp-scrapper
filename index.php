<?php
/**
 * Created by Ahmed Maher Halima.
 * Email: phpcodertop@gmail.com
 * github: https://github.com/phpcodertop
 * Date: 6/26/2022
 * Time: 12:50 AM
 */

include_once 'vendor/autoload.php';
include_once 'Yelp.php';

# https://github.com/RavenHustler/yelpscraper    Alternative repository
# https://yelp.test

# sample search url https://yelp.test?search=hotel&location=california&max=20

$searchPhrase = $_GET['search'] ?? 'Restaurants - Delivery';
$location = $_GET['location'] ?? 'San Francisco, CA';
$max = $_GET['max'] ?? 20;

$yelp = new Yelp($searchPhrase, $location);

$filteredResults = $yelp->scrape($max);

try
{
    $yelp->export();
} catch (Exception $e)
{
}

