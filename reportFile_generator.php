<?php
DEFINE("REPORT_FILENAME", "./report.csv");

function generateReportFile($data)
{
    $reportFile = fopen(REPORT_FILENAME, 'w');
    $metrics = $data->results->metrics;
    if (!$metrics) {
        trigger_error("The report view contains no metric values for a report");
        fclose($reportFile);
        return;
    }
    createTableHeader($metrics, $reportFile);
    createTableBody($metrics, $reportFile);
    fclose($reportFile);
}

function createTableHeader($metrics, $reportFile): void
{
    $header = '';
    $metricArr = $metrics[0]->metrics;
    while ($metric_name = current($metricArr)) {
        $header .= ',' . key($metricArr);
        if ($metric_name->comparison) {
            $header .= ",change";
        }
        next($metricArr);
    }
    fwrite($reportFile, $header . "\n");
}

function createTableBody($metrics, $reportFile): void
{
    foreach ($metrics as $metric) {
        $dimension = $metric->dimension->title;
        $values = '';
        foreach ($metric->metrics as $m) {
            $value = $m->base;
            if ($m->comparison) {
                $comparison = ',' . ($value - $m->comparison);
            } else {
                $comparison = '';
            }
            $values .= ',' . $value . $comparison;
        }
        fwrite($reportFile, $dimension . $values . "\n");
    }
}
