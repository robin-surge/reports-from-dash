<?php
require_once './mail_helper.php';
require_once './configuration_handler.php';
require_once './API_calls_and_parsing.php';
require_once './reportFile_generator.php';

createReports();

function createReports()
{
    loadConfig();
    global $config;
    foreach ($config as $domain => $settings) {
        if (!$config[$domain]) {
            continue;
        }

        $view = getView($domain);

        if (!$view) {
            continue;
        } elseif (reportIsDue($domain, $view)) {
            $params = getParamsFromView($view);
            $data = getReportDataWithParams($domain, $params);
            generateReportFile($data);
            sendReport($domain);
            updateConfigWithNewTS($domain);
        } else {
            trigger_error("There's no report due for $domain at this time");
        }
    }
    writeUpdatedConfigAndQuit();
}

function reportIsDue($domain, $view)
{
    global $config;
    $freq = $config[$domain]["freq"];
    $ts = (int)$config[$domain]["ts"];
    if ($freq === null || $ts === null) {
        $config[$domain]["freq"] = $view->name;
        $config[$domain]["ts"] = time();
        return true;
    }
    if (strpos($freq, "daily") !== false
        && (time() > $ts + (24 * 60 * 60))) {
        return true;
    } elseif (strpos($freq, "weekly") !== false
        && (time() > $ts + (7 * 24 * 60 * 60))) {
        return true;
    } else {
        return false;
    }
}

function sendReport($domain)
{
    global $config;
    $my_file = REPORT_FILENAME;
    $my_name = "Surge Team";
    $my_mail = "team@surge.io";
    $my_replyto = "robin@surge.io";
    $my_subject = "Your report for " . $domain;
    $my_message = wordwrap("Hi,\n\nHere's your Surge report for $domain. You can change the frequency of your reports by naming your report view either dailyReport or weeklyReport.\n\nHave a nice day!\n\nKind regards,\nSurge.io Team");
    if (!mail_attachment($my_file, $config[$domain]["email"], $my_mail, $my_name, $my_replyto, $my_subject, $my_message)) {
        trigger_error("Sending report via email to {$config[$domain]["email"]} failed!");
    } else{
        print_r("report for $domain successfully sent to {$config[$domain]["email"]}!");
    }
}