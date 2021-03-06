<?php

include './functions.php';

function selectUser($search, $orderById = 'sir_id', $orderBy = 'desc', $limit = '10', $offset) {
    if ($orderById == 'srno') {
        $orderById = 'sir_id';
    }

    $searchString = '';

    if (isset($search) && $search != '') {
        $searchString = "AND (sir_heading_text LIKE '%$search%')";
    }

    $data = [];

    $result = [];

    $sliderId = '';
    if (isset($_GET['s_id']) && $_GET['s_id'] != '') {
        $sliderId = $_GET['s_id'];
    }

    $selectUser = "SELECT * FROM slider_img_relation WHERE sir_slider_id=$sliderId $searchString ORDER BY $orderById $orderBy LIMIT $limit offset $offset ";

    $query = mysql_query($selectUser);
    $totalRecord = totalUser();
    $i = 0;
    $k = $totalRecord - $offset;
    while ($row = mysql_fetch_assoc($query)) {
        $row['srno'] = $k;
        $k--;
        $data[$i] = $row;
        $i++;
    }



    $result['totalRecord'] = $totalRecord;

    $result['result'] = $data;

    return $result;
}

function totalUser() {

    $data = [];

    $sliderId = '';
    if (isset($_GET['s_id']) && $_GET['s_id'] != '') {
        $sliderId = $_GET['s_id'];
    }
    $selectUser = "SELECT * FROM slider_img_relation WHERE sir_slider_id=$sliderId";

    $query = mysql_query($selectUser);

    $i = 0;

    while ($row = mysql_fetch_assoc($query)) {

        $data[$i] = $row;

        $i++;
    }

    return count($data);
}

if (strrpos($_SERVER["REQUEST_URI"], '?')) {

    $subStr = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], '?') + 1);

    parse_str($subStr, $_REQUEST);
}

$searchText = isset($_REQUEST['search']['value']) ? $_REQUEST['search']['value'] : '';



$limit = isset($_REQUEST['length']) ? $_REQUEST['length'] : 5;

$orderById = 'slider_id';

$orderBy = 'asc';

if (isset($_REQUEST['order'])) {

    for ($i = 0, $ien = count($_REQUEST['order']); $i < $ien; $i++) {

        $columnIdx = intval($_REQUEST['order'][$i]['column']);

        $requestColumn = $_REQUEST['columns'][$columnIdx];

        $orderById = isset($requestColumn['data']) ? $requestColumn['data'] : 'u_id';

        $orderBy = 'DESC';

        if ($requestColumn['orderable'] == 'true') {

            $orderBy = $_REQUEST['order'][$i]['dir'] === 'asc' ?
                    'ASC' :
                    'DESC';
        }
    }
}

if (isset($_REQUEST['start'])) {

    $page = ($_REQUEST['start'] / $limit) + 1;

    $_GET['page'] = $page;
}



$result = selectUser($searchText, $orderById, $orderBy, $limit, $_REQUEST['start']);


$u = 0;

foreach ($result['result'] as $row) {
    $sliderId = '';
    if (isset($_GET['s_id']) && $_GET['s_id'] != '') {
        $sliderId = $_GET['s_id'];
    }
     if (isset($row['sir_created']) && $row['sir_created'] != '') {
        $result['result'][$u]['sir_created'] = date('d-m-Y', strtotime($row['sir_created']));
    }

    if (isset($row['sir_img']) && $row['sir_img'] != '') {
        $result['result'][$u]['sir_img'] = '<img src="uploads/slider/' . $row['sir_img'] . '" width="50" height="50">';
    }

    $result['result'][$u]['action'] = '<a href="javascript:;" onclick="deleteSliderImage(' . $row['sir_id'] . ',' . $row['sir_slider_id'] . ')" class="btn btn-danger btn-sm" title="Delete"><i class="fa fa-trash-o"></i></a>&nbsp;&nbsp;<a href="addslider_image.php?s_id=' . $sliderId . '&sir_id=' . $row['sir_id'] . '"  class="btn btn-default btn-sm" title="Edit"><i class="fa fa-pencil"></i></a>&nbsp;&nbsp;';
    $u++;
}



$result['limit'] = $limit;



$result['orderById'] = $orderById;



$result['orderBy'] = $orderBy;



$result['searchText'] = $searchText;

$result['draw'] = isset($_REQUEST['draw']) ? $_REQUEST['draw'] : 0;



$response = array('draw' => $result['draw'], 'recordsTotal' => $result['totalRecord'], 'recordsFiltered' => $result['totalRecord'], 'data' => $result['result']);



echo json_encode($response);

