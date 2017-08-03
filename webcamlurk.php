#!/usr/bin/php
<?php

define('UA', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/37.0.2062.120 Chrome/37.0.2062.120 Safari/537.36');

@list (, $fnprefix, $url, $referer) = $_SERVER['argv'];

if (!$fnprefix || !$url || !$referer) {
	echo <<<EOT
USAGE: webcamlurk.php PREFIX URL REFERER
where URL can contain \$CNT which will be replaced by a timestamp-like, increasing int.
Example:
  ./webcamlurk.php \\
    example \\
    'http://webcam1.comune.ra.it/record/current.jpg?rand=\$CNT' \\
    http://www.comune.ra.it/La-Citta/Webcam
  # (make sure to escape that dollar sign properly for your shell)

EOT;
die;
}

function curl_setup(&$curl) {
	global $referer;
	curl_setopt($curl, CURLOPT_USERAGENT, UA);
	if ($referer) {
		curl_setopt($curl, CURLOPT_REFERER, $referer);
	}
}

function geth () {
	global $url;
	$curl = curl_init();
	curl_setup($curl);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_FILETIME, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HEADER, true);
        $headers = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
	$m = array();
	if (preg_match("/^Last-Modified: (.*)$/m", $headers, $m)) {
		$lastmoddt = new DateTime($m[1]);
		return $lastmoddt;
	}
	else {
		return false;
	}
}
function download ($dlurl) {
	global $url;
	$curl = curl_init();
	curl_setup($curl);
	curl_setopt($curl, CURLOPT_URL, $dlurl);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec ($curl);
	$error = curl_error($curl); 
	if ($error) {
		echo "CURL error: $error\n";
		return false;
	}
	else {
		return $data;
	}
}
	


$minintv = 60;
$avgintv = $minintv;

$do_geth = true;

$lastts = new DateTime();
$lastts = $lastts->getTimestamp();
$lastmd5 = false;

$slept = $avgintv;
$sleepincr = 30;
$sleepoverhang = 10;

$cnt = 0;

while (true) {
	$sleep = $slept > 0
		? $sleepincr
		: max($avgintv + $sleepoverhang, $minintv);
	if ($cnt > 0) {
		echo "Sleeping for $sleep seconds\n";
		$slept += $sleep;
		sleep($sleep);
	}
	$do_download = true;
	if ($do_geth) {
		$geth = geth();
		if ($geth === false) {
			echo "DISABLING HEADERS\n";
			$do_geth = false;
		}
		else {
			echo "Headers say last mod was ".$geth->format("Y-m-d H:i:s").", ";
			$gethts = $geth->getTimestamp();
			if ($gethts !== $lastts) {
				echo "will download.\n";
				$do_download = true;
				$lastts = $gethts;
			}
			else {
				echo "gonna skip.\n";
				$do_download = false;
			}
		}
	}
	if (!$do_download) {
		echo "Not downloading after $slept seconds\n";
	}
	else {
		$dlurl = str_replace('$CNT', $cnt, $url);
		echo "Downloading from $dlurl after $slept seconds\n";
		$data = download($dlurl);
		if ($data !== false) {
			$md5 = md5($data);
			if ($md5 === $lastmd5) {
				echo "Data did not change, skipping\n";
			}
			else {
				$avgintv = ($slept + $avgintv - $sleepoverhang) / 2;
				echo "After $slept seconds, avgintv is now $avgintv\n";
				$slept = 0;
				$now = new DateTime();
				$outfn = $fnprefix.($now->format('Y-m-d_H:i:s'));
				$bytes = file_put_contents($outfn, $data);
				echo "$bytes Bytes written to $outfn\n";
				$lastmd5 = $md5;
			}
		}
	}
	$cnt++;
}
