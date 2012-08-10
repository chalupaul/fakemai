<?php
require_once('URLToken.php');
require_once('config.php');
require_once('partial.php');

list($sPassedTokenTime, $sPassedTokenClient) = split('_', $_REQUEST['token']);
$nTime = $sPassedTokenTime - $nWindow;
$sRebuiltTokenClient = urlauth_gen_token($sURL, $nWindow, $sSalt, $sExtract, $nTime);

if ($sPassedTokenTime >= time() && $sPassedTokenClient == $sRebuiltTokenClient) {
   $aCutURI = parse_url($_SERVER['REQUEST_URI']);
   $sDownloadFile = basename($aCutURI['path']);
   serve_file_resumable("$sHiddenFilesDir/$sDownloadFile", $sDownloadMimeType);
} else {
  header("HTTP/1.1 410 Gone.");
}
?>