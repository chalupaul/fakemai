<?
/**
 * serve_file_resumable()
 * This is a wrapper to let php support the range header for resumes
 * and partial uploads.
 */
function serve_file_resumable ($sFile, $sContentType = 'application/octet-stream') {

    // Avoid sending unexpected errors to the client - we should be serving a file,
    // we don't want to corrupt the data we send
    @error_reporting(0);

    // Make sure the files exists, otherwise we are wasting our time
    if (!file_exists($sFile)) {
      header("HTTP/1.1 404 Not Found");
      exit;
    }

    // Get the 'Range' header if one was sent
    if (isset($_SERVER['HTTP_RANGE'])) $mRange = $_SERVER['HTTP_RANGE']; // IIS/Some Apache versions
    else if ($apache = apache_request_headers()) { // Try Apache again
      $aHeaders = array();
      foreach ($apache as $header => $val) $aHeaders[strtolower($header)] = $val;
      if (isset($aHeaders['range'])) $mRange = $aHeaders['range'];
      else $mRange = FALSE; // We can't get the header/there isn't one set
    } else $mRange = FALSE; // We can't get the header/there isn't one set

    // Get the data range requested (if any)
    $sFilesize = filesize($sFile);
    if ($mRange) {
      $bPartial = true;
      list($param,$mRange) = explode('=',$mRange);
      if (strtolower(trim($param)) != 'bytes') { // Bad request - range unit is not 'bytes'
        header("HTTP/1.1 400 Invalid Request");
        exit;
      }
      $mRange = explode(',',$mRange);
      $mRange = explode('-',$mRange[0]); // We only deal with the first requested range
      if (count($mRange) != 2) { // Bad request - 'bytes' parameter is not valid
        header("HTTP/1.1 400 Invalid Request");
        exit;
      }
      if ($mRange[0] === '') { // First number missing, return last $mRange[1] bytes
        $nEnd = $sFilesize - 1;
        $nStart = $nEnd - intval($mRange[0]);
      } else if ($mRange[1] === '') { // Second number missing, return from byte $mRange[0] to end
        $nStart = intval($mRange[0]);
        $nEnd = $sFilesize - 1;
      } else { // Both numbers present, return specific range
        $nStart = intval($mRange[0]);
        $nEnd = intval($mRange[1]);
        if ($nEnd >= $sFilesize || (!$nStart && (!$nEnd || $nEnd == ($sFilesize - 0)))) $bPartial = false; // Invalid range/whole file specified, return whole file
      }      
      $length = $nEnd - $nStart + 1;
    } else $bPartial = false; // No range requested

    // Send standard headers
    header("Content-Type: $sContentType");
    header("Content-Length: $sFilesize");
    header('Content-Disposition: attachment; filename="'.basename($sFile).'"');
    header('Accept-Ranges: bytes');

    // if requested, send extra headers and part of file...
    if ($bPartial) {
      header('HTTP/1.1 206 Partial Content'); 
      header("Content-Range: bytes $nStart-$nEnd/$sFilesize"); 
      if (!$fp = fopen($sFile, 'r')) { // Error out if we can't read the file
        header("HTTP/1.1 500 Internal Server Error");
        exit;
      }
      if ($nStart) fseek($fp,$nStart);
      while ($length) { // Read in blocks of 8KB so we don't chew up memory on the server
        $read = ($length > 8192) ? 8192 : $length;
        $length -= $read;
        print(fread($fp,$read));
      }
      fclose($fp);
    } else readfile($sFile); // ...otherwise just send the whole file

    // Exit here to avoid accidentally sending extra content on the end of the file
    exit;

  }
?>
