<?php
$filePath=$_GET['url'];
$pieces = explode("/", $filePath);
$filename=end($pieces);
echo $filePath;
echo "<br/>";
echo $filename;
?>
<p>
<a href="<?php echo $filePath;?>">Click here</a>
</p>
<?php 
  // The file path
  //$file = "/path/to/file.pdf"; 
  
  //echo file_get_contents($filePath);
  //file_put_contents("temp.pdf", file_get_contents($filePath));
    
  // Header Content Type
  //header("Content-type: application/pdf"); 
    
  //header("Content-Length: " . filesize($filePath)); 
    
  // Send the file to the browser.
  //readfile($filePath); 
?>