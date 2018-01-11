<?php
/* .env contains two constants: The JWT and an array DOMAINS with the domain(s) for which reporting should be enabled */
require './.env';

function createReports(){
    foreach(DOMAINS as $domain) {
        $view = getView($domain);
        $params = getParamsFromView($view);
        $data = getDataWithParams($domain, $params);
        prepReportFile($data);
        sendReport();
    }
}

createReports();

function getView($domain){
    return sendRequest($domain,'userdata', null);
}

function getParamsFromView($view){
    $views = $view->results->data->views;
    $reportView = null;
    foreach($views as $v){
        if($v->name === 'report'){
            $reportView = $v;
        }
    }
    if(!$reportView){
        trigger_error("No report view for ".$views[0]->domain);
        return false;
    }
    $range = $reportView->baseRange;
    $comparison = $reportView->comparisonRange;
    $metrics = $reportView->metrics;
    $sortedMetric = $reportView->sortedMetric;
    $dimensions = $reportView->dimensions;
    $timezone = $reportView->timezone;
    $parsedView = array(
        "base_timeframe"=>$range,
        "comparison_timeframe"=>$comparison,
        "timezone"=>$timezone,
        "metrics[]"=>$metrics,
        "order_by"=>$sortedMetric,
        "dimensions[]"=>$dimensions
    );
    return $parsedView;
}

function getDataWithParams($domain, $parsedView){
    return sendRequest($domain,'metrics', $parsedView);
}

function prepReportFile($data){}

function sendReport(){}

function sendRequest($domain,$endpoint,$params){
    if($params === null){
        $paramString = '';
    }
    else{
        $paramString = '?'.http_build_query($params);
    }
    $url = 'http://api-analytics-beta.cloudwp.io/'.$domain.'/'.$endpoint.$paramString;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'authorization: Bearer '.JWT,
        'accept: application/json',
        'authority: api-analytics-beta.cloudwp.io'
        )
    );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);

    if(curl_errno($ch)){
        trigger_error("Error performing HTTP request to API: ".curl_error($ch));
        curl_close($ch);
        return false;
    }
    else{
        $data = json_decode($res);
        curl_close($ch);
        return $data;
    }
}