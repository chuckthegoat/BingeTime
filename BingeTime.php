<?php

$showname = "Fullmetal%20Alchemist%20Brotherhood";

include 'apikey.php';

function getToken() {
	global $username, $userkey, $apikey;
	$url = 'https://api.thetvdb.com/login';
	$data = json_encode(array(
			'apikey' => $apikey,
			'userkey' => $userkey,
			'username' => $username
	));

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);
	return json_decode($response)->{'token'};
}

function makeRequest($token, $url, $data) {
	$ch = curl_init($url.$data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer '.$token));
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
}


function showLookup($token, $showName) {
	$url = 'https://api.thetvdb.com/search/series';
	$data =	"?name=$showName";
	$response = makeRequest($token, $url, $data);
	return json_decode($response)->{'data'}[0]->{'id'};
}

function getEpisodeCount($token, $id) {
	$url = "https://api.thetvdb.com/series/$id/episodes";
	$response = makeRequest($token, $url, '');
	$count = 0;
	$runtime = 0;
	foreach (json_decode($response)->{'data'} as $episode) {
		if ($episode->{'airedSeason'} > 0) {
			$count++;
		}
	}
	return $count;
}

function getRuntime($token, $id) {
	$url = "https://api.thetvdb.com/series/$id";
	$response = makeRequest($token, $url, '');
	return json_decode($response)->{'data'}->{'runtime'};
}

$token = getToken();
$id = showLookup($token, "$showname");
$count = getEpisodeCount($token, $id);
$runtime = getRuntime($token, $id);
$totalruntime = $runtime*$count;
print(floor($totalruntime/60)." hours ".($totalruntime%60)." minutes.");

?>
