<?php

/*//
HRCLOUD2-PLUGIN-START
App Name: Grabber
App Version: 2.0 (4-13-2021 23:30)
App License: GPLv3
App Author: zelon88
App Description: A simple HRCloud2 App for grabbing files from URL's.
App Integration: 0 (False)
HRCLOUD2-PLUGIN-END
//*/

$minStyles = 1;

// / The following code sanitizes the user input URL. 
if (isset($_POST['grabberURL'])) { 
  $grabberURLPOST = htmlentities(str_replace(str_split('~#[](){};$!#^&@>*<"\''), '', $_POST['grabberURL']), ENT_QUOTES, 'UTF-8'); }

// / The following code sanitizes the user input filename. 
if (isset($_POST['grabberFilename'])) { 
  $grabberFilenamePOST = htmlentities(str_replace(str_split('~#[](){};$!#^&@>*<"\''), '', $_POST['grabberFilename']), ENT_QUOTES, 'UTF-8');
  $grabberFilenamePOST = str_replace('./', '', $grabberFilenamePOST); 
  $grabberFilenamePOST = str_replace('../', '', $grabberFilenamePOST);
  $grabberFilenamePOST = str_replace('..', '', $grabberFilenamePOST); 
  $grabberFilenamePOST = str_replace('./', '', $grabberFilenamePOST); 
  $grabberFilenamePOST = str_replace('../', '', $grabberFilenamePOST);
  $grabberFilenamePOST = str_replace('..', '', $grabberFilenamePOST); } 
?>

<script type="text/javascript">
// / Javascript to clear the text input fields onclick.
    function Clear() {    
      document.getElementById("grabberURL").value= ""; }
    function Clear() {    
      document.getElementById("grabberFilename").value= ""; }
    function goBack() {
      window.history.back(); }
</script>
<div id='GrabberAPP' name='GrabberAPP' align='center'><h3>File Grabber</h3><hr />
<form action="Grabber.php" method="post" enctype="multipart/form-data">
<?php 
if (!isset($grabberURLPOST)) { 
  echo ('<p align="left" style="padding-left:15px;"><strong>1. </strong>Enter a URL to download.</p>'); 
?>
<p align="left" style="padding-left:15px;"><input id="grabberURL" name="grabberURL" value="" type="text"></p>
<?php 
  echo ('<p align="left" style="padding-left:15px;"><strong>2. </strong>Enter a Cloud directory/filename for your downloaded file.</p>'); 
  echo('<p align="left" style="padding-left:15px;"><input  id="grabberFilename" name="grabberFilename" value="" type="text"></p>'); 
  echo ('<p align="left" style="padding-left:15px;"><input type="submit" id="grabberSubmit" name="grabberSubmit" title="Grab Files" alt="Grab Files" value="Grab Files"></p><hr />'); } 
?>
</form>
<?php
// / The follwoing code checks if the commonCore.php file exists and 
// / terminates if it does not.
if (!file_exists('/var/www/html/HRProprietary/HRCloud2/commonCore.php')) {
  echo nl2br('</head><body>ERROR!!! HRC2GrabberApp46, Cannot process the HRCloud2 Common Core file (commonCore.php)!'."\n".'</body></html>'); 
  die (); }
else {
  require_once ('/var/www/html/HRProprietary/HRCloud2/commonCore.php'); }

if ($UserIDRAW == 0) {
  $txt = ('ERROR!!! HRC2GrabberApp56, A non-logged in user attempted to execute the Grabber App on '.$Time.'.');
  $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL , FILE_APPEND); 
  die($txt); }

$GRABData1 = $GRABData2 = FALSE;
$curlCode = '';
$ERROR = 0;
$YouTubeArr = array('youtube', 'youtu.be', 'googlevideo', 'googleusercontent', 'gstatic');
$DangerArr = array('<script ', '<?', '?>');
$DangerExtArr = array('..', './', '.php', '.html', '.js', '.a');  
$txt = ('OP-Act: Initiating Grabber App on '.$Time.'.');
$MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL , FILE_APPEND);
  
// / The following function was found on 
 // / http://stackoverflow.com/questions/7684771/how-check-if-file-exists-from-the-url
// / It checks if a remote file exists and returns the status. 
function does_url_exist($url) {
  $ch = curl_init($url);    
  curl_setopt($ch, CURLOPT_NOBODY, TRUE);
  curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  if ($code == 200 or $code == 302) {
    $stat = TRUE; } 
  else {
    $stat = FALSE; }
  curl_close($ch);
  return array($stat, $code); }

// / The following code was designed by zelon88 and grabs a target file for storage on the server.
function grab_simple_file($url, $filename) {
  set_time_limit(0);
  $fp = fopen ($filename, 'w+');
  $ch = curl_init(str_replace(" ","%20",$url));
  curl_setopt($ch, CURLOPT_TIMEOUT, 50);
  curl_setopt($ch, CURLOPT_FILE, $fp); 
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
  curl_exec($ch); 
  curl_close($ch);
  fclose($fp); }

// / The following code handles errors and echos the result of the operation to the user.
if (isset($grabberURLPOST)) {
  $GrabberURLExtension = pathinfo(parse_url($grabberURLPOST, PHP_URL_PATH), PATHINFO_EXTENSION);
  $GrabberFile = $CloudUsrDir.$grabberFilenamePOST.'.'.$GrabberURLExtension;  
  $GrabberTmpFile = $CloudTmpDir.$grabberFilenamePOST.'.'.$GrabberURLExtension;
  $txt = ('OP-Act: Scanning URL for file on '.$Time.'.');
  echo ($txt.'<hr />');
  $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL , FILE_APPEND);
  list ($doesURLExist, $curlCode) = does_url_exist($grabberURLPOST);
  if ($doesURLExist == TRUE) { 
      $txt = ('ERROR!!! HRC2GrabberApp70, Curl Error, '.$curlCode.', The supplied URL contains reference to a file that does not exist on '.$Time.'!');
      die($txt.'<hr />');
      $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL , FILE_APPEND); } 
  foreach($DangerExtArr as $DangerExtArr1) {
    $Ext1Count = strlen($DangerExtArr1);
    $ExtString = substr($grabberURLPOST, -$Ext1Count);
    if (in_array($ExtString, $DangerExtArr)) {
      $txt = ('ERROR!!! HRC2GrabberApp99, The file at the specified URL is an unsupported filetype on '.$Time.'.');
      $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL , FILE_APPEND); 
      die($txt); } }
  $txt = ('OP-Act: Scan complete! A File exists at '.$grabberURLPOST.' on '.$Time.'.');
  echo ($txt.'<hr />');
  $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL , FILE_APPEND);
  $GRABData1 = grab_simple_file($grabberURLPOST, $GrabberFile); 
  $GRABData2 = copy($GrabberFile, $GrabberTmpFile); 
  if (!$GRABData2) { 
    $txt = ('ERROR!!! HRC2GrabberApp132, Could not copy '.$GrabberFile.' to '.$GrabberTmpFile.' on '.$Time.'!'); 
    $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL , FILE_APPEND); 
    die($txt.'<hr />'); } 
  // / Check the Cloud Location with ClamAV if VirusScanning is enabled in Admin Settings.
  if ($VirusScan == '1') {
    shell_exec('clamscan -r '.$GrabberFile.' | grep FOUND >> '.$ClamLogDir); 
    shell_exec('clamscan -r '.$CloudTempDir.' | grep FOUND >> '.$ClamLogDir); 
    $VirusScanDATA = file_get_contents($ClamLogDir);
    if (filesize($ClamLogDir > 3) or strpos($VirusScanDATA, 'FOUND') == TRUE) {
      echo nl2br('WARNING!!! HRC2GrabberApp124, There were potentially infected files detected. The file
        transfer could not be completed at this time. Please check your file for viruses, check your HRC2 AV logs, 
        or try again later.'."\n"); 
        die(); } }
    if (!file_exists($GrabberFile)) {
      $txt = ('ERROR!!! HRC2GrabberApp115, There was a problem creating '.$grabberFilenamePOST.' on '.$Time.'!'); 
      $ERROR = 1;
      echo ($txt.'<hr />');
      $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL , FILE_APPEND); }
    if (file_exists($GrabberFile)) {
      $txt = ('OP-Act: Created '.$grabberFilenamePOST.' on '.$Time.'!'); 
      echo ($txt.'<hr />');
      $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL , FILE_APPEND); } 
  $txt1 = ('<p><form action="'.$URL.'/HRProprietary/HRCloud2/DATA/'.$UserID.'/'.$grabberFilenamePOST.'"><input type="submit" id="downloadGrabbed" name="downloadGrabbed" value="Download File"></form></p>');
  if (!file_exists($GrabberFile) or $ERROR == 1) {
    $txt1 = ''; }
  $txt2 = ('<p><button id="goBack" name="goBack" onclick="goBack();">Go Back</button></p>');
  echo nl2br($txt1.$txt2.'<hr />'); }

?>
</div>