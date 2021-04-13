<?php

// Log page load time
date_default_timezone_set("Pacific/Auckland");
echo "LOG: loaded at " . date('d-m-Y h:i:s A') . "<br><br>";

// Set domain URL To fetch later
// $domain = 'https://www.comcom.govt.nz';
$domain = 'https://leaguefuel.netlify.app/';
$dirName = "dist";


// Get current file path
$dir = dirname(__FILE__);
$distLoc = $dir . "/$dirName/";

// Remove dist (if exists)
delete_files($distLoc);
// Create dist loc
mkdir($distLoc, 0777, true);

// get Data from domain
$source_lines = file($domain);

// Define search params
$fileLinePattern = "/(src|href)=\".*.(js|css).*/";
// $fileLinePattern = "/(src|href)=\"[^\"]+\.(js|css)(\?[^\"])?/\"";
$fileNamePattern = "/([a-z0-9_.-]*[\/]?)$/";
$pageLinkPattern = "/<a.*href=\".*\">.*<\/a>/";


// Delete index.html
// delete_files("dist/index.html"); 
// Create index.html
$htmlFile = fopen("dist/index.html", 'w'); 

// Check page lines for src/href info
foreach ($source_lines as $line_num => $line)
{    

    // echo "<br>Inside Foreach<br>";
    // Create variable of raw line to use as reference.
    $rawLine = $line;

    // Start detecting lines with files
    if(preg_match( $fileLinePattern, $line)){

        // Strip URL from line 
        preg_match('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $line, $match);

        // echo "<br>Inside Preg<br>";

        // Identify if null 
        if ($match == null ){


            // Check line for relative URL instead
            $relativeUrlRegex = "/(src|href)=\".*.(js|css).*/";
            preg_match($relativeUrlRegex, $line, $matchRel);

            if ($matchRel == null) {
                echo "No rel match";
            } else {
                echo "YES Rel Match $matchRel[0]";
            }


            // echo "Null  on " . htmlspecialchars($line) . "<br>";
            // Write line to index file
            fwrite($htmlFile, $line);
            // echo "<br>Inside null<br>";

        }
        else {
            // echo "<br>Inside else<br>";
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
            $fileLoc = 'dist/' . $fileName[1];

            // Open/Create File
            $file = fopen($fileLoc, 'w');
            // Loop through lines and write to file
            foreach ($fileLines as $line_num => $line) {
                fwrite($file, $line);
            }    
            // Close file
            fclose($file);

            // After generating file, write line to HTML with replaced link source
            $replacedLine = str_replace($url, $fileName[1], $rawLine) ;

            // Write line to html file with replaced source 
            fwrite($htmlFile, $replacedLine);
        }
    }
    else if (preg_match($pageLinkPattern, $line) && preg_match('/src=".*.(svg|png|jpg)/', $line) == false ) {

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

// header("Location: /$dirName");
// exit;

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


