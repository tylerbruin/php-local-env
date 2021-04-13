<?php

// ! getPage.php is a secondary script used to fetch additional pages and its assets post using the index.php script.


// Log page load time
date_default_timezone_set("Pacific/Auckland");
echo "LOG: loaded at " . date('d-m-Y h:i:s A') . "<br><br>";

$pageURL = $_GET["url"];
$pageName = $_GET["name"];

echo "URL: " + $pageURL + " NAME: " + $pageName;


// Get current file path
$dir = dirname(__FILE__);
$distLoc = $dir . "/dist/" . $pageName;
$localDir = "/dist" . $pageName;

// Remove dist
delete_files($localLoc);
// Create dist loc
mkdir($localLoc, 0777, true);


// Set Source
$source_lines = file($pageURL);

// Define search params
$fileLinePattern = "/(src|href)=\".*.(js|css).*/";
// $fileLinePattern = "/(src|href)=\"[^\"]+\.(js|css)(\?[^\"])?/\"";
$fileNamePattern = "/([a-z0-9_.-]*[\/]?)$/";
$pageLinkPattern = "/<a.*href=\".*\">.*<\/a>/";


// Delete index.html
delete_files($localLoc . "/page.html"); 
// Create index.html
$htmlFile = fopen($localLoc . "/page.html", 'w'); 

// Check page lines for src/href info
foreach ($source_lines as $line_num => $line)
{    

    // Create variable of raw line to use as reference.
    $rawLine = $line;

    // Start detecting lines with files
    if(preg_match( $fileLinePattern, $line)){

        // Strip URL from line 
        preg_match('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $line, $match);

        // Identify if null 
        if ($match == null ){
            // echo "Null  on " . htmlspecialchars($line) . "<br>";
            // Write line to index file
            fwrite($htmlFile, $line);
        }
        else {

            // Get File name from path, removing query params
            $http = parse_url($match[0], PHP_URL_SCHEME);
            $url = parse_url($match[0], PHP_URL_HOST);
            $path = parse_url($match[0], PHP_URL_PATH);
            // Create URL
            $url = $http . "://" . $url . $path;

            // Get file name
            preg_match($fileNamePattern, $url, $fileName);

            // log File urls and file name
            // echo "<pre>LOG: ";
            // print($url . " <strong> File name: " . $fileName[1] . "</strong>");
            // echo "</pre>";

            //Print files

            // Set URL to pull file contents 
            $fileLines = file($url);

            // Add dist to file name 
            $fileLoc = $distLoc . $fileName[1];


            // After generating file, write line to HTML with replaced link source
            $replacedLine = str_replace($url, $fileName[1], $rawLine) ;

            // Write line to html file with replaced source 
            fwrite($htmlFile, $replacedLine);
        }
    }
    else if (preg_match($pageLinkPattern, $line) && preg_match('/src=".*(svg|png|jpg)/', $line) == false ) {

        // Get elements from line. 
        preg_match('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $line, $urlReplace);

        // Get URL Path to use as Page Name
        $nameReplace = parse_url($urlReplace[0], PHP_URL_PATH);
        
        // Generate line 
        $replacementURL = "../getPage.php?url=" . $urlReplace[0] . "&name=" . $nameReplace;

        // Replace line
        $replacementLine = preg_replace('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $replacementURL, $line);

        // echo $replacementURL . "<br>";

        // Write replaced line to file
        fwrite($htmlFile, $replacementLine);
    }
    else {
        // If no link detected, write line to HTML file.
        fwrite($htmlFile, $line);
    }

}

// Close HTML file
fclose($htmlFile);
print("<h1>Navigate to page: </h1><a href='");
print($localDir . "/page.html");
print("'>Click here</a>");

// log echo finished
echo "<br>LOG: Finished writing files at " . date('d-m-Y h:i:s A') . "<br><br>";


// Delete files function
function delete_files($target) {
    if(is_dir($target)){
        $files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned

        foreach( $files as $file ){
            delete_files( $file );      
        }

        rmdir( $target );
    } elseif(is_file($target)) {
        unlink( $target );  
    }
}

?>


