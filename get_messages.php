<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require __DIR__ . '/vendor/autoload.php';

if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName('Gmail API PHP Quickstart');
    $client->setScopes(Google_Service_Gmail::GMAIL_READONLY);
    $client->setAuthConfig('credentials.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    // Load previously authorized token from a file, if it exists.
    // The file token.json stores the user's access and refresh tokens, and is
    // created automatically when the authorization flow completes for the first
    // time.
    $tokenPath = 'token.json';
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }

    // If there is no previous token or it's expired.
    if ($client->isAccessTokenExpired()) {
        // Refresh the token if possible, else fetch a new one.
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);

            // Check to see if there was an error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }
        // Save the token to a file.
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
    return $client;
}


// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Gmail($client);

$user = 'me';
for($i = 1500; $i < 1600; $i++) {
	$newTime = strtotime("-$i day");
	$before = strtotime(date('Y-m-d H:i:s', $newTime));

	$opt_param = array();

	$userId = 'me';
	$opt_param['q'] = "before:$before";

	$mlist = $service->users_messages->listUsersMessages($userId, $opt_param);
	foreach($mlist as $m){

		$optParamsGet2['format'] = 'full';
		$single_message = $service->users_messages->get('me',$m->id, $optParamsGet2);

		$message_id = $m->id;
		$headers = $single_message->getPayload()->getHeaders();
		$snippet = $single_message->getSnippet();

		foreach($headers as $single) {

			if ($single->getName() == 'Subject') {

				$message_subject = $single->getValue();

			}

			else if ($single->getName() == 'Date') {

				$message_date = $single->getValue();
				$message_date = date('M jS Y h:i A', strtotime($message_date));
			}

			else if ($single->getName() == 'From') {

				$message_sender = $single->getValue();
				$message_sender = str_replace('"', '', $message_sender);
			}
		}
		echo $message_date." ".$message_sender."\n";
	}
}
