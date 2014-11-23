<?php

class CImage {
  public function __construct($imgDirPath,$cachePath){
    $this->imgDirPath = $imgDirPath;
    $this->cachePath = $cachePath;
  }
  private $imgPath;
  private $verbose;
  private $verboseLog;
  private $saveAs;
  
  public function getParameters() {
    $src = isset($_GET['src']) ? strip_tags($_GET['src']) : null;
    $this->saveAs = isset($_GET['save-as']) ? strip_tags($_GET['save-as']) : null;
    $height = isset($_GET['height']) ? strip_tags($_GET['height']) : null;
    $width = isset($_GET['width']) ? strip_tags($_GET['width']) : null;
    $crop_to_fit = isset($_GET['crop-to-fit']) ? strip_tags($_GET['crop-to-fit']) : null;
    $quality = isset($_GET['quality']) ? strip_tags($_GET['quality']) : 60;
    $sharpen = isset($_GET['sharpen']) ? strip_tags($_GET['sharpen']) : null;
    $this->verbose = isset($_GET['verbose']) ? true : null;
    $no_cache = isset($_GET['no-cache']) ? strip_tags($_GET['no-cache']) : null;
    $maxHeight = isset($_GET['maxHeight']) ? strip_tags($_GET['maxHeight']) : 2000;
    $maxWidth = isset($_GET['maxWidth']) ? strip_tags($_GET['maxWidth']) : 2000;
    $parameters = array (
      'src' => $src,
      'save_as' => $this->saveAs,
      'height' => $height,
      'width' => $width,
      'crop_to_fit' => $crop_to_fit,
      'quality' => $quality,
      'sharpen' => $sharpen,
      'verbose' => $this->verbose,
      'no_cache' => $no_cache,
      'maxHeight'=> $maxHeight,
      'maxWidth' =>  $maxWidth,
    );
    return $parameters;
  }
  /// validate
  // get source
  // open original
  // rezise / crop
  // sharpen / qulaity
  // verbose
  /// cache
  
  
  public function displayImage($params) { 
    /// first make sure all params are valid
    $this->imgPath = realpath($this->imgDirPath . $params['src']);
    $this->validateParameters($params);
    $image = $this->getOriginalImg();
    if (isset($params['verbose'])) {
      $this->createVerboseLog();
    }
    if(isset($params['crop_to_fit']) || isset($params['height']) || isset($params['width'])) {
      $image = $this->resizeImage($params['height'], $params['width'], $params['crop_to_fit'], $image);
    }
    if (isset($params['sharpen'])) {
      $image = $this->sharpenImage($image);
    }
    $cacheFileName = $this->createCacheFileName($params);
    $this->checkCachePath($cacheFileName, $params['no_cache']);
    
    $this->saveImg($image, $cacheFileName, $params['quality']);
    $this->outputImage($cacheFileName, $this->verbose);
  }
  //
  // Get original image
  //
  private function getOriginalImg() {
    $parts = pathinfo($this->imgPath);
    $fileExtensions = $parts['extension'];

    switch($fileExtensions) {
      case 'jpg':
      case 'jpeg':
        $image = imagecreatefromjpeg($this->imgPath);
        break;

      case 'png':
        $image = imagecreatefrompng($this->imgPath);
        break;

      default:
        $this->errorMessage('No support for given file extension');
    }
    return $image;
  }
  /// Resize image 
  private function resizeImage($newHeight, $newWidth, $cropToFit, $image) {
    /// get orginal image width / height
    $imgInfo = list($width, $height) = getimagesize($this->imgPath);
    $aspectRatio = $width / $height;
    if($newHeight && $newWidth && $cropToFit) { /// crop to fit
      $cropWidth = $aspectRatio > $aspectRatio ? $newWidth : round($newHeight * $aspectRatio);
      $cropHeight = $aspectRatio > $aspectRatio ? round($width / $aspectRatio) : $newHeight;
      if($this->verbose) {
        $this->createVerbose("Crop to fit into box of {$newWidth} x {$newHeight}. Cropping dimensions: {$cropWidth}x{$cropHeight}.");
      }
    }
    else if($newWidth && !$newHeight) { /// get new width
      $newHeight = round($newWidth / $aspectRatio);
      if($this->verbose) {$this->createVerbose("New width is known {$newWidth}, height is calculated to {$newHeight}."); }
    }
    else if(!$newWidth && $newHeight) { /// get new height
      $newWidth = round($newHeight * $aspectRatio);
      if($this->verbose) {$this->createVerbose("New height is known {$newHeight}, width is calculated to {$newWidth}."); }
    }
    else if($newWidth && $newHeight) { /// get new width and height
      $ratioWidth  = $width  / $newWidth;
      $ratioHeight = $height / $newHeight;
      $ratio = ($ratioWidth > $ratioHeight) ? $ratioWidth : $ratioHeight;
      $newWidth  = round($width  / $ratio);
      $newHeight = round($height / $ratio);
      if($this->verbose) {$this->createVerbose("New width & height is requested, keeping aspect ratio results in {$newWidth}x{$newHeight}."); }
    }
    else {
      $newWidth = $width;
      $newHeight = $height;
      if($this-$verbose) { $this->createVerbose("Keeping original width & heigth."); }
    }
    /// make the changes to the image
    if($cropToFit) {
      if($this->verbose) { $this->createVerbose("Resizing, crop to fit."); }
      $cropX = round(($width - $cropWidth) / 2);  
      $cropY = round(($height - $cropHeight) / 2);    
      $imageResized = $this->createImageKeepTransparency($newWidth, $newHeight);
      imagecopyresampled($imageResized, $image, 0, 0, $cropX, $cropY, $newWidth, $newHeight, $cropWidth, $cropHeight);
      $image = $imageResized;
      $width = $newWidth;
      $height = $newHeight;
    }
    else if(!($newWidth == $width && $newHeight == $height)) {
      if($this->verbose) { $this->createVerbose("Resizing, new height and/or width."); }
      $imageResized = $this->createImageKeepTransparency($newWidth, $newHeight);
      imagecopyresampled($imageResized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
      $image  = $imageResized;
      $width  = $newWidth;
      $height = $newHeight;
    }
    return $image;
  }
  /// Sharpen the image
  private function sharpenImage($image) {
    $matrix = array(
      array(-1,-1,-1,),
      array(-1,16,-1,),
      array(-1,-1,-1,)
    );
    $divisor = 8;
    $offset = 0;
    imageconvolution($image, $matrix, $divisor, $offset);
    if($this->verbose) {
      $this->createVerbose('Applying filter: Sharpen');
    }
    return $image;
  }
  /// Create a cashe file with the correct parameters in file name
  private function createCacheFileName($params) {
    $parts          = pathinfo($this->imgPath);
    $fileExtension  = $parts['extension'];
    $this->saveAs         = is_null($params['save_as']) ? $fileExtension : $params['save_as'];
    $quality_       = is_null($params['quality']) ? null : "_q{$params['quality']}";
    $cropToFit_     = is_null($params['crop_to_fit']) ? null : "_cf";
    $dirName        = preg_replace('/\//', '-', dirname($params['src']));
    $cacheFileName = IMAGE_CACHE_PATH . "-{$dirName}-{$parts['filename']}_{$params['width']}_{$params['height']}{$quality_}{$cropToFit_}.{$this->saveAs}";
    $cacheFileName = preg_replace('/^a-zA-Z0-9\.-_/', '', $cacheFileName);
    if($this->verbose) { $this->createVerbose("Cache file is: {$cacheFileName}"); }
    return $cacheFileName;
    
  }
  private function checkCachePath($cacheFileName, $ignoreCache) {
    $imageModifiedTime = filemtime($this->imgPath);
    $cacheModifiedTime = is_file($cacheFileName) ? filemtime($cacheFileName) : null;
    if(!$ignoreCache && is_file($cacheFileName) && $imageModifiedTime < $cacheModifiedTime) {
      if($this->verbose) {
        $this->createVerbose('Cache file is valid, output it.');
      }
      $this->outputImage($cacheFileName, $this->verbose);
    }
    if($this->verbose) {
      $this->createVerbose('Cache is not valid. Creating a new cache');
    }
  }
  
  private function saveImg($image, $cacheFileName, $quality) {
    switch($this->saveAs) {
      case 'jpeg':
      case 'jpg':
        if($this->verbose) { $this->createVerbose("Saving image as JPEG to cache using quality = {$quality}."); }
        imagejpeg($image, $cacheFileName, $quality);
      break;  

      case 'png':  
        if($this->verbose) { $this->createVerbose("Saving image as PNG to cache."); }
          imagealphablending($image, false);
          imagesavealpha($image, true);
          imagepng($image, $cacheFileName);   
      break;  

      default:
        $this->errorMessage('No support to save as this file extension.');
      break;
      if($this->verbose) { 
        clearstatcache();
        $cacheFilesize = filesize($cacheFileName);
        $this->createVerbose("File size of cached file: {$cacheFilesize} bytes."); 
        $this->createVerbose("Cache file has a file size of " . round($cacheFilesize/$filesize*100) . "% of the original size.");
      }
    }
  }
  //
  // Start displaying log if verbose mode & create url to current image
  //
  private function createVerboseLog() {
      $query = array();
      parse_str($_SERVER['QUERY_STRING'], $query);
      unset($query['verbose']);
      $url = '?' . http_build_query($query);


    $this->verboseLog = <<<EOD
    <html lang='en'>
    <meta charset='UTF-8'/>
    <title>img.php verbose mode</title>
    <h1>Verbose mode</h1>
    <p><a href=$url><code>$url</code></a><br>
    <img src='{$url}' /></p>
EOD;
  }
  
  
  //
    // Validate incoming arguments
  //
  private function validateParameters($params) {
    isset($params['src']) or $this->errorMessage('Must set src-attribute.');
    preg_match('#^[a-z0-9A-Z-_\.\/]+$#', $params['src']) or $this->errorMessage('Filename contains invalid characters.');
    substr_compare(IMAGE_PATH, $this->imgPath, 0, strlen(IMAGE_PATH)) == 0 or $this->errorMessage('Security constraint: Source image is not directly below the directory IMG_PATH.');
    if (isset($params['save_as'])) { if(!in_array($params['save_as'], array('png', 'jpg', 'jpeg'))) { $this->errorMessage('Not a valid extension to save image as'); }}
    if (isset($params['quality'])) { if((!is_numeric($params['quality'])) && ($params['quality'] < 0) || ($params['quality'] > 100)) { $this->errorMessage('Quality out of range'); }}
    if (isset($params['width'])) { if(!is_numeric($params['width']) and $params['width'] < 0 || $params['width'] >= $maxWidth) { $this->errorMessage('Width out of range'); }}
    if (isset($params['height'])) { if($params['height'] < 0 || $params['height'] > $params['maxHeight']) { $this->errorMessage('Height out of range'); }}
    if (isset($params['crop_to_fit'])) { if((!$params['crop_to_fit']) && (!$params['width']) && (!$params['height'])) { $this->errorMessage('Crop to fit needs both width and height to work'); }}
  }

   /**
   * Display error message.
   *
   * @param string $message the error message to display.
   */
  private function errorMessage($message) {
    header("Status: 404 Not Found");
    die('img.php says 404 - ' . htmlentities($message));
  }
  /**
 * Display log message.
 *
 * @param string $message the log message to display.
 */
  private function Createverbose($message) {
    $this->verboseLog .= "<p>" . htmlentities($message) . "</p>";
  }
  /**
  * Output an image together with last modified header.
  *
  * @param string $file as path to the image.
  * @param boolean $verbose if verbose mode is on or off.
  */
  private function outputImage($file, $verbose) {
    $info = getimagesize($file);
    !empty($info) or $this->errorMessage("The file doesn't seem to be an image.");
    $mime   = $info['mime'];
    $lastModified = filemtime($file);  
    $gmdate = gmdate("D, d M Y H:i:s", $lastModified);

    if($this->verbose) {
      $this->createVerbose("Memory peak: " . round(memory_get_peak_usage() /1024/1024) . "M");
      $this->createVerbose("Memory limit: " . ini_get('memory_limit'));
      $this->createVerbose("Time is {$gmdate} GMT.");
    }

    if(!$this->verbose) header('Last-Modified: ' . $gmdate . ' GMT');
    if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModified){
      if($this->verbose) { $this->createVerbose("Would send header 304 Not Modified, but its verbose mode."); }
      if($this->verbose) { echo $this->verboseLog; exit; }
      header('HTTP/1.0 304 Not Modified');
    } 
    else {  
      if($this->verbose) { $this->createVerbose("Would send header to deliver image with modified time: {$gmdate} GMT, but its verbose mode."); }
      if($this->verbose) { echo $this->verboseLog; exit; }
      header('Content-type: ' . $mime);  
      readfile($file);
    }
    exit;
  }
  /**
  * Create new image and keep transparency
  *
  * @param resource $image the image to apply this filter on.
  * @return resource $image as the processed image.
  */
  private function createImageKeepTransparency($width, $height) {
      $img = imagecreatetruecolor($width, $height);
      imagealphablending($img, false);
      imagesavealpha($img, true);  
      return $img;
  }
}
