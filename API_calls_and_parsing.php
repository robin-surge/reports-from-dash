<?php
/*
.env contains the JWT as PHP constant and is thus not committed to GitHub
*/
require_once './.env';

function getView($domain)
{
    $userdata = sendRequest($domain, 'userdata', null);
    $views = $userdata->results->data->views;
    $reportView = null;
    foreach ($views as $v) {
        if ($v->name === 'dailyReport' || $v->name === 'weeklyReport') {
            $reportView = $v;
        }
    }
    if (!$reportView) {
        trigger_error("No report view for " . $views[0]->domain);
        return false;
    } else {
        return $reportView;
    }
}

function getParamsFromView($view)
{
    $range = $view->baseRange;
    $comparison = $view->comparisonRange;
    $metrics = $view->metrics;
    $sortedMetric = $view->sortedMetric;
    $dimensions = $view->dimensions;
    $timezone = $view->timezone;
    $parsedView = array(
        "base_timeframe" => $range,
        "timezone" => $timezone,
        "metrics" => $metrics,
        "order_by" => $sortedMetric,
        "dimensions" => $dimensions
    );
    if ($comparison !== 'none') {
        $parsedView["comparison_timeframe"] = $comparison;
    }
    return $parsedView;
}

function getReportDataWithParams($domain, $parsedView)
{
    return sendRequest($domain, 'analytics/metrics', $parsedView);
}

function sendRequest($domain, $endpoint, $params)
{
    if ($params === null) {
        $paramString = '';
    } else {
        $paramString = '?' . http_build_query($params);
    }
    $url = 'https://api-analytics-beta.cloudwp.io/' . $domain . '/' . $endpoint . $paramString;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'authorization: Bearer ' . JWT,
            'accept: application/json',
            'authority: api-analytics-beta.cloudwp.io'
        )
    );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);

    if (curl_errno($ch)) {
        trigger_error("Error performing HTTP request to API: " . curl_error($ch));
        curl_close($ch);
        return false;
    } else {
        $data = json_decode($res);
        curl_close($ch);
        return $data;
    }
}