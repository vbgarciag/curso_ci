<?php

    require '../header_rest.php';
    $controllerRest = new ControllerRest();

    $api_key = "";
    if(!empty($_GET['api_key']))
        $api_key = $_GET['api_key'];

    $lat = 0;
    if(!empty($_GET['lat']))
        $lat = str_replace(",", ".", $_GET['lat']);

    $lon = 0;
    if(!empty($_GET['lon']))
        $lon = str_replace(",", ".", $_GET['lon']);

    $radius = 0;
    if(!empty($_GET['radius']))
        $radius = $_GET['radius'];

    $news_count = 0;
    if(!empty($_GET['news_count']))
        $news_count = $_GET['news_count'];

    $fetch_category = 0;
    if(!empty($_GET['fetch_category']))
        $fetch_category = $_GET['fetch_category'];

    if( empty($api_key) ) {
        $arr = array();
        $arr['status'] = formatStatus('3', 'Invalid Access.');
        echo json_encode($arr);
        return;
    }
    
    if($lat == 0 || $lon == 0 || $radius == 0) {
        $arr = array();
        $arr['status'] = formatStatus('3', 'Invalid Access.');
        echo json_encode($arr);
        return;
    }

    $results = $controllerRest->getResultStoresNearby($lat, $lon, $radius);

    $ind = 0;
    $arrayObjs = array();
    foreach ($results as $row) {
        $arrayObj = array();
        foreach ($row as $columnName => $field) {
            if(!is_numeric($columnName)) {
                $val = preg_replace('~[\r\n]+~', '', $field);
                $val = htmlspecialchars(trim(strip_tags($val)));
                $arrayObj[$columnName] = $val;
            }
        }

        $arrayObj['photos'] = array();
        $store_id = $arrayObj['store_id'];
        if( !empty($store_id) ) {
            $resultPhotos = $controllerRest->getResultPhotosByStoreId($store_id);
            $arrayObj['photos'] = getArrayObjs($resultPhotos);
        }

        $arrayObjs[$ind] = $arrayObj;
        $ind += 1;
    }


    $arrayNews = array();
    if($news_count > 0) {
        $resultNews = $controllerRest->getResultNewsAtMax($news_count);
        $arrayNews = getArrayObjs($resultNews);
    }

    $arrayCategories = array();
    if($fetch_category > 0) {
        $resultsCategories = $controllerRest->getResultCategories();
        $arrayCategories = getArrayObjs($resultsCategories);
    }

    $arrayJSON = array();
    $arrayJSON['stores'] = $arrayObjs;
    $arrayJSON['news'] = $arrayNews;
    $arrayJSON['categories'] = $arrayCategories;
    echo json_encode($arrayJSON);

    function formatStatus($status_code, $status_text) {
        $arr = array( 'status_code' => ''.$status_code.'', 'status_text' => ''.$status_text.'' );
        return $arr;
    }

    function getObj($results) {
        $arrayObj = array();
        foreach ($results as $row) {
            foreach ($row as $columnName => $field) {
                if(!is_numeric($columnName)) {
                    $arrayObj[$columnName] = $field;
                }
            }
        }
        return $arrayObj;
    }

    function getArrayObjs($results) {
        $ind = 0;
        $arrayObjs = array();
        foreach ($results as $row) {
            $arrayObj = array();
            foreach ($row as $columnName => $field) {
                if(!is_numeric($columnName)) {
                    $val = preg_replace('~[\r\n]+~', '', $field);
                    $val = htmlspecialchars(trim(strip_tags($val)));
                    $arrayObj[$columnName] = $val;
                }
            }
            $arrayObjs[$ind] = $arrayObj;
            $ind += 1;
        }
        return $arrayObjs;
    }

?>