<?php

# Includes the api key from a separate file
include 'apikey.php';

# The name of the show you're looking up
$showname = "Fullmetal%20Alchemist%20Brotherhood";

# Defines a function to retrieve a JWT token
function getToken() {
	# Retrieves the apikey from global space
	global $apikey;
	# Sets the API endpoint
	$url = 'https://api.thetvdb.com/login';
	$data = json_encode(array(
			'apikey' => $apikey,
	));

	# Build and send the POST request
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

	# Close the curl handler and return the JWT token
        curl_close($ch);
	return json_decode($response)->{'token'};
}

# Defines a function to make a generic API GET request
function makeRequest($token, $url) {
	# Build and send the curl handler
	$ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer '.$token));
        $response = curl_exec($ch);

	# Close the curl handler and return the JSON response
        curl_close($ch);
        return $response;
}

# Defines a function to retrieve a show id
function showLookup($token, $showname) {
	# Sets the API endpoint
	$url = "https://api.thetvdb.com/search/series?name=$showname";
	# Retrieve the JSON response
	$response = makeRequest($token, $url);
	# Return the id
	return json_decode($response)->{'data'}[0]->{'id'};
}

# Defines a function to count the episodes of a show
function getEpisodeCount($token, $id) {
	# Sets the API endpoint
	$url = "https://api.thetvdb.com/series/$id/episodes";
	# Retrieve the JSON response
	$response = makeRequest($token, $url);
	$count = 0;
	# Iterate over episodes counting every episodes with an "aired season" greater than 0
	foreach (json_decode($response)->{'data'} as $episode) {
		if ($episode->{'airedSeason'} > 0) {
			$count++;
		}
	}
	
	# Return the sum of episodes
	return $count;
}

# Defines a function to retrieve the listed runtime of a show
function getRuntime($token, $id) {
	# Sets the API endpoint
	$url = "https://api.thetvdb.com/series/$id";
	# Retrieve the JSON response
	$response = makeRequest($token, $url);
	# Return the listed runtime
	return json_decode($response)->{'data'}->{'runtime'};
}

# Get a JWT token
$token = getToken();
# Get the show id
$id = showLookup($token, "$showname");
# Get the episode count
$count = getEpisodeCount($token, $id);
# Get the listed runtime
$runtime = getRuntime($token, $id);
# Calculate total runtime and print it out in terms of hours and minutes
$totalruntime = $runtime*$count;
print(floor($totalruntime/60)." hours ".($totalruntime%60)." minutes.");

?>
