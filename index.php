<?php
header("Access-Control-Allow-Origin: *");

require_once "db.php";

function generateCode($len) {
	if ($len > 36) $len = 36;
	$str = "1234567890abcdefghijklmnopqrstuvwxyz";
	$str = substr(str_shuffle($str), 0, $len);
	return $str;
}

function generateUniqueCode($len) {
	global $db;
	$key = generateCode($len);
	$sql = "SELECT * FROM data WHERE code = ?;";
	if ($stmt = $db->prepare($sql)) {
		$stmt->bind_param("s", $key);
		if ($stmt->execute()) {
			$stmt->store_result();
			if ($stmt->num_rows > 0) {
				generateUniqueCode($len);
			} else {
				return $key;
			}
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function urlIsShortened($url) {
	global $db;
	$sql = "SELECT code, url FROM data WHERE url = ?;";
	if ($stmt = $db->prepare($sql)) {
		$stmt->bind_param("s", $url);
		if ($stmt->execute()) {
				$stmt->bind_result($code, $url);	
				while ($stmt->fetch()) {
					return $code;
				}
				return false;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

$shortenedURL = $error = "";

function createShortlink() {
global $db;
global $shortenedURL;
global $error;
if (!(isset($_POST["url"]) && !empty(trim($_POST["url"])))) {
	return;
} else {
	$url = trim($_POST["url"]);

	// make sure the URL is valid
	if (!preg_match("/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?|^((http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/", $url)) {
		$error = "Invalid URL";
		return;
	}

	// if there is no scheme, then make the scheme http
	if (!parse_url($url, PHP_URL_SCHEME)) {
		$url = "http://$url";
	}
	
	if (!$code = generateUniqueCode(5)) return;

	if ($alreadyShortened = urlIsShortened($url)) $shortenedURL = $alreadyShortened;

	if (empty($shortenedURL)) {
		$sql = "INSERT INTO data (code, url) VALUES (?, ?);";
		if ($stmt = $db->prepare($sql)) {
			$stmt->bind_param("ss", $code, $url);
			if ($stmt->execute()) {
				$shortenedURL = $code;
			} else {
				return;
			}
		} else {
			return;
		}
	}
}
}
createShortlink();
?>
<!DOCTYPE html>
<html>
<head>
	<meta name="description" content="A simple URL shortener. It's free, it's easy, and it's open source.">
	<meta name="keywords" lang="en" content="url,uri,short,shorten,save,share,simple,easy,free,url.af,urlaf,small,quick,free url shortener,free link shortener,link,best url shortener,best,shorten url free,shorten link free">
	<meta name="robots" content="index">
	<title>url.af | Free URL shortener</title>
	<link rel="stylesheet" type="text/css" href="style.css">
	<link rel="icon" href="data:image/x-icon;base64,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" type="image/x-icon" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
	<header>
		<span>url.af</span>
		<img src="flame.svg">
		<a href="https://github.com/Andrew-9/url.af/"><img id="github" src="GitHub.png"></a>
	</header>
	<form method="POST">
		<input name="url" value="<?php if (!empty($error)) echo $error; else if (isset($shortenedURL) && !empty(trim($shortenedURL))) echo htmlspecialchars("url.af/$shortenedURL"); ?>">
		<button type="submit">shorten it</button>
	</form>
</body>
</html>