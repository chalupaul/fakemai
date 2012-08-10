<html><body>
<?
require_once('config.php');
require_once('URLToken.php');

print "<p><a href=\"http://$_SERVER[SERVER_NAME]" . urlauth_gen_url($sURL, $sParam, $nWindow, $sSalt, $sExtract, time()) . "\">good link, generated with time()</a></p>\n";
print "<p><a href=\"http://$_SERVER[SERVER_NAME]" . urlauth_gen_url($sURL, $sParam, $nWindow, $sSalt, '192.168.10.10', time()) . "\">bad link, different ip address</a></p>\n";
print "<p><a href=\"http://$_SERVER[SERVER_NAME]" . urlauth_gen_url($sURL, $sParam, $nWindow, $sSalt, $sExtract, time() - 172800) . "\">old link, generated 2 days ago</a></p>\n";

?>
</body></html>