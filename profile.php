<?php
header('Content-Type: application/json');
include 'autoload.php';
$table = 'supersql_profiler_test_table';


$con = new \SuperSQL\SQLHelper('localhost','mysql','root','54757');

$r = $con->create($table,array(
    'id' => 'int',
    'string' => 'varchar(30)',
    'json' => 'varchar(100)',
    'group' => 'int'
));

if ($r->error()) {
    $con->delete($table);
}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
function generateRandomArray() {
    $len = rand(3,7);
    $arr = array();
    for ($i = 0; $i < $len; $i++) {
        array_push($arr,rand(0,100));
    }
    return $arr;
}
$final = array();
for ($j = 0; $j < 30; $j++) {
$out = array();

$toInsert = array();
for ($i = 0; $i < 100; $i ++) {
    array_push($toInsert,array(
        'id' => $i + 1,
        'string' => generateRandomString(),
        'json' => generateRandomArray(),
        'group' => rand(0,5)
    ));
}

$start = microtime(true);

// INSERT 1 ROW
$insert1s = microtime(true);
$insert1r = $con->insert($table,array(
    'id' => 0,
    'string' => 'hello world',
    'json[json]' => array(1,2,3,4,5),
    'group' => 0
));
$insert1e = microtime(true);
// END

// INSERT 100 ROWS USING TEMPLATE
$inserts = microtime(true);
$insertr = $con->insert($table,array(
    array(
        'id[int]',
        'string[str]',
        'json[json]',
        'group[int]'
    ),
    $toInsert
    )
);
$inserte = microtime(true);
// END

// SELECT ALL ROWS
$selects = microtime(true);
$selectr = $con->select($table);
$selecte = microtime(true);

// SELECT ALL WHILE PARSING JSON
$select2s = microtime(true);
$select2r = $con->select($table,array('*','json[json]'));
$select2e = microtime(true);

// SELECT ROWS WITH GROUP = 0
$select3s = microtime(true);
$select3r = $con->select($table,array(),array('group' => 0));
$select3e = microtime(true);


// UPDATE 1 ROW

$updates = microtime(true);
$updater = $con->update($table,array(
    'id' => 0,
    'string' => 'goodbye',
    'json[json]' => array(5,4,3,2,1),
    'group' => 0
),array('id' => 0));
$updatee = microtime(true);


// DELETE ALL ROWS
$deletes = microtime(true);
$deleter = $con->delete($table);
$deletee = microtime(true);
// END


$end = microtime(true);
$out['insert1'] = array(
    'start' => $insert1s,
    'end' => $insert1e,
    'error' => json_encode($insert1r->error()),
    'rows' => $insert1r->rowCount()
);

$out['insert'] = array(
    'start' => $inserts,
    'end' => $inserte,
    'error' => json_encode($insertr->error()),
    'rows' => $insertr->rowCount()
);

$out['select'] = array(
    'start' => $selects,
    'end' => $selecte,
    'error' => json_encode($selectr->error()),
    'rows' => $selectr->rowCount()
);

$out['select2'] = array(
    'start' => $select2s,
    'end' => $select2e,
    'error' => json_encode($select2r->error()),
    'rows' => $select2r->rowCount()
);

$out['select3'] = array(
    'start' => $select3s,
    'end' => $select3e,
    'error' => json_encode($select3r->error()),
    'rows' => $select3r->rowCount()
);

$out['update'] = array(
    'start' => $updates,
    'end' => $updatee,
    'error' => json_encode($updater->error()),
    'rows' => $updater->rowCount()
);

$out['delete'] = array(
    'start' => $deletes,
    'end' => $deletee,
    'error' => json_encode($deleter->error()),
    'rows' => $deleter->rowCount()
);
$out['start'] = $start;
$out['end'] = $end;
array_push($final,$out);
}
$con->drop($table);
echo json_encode(array('data' => $final, 'version' => phpversion()));
?>
