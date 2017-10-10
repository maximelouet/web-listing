<?php

// Saumon directory listing
// https://github.com/maximelouet/web-listing
// MIT License (see "LICENSE" file)

$title = 'Saumon files';        // page title (used by the browser and search engines)
$css_file = 'listing.css';      // path to the CSS file used by the page
$dir = '.';                     // directory to scan ('.' is the current directory)
$ignore_hidden_files = true;    // ignore files starting with '.'
$ignore_directories = true;     // ignore subfolders of the $dir folder
$ignored_files = [ "index.php", "listing.php", "listing.css" ]; // array of other files to ignore
$allow_search_engines = false;  // whether to allow search engines (like Google) to index this page


?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($title, ENT_NOQUOTES); ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,700">
    <link rel="stylesheet" href="<?php echo $css_file; ?>">
    <meta name="robots" content="<?php echo get_search_engine_meta($allow_search_engines); ?>">
</head>
<body>
    
<?php

$files = get_files($dir, $ignore_hidden_files, $ignore_directories, $ignored_files);
print_files_table($files);

?>

</body>
</html>
<?php

function get_files($dir, $ignore_hidden_files, $ignore_directories, $ignored_files) {
    $d = new DirectoryIterator($dir) or die("Fatal error: failed opening directory $dir for reading.");
    $files = array();

    foreach($d as $fileinfo) {
        if (($fileinfo->isDot()) ||
            ($ignore_hidden_files && substr($fileinfo->getFilename(), 0, 1) == ".") ||
            ($ignore_directories && $fileinfo->isDir()) ||
            (in_array($fileinfo->getFilename(), $ignored_files)))
            continue;
        $files[] = array(
            'name' => "{$fileinfo}",
            'type' => ($fileinfo->getType() == "dir") ? "dir" : mime_content_type($fileinfo->getRealPath()),
            'size' => $fileinfo->getSize()
        );
    }

    usort($files, 'compare_by_name');
    return $files;
}

function print_files_table($files) {
    if (empty($files)) {
        echo "\t" . '<p>No files to show.</p>' . "\n";
        return;
    }

    $files_count = 0;
    $table = "\t<table>\n\t\t<tr>\n\t\t\t<th>Name</th>\n\t\t\t<th>Size</th>\n\t\t</tr>\n\n";

    foreach ($files as $file) {
      $files_count++;
      $name = $file['name'];
      $size = $file['size'];
      $type = get_type($file['type']);
      $human_size = get_human_size($size);
      $size_class = get_size_class($size);
      
      $table .= "\t\t" . '<tr class="' . $type . '">' . "\n";
      $table .= "\t\t\t" . '<td><a href="' . $name . '">' . $name . '</a></td>';
      $table .= '<td class="' . $size_class . '">' . $human_size . '</td>' . "\n";
      $table .= "\t\t" . '</tr>' . "\n";
    }
    $table .= "\t" . '</table>' . "\n";
    echo $table;

    print_footer($files_count);
}

function print_footer($files_count) {
    $files_text = ($files_count == 1 ? 'file' : 'files');

    echo "\n\t" . '<p>';
    echo $files_count;
    echo ' ';
    echo $files_text;
    echo '.</p>' . "\n";
}

function get_type($type) {
    switch ($type) {
        case 'application/pdf':
            return 'pdf';
            break;
        case 'application/vnd.oasis.opendocument.text':
        case 'application/msword':
        case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
            return 'doc';
            break;
        case 'application/vnd.oasis.opendocument.spreadsheet':
        case 'application/vnd.ms-excel':
        case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
            return 'calc';
            break;
        case 'application/vnd.oasis.opendocument.presentation':
        case 'application/vnd.ms-powerpoint':
        case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
            return 'pres';
            break;
        case 'application/zip':
        case 'application/gzip':
        case 'application/x-gzip':
            return 'archive';
            break;
        default:
            $typearr = explode("/", $type, 2);
            return $typearr[0];
            break;
    }
}

function get_size_class($size) {
    if ($size >= 52428800) // 50 MB
        return 'big';
    else if ($size >= 20971520) // 20 MB
        return 'middle';
    else
        return 'small';
}

function get_human_size($bytes, $decimals = 0) {
    $size = array('bytes','kB','MB','GB','TB','PB','EB','ZB','YB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
}

function compare_by_name($a, $b) {
    return strcmp($a["name"], $b["name"]);
}

function get_search_engine_meta($allow) {
    if ($allow)
        return 'index';
    else
        return 'noindex, nofollow';
}
