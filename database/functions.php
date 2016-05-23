<?php

function esc_quot($fixstring = "") {
    $temp = preg_replace('/^\'/', '&lsquo;', $fixstring);
    $temp2 = preg_replace('/\'$/', '&rsquo;', $temp);
    $tempx = preg_replace('/ \'/', ' &lsquo;', $temp2);
    $temp3 = str_replace('\'', '&rsquo;', $tempx);
    $temp4 = preg_replace('/^\"/', '&ldquo;', $temp3);
    $temp5 = preg_replace('/\"$/', '&rdquo;', $temp4);
    $temp6 = preg_replace('/(\")([ .,;:!])/', '&rdquo;$2', $temp5);
    $temp7 = preg_replace('/ \"/', ' &ldquo;', $temp6);
    return $temp7;
}

function strip_zeros_from_date($marked_string = "") {
    // first remove the marked zeros
    $no_zeros = str_replace('*0', '', $marked_string);
    // then remove any remaining marks
    $cleaned_string = str_replace('*', '', $no_zeros);
    return $cleaned_string;
}

function redirect_to($location = NULL) {
    if ($location != NULL) {
        header("Location: {$location}");
        exit;
    }
}

function output_message($message = "") {
    if (!empty($message)) {
        return "<p class=\"message\">{$message}</p>";
    } else {
        return "";
    }
}

function __autoload($class_name) {
    $class_name = strtolower($class_name);
    $path = LIB_PATH . DS . "class.{$class_name}.php";
    if (file_exists($path)) {
        require_once($path);
    } else {
        die("The file class.{$class_name}.php could not be found.");
    }
}

function include_layout_template($template = "") {
    include(SITE_ROOT . DS . 'templates' . DS . $template);
}

function log_action($action, $message = "") {
    $logfile = SITE_ROOT . DS . 'logs' . DS . 'log.txt';
    $new = file_exists($logfile) ? false : true;
    if ($handle = fopen($logfile, 'a')) { // append
        $timestamp = strftime("%Y-%m-%d %H:%M:%S", time());
        $content = "{$timestamp} | {$action}: {$message}\n";
        fwrite($handle, $content);
        fclose($handle);
        if ($new) {
            chmod($logfile, 0755);
        }
    } else {
        echo "Could not open log file for writing.";
    }
}

function datetime_to_text($datetime = "") {
    $unixdatetime = strtotime($datetime);
    return strftime("%B %d, %Y at %I:%M %p", $unixdatetime);
}

?>