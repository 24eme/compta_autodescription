<?php

$baseindexer = $argv[1];
$paths = array('Compta', '_Archives');



function mbstr2str($mbstr) {
    $cars = unpack('c*', $mbstr);
    if (count($cars) > 2 && $cars[1] != -2 && $cars[2] != -1) {
        return $mbstr;
    }
    $str = '';
    for ($y = 3 ; $y <= count($cars) ; $y ++) {
        if ($cars[$y]) {
            $str .= pack('c', $cars[$y]);
        }
    }
    return $str;
}

function get_exercice($date) {
    $c = explode('-', $date);
    if($c[1]> 7) {
        return $c[0];
    }
    return $c[0] - 1;
}

function get_pdf_prop($file)
{
    $f = fopen($file,'rb');
    if(!$f) {
        return false;
    }

    //Read the last 16KB
    fseek($f, -16384*2, SEEK_END);
    $s = fread($f, 16384*2);

    //Extract Info object number
    if(!preg_match('/Info ([0-9]+) /', $s, $a)) {
        return false;
    }
    $object_no = $a[1];

    //Extract Info object offset
    $lines = preg_split("/endobj[\r\n]+/", $s);
    foreach($lines as $l) {
        if (strpos($l, $object_no.' ') !== false) {
            $s_info = $l;
            break;
        }
    }
    //Extract properties
    if(!preg_match('/<<(.*)>>/Us', $s_info, $a)) {
        return false;
    }
    $n = preg_match_all('|/([a-z:]+) ?\(([^\)]*)\)|Ui', $a[1], $a);
    $prop = array('md5' => md5($s));
    $edited = false;
    for($i=0; $i<$n; $i++) {
        $key = $a[1][$i];
        if (strpos($key, ':')) {
            $edited = true;
        }
        $value = mbstr2str($a[2][$i]);
        if (mb_detect_encoding($value, ['UTF-8', 'ISO-8859-1']) == 'ISO-8859-1') {
            $value = utf8_encode($value);
        }
        $prop[$key] = $value;
    }

    if (!$edited) {
        return false;
    }
    return $prop;
}

function index_dir($path, $lastmod) {
    global $db;
    if ($dh = opendir($path)) {
        while (($file = readdir($dh)) !== false) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }
            $filepath = $path.'/'.$file;

            $stat = stat($filepath);
            if ($stat['mode'] & 0040000) {
                index_dir($filepath, $lastmod);
                continue;
            }
            if ($stat['mtime'] > $lastmod) {
                if (preg_match('/\.(pdf|jpg|png)$/i', $filepath, $m)) {
                    $extention = $m[1];
                    $md5 = null;
                    $meta = null;
                    switch ($extention) {
                        case 'pdf':
                            $meta = get_pdf_prop($filepath);
                            if (isset($meta['md5'])) {
                                $md5 = $meta['md5'];
                            }
                            break;
                        case 'png':
                        case 'jpg':
                        default:
                            // code...
                            break;
                    }
                    if (!$md5) {
                        $md5 = md5_file($filepath);
                    }
                    if (preg_match('/[^0-9](20[0-3][0-9])-?([01][0-9])-?([0-9][0-9])/', $filepath, $m)) {
                        $date = strtotime($m[1].'-'.$m[2].'-'.$m[3]);
                        if ($date) {
                            $stat['ctime'] = $date;
                        }
                    }
                    $res = $db->query("SELECT id FROM file WHERE fullpath = \"$filepath\";");
                    $row = null;
                    if ($row) {
                        $row = $res->fetchArray();
                    }
                    if (!$row || !$row[0]) {
                        $sql  = "INSERT INTO file (fullpath, filename, md5, size, mtime, ctime, extention) VALUES (\"$filepath\", \"$file\" ";
                        $sql .= isset($meta['md5']) ? ", \"".$meta['md5']."\"" : ', null';
                        $sql .= isset($stat['size']) ? ',  '.$stat['size']." " : ', null';
                        $sql .= isset($stat['mtime']) ? ', '.$stat['mtime'] : ', null';
                        $sql .= isset($stat['ctime']) ? ', '.$stat['ctime'] : ', null';
                        $sql .= ", \"".$extention."\"";
                        $sql .= ") ";
                        if (!$db->exec($sql)) {
                            print_r(['ERROR', $sql]);
                        }
                    }else{
                        $sql = "UPDATE file SET ";
                        $sql .= " filename = \"$file\", extention = \"".$extention."\" ";
                        $sql .= isset($stat['size']) ? ', size = '.$stat['size']." " : '';
                        $sql .= isset($stat['mtime']) ? ', mtime = '.$stat['mtime'] : '';
                        $sql .= isset($meta['md5']) ? ", md5 = '".$meta['md5']."'" : '';
                        $sql .= " WHERE fullpath = \"$filepath\";";
                        $db->exec($sql);
                    }
                    if ($meta) {
                        $res = $db->query("SELECT id FROM file WHERE fullpath = \"$filepath\";");
                        $row = null;
                        if ($row) {
                            $row = $res->fetchArray();
                        }
                        if (!$row || !$row[0]) {
                            $sql  = "INSERT INTO file (fullpath, filename, md5, size, mtime, ctime, extention) VALUES (\"$filepath\", \"$file\" ";
                            $sql .= isset($meta['md5']) ? ", \"".$meta['md5']."\"" : ', null';
                            $sql .= isset($stat['size']) ? ',  '.$stat['size']." " : ', null';
                            $sql .= isset($stat['mtime']) ? ', '.$stat['mtime'] : ', null';
                            $sql .= isset($stat['ctime']) ? ', '.$stat['ctime'] : ', null';
                            $sql .= ", \"".$extention."\"";
                            $sql .= ") ";
                            if (!$db->exec($sql)) {
                                print_r(['ERROR', $sql]);
                            }

                            $sql  = "INSERT INTO piece (fullpath, filename, md5) VALUES (\"$filepath\", \"$file\", ";
                            $sql .= isset($meta['md5']) ? "\"".$meta['md5']."\"" : 'null';
                            $sql .= ") ";
                            if (!$db->exec($sql)) {
                                print_r(['ERROR', $sql]);
                            }
                        }
                        $sql = "UPDATE piece SET ";
                        $sql .= " filename = \"$file\", extention = \"".$extention."\" ";
                        $sql .= isset($stat['size']) ? ', size = '.$stat['size']." " : '';
                        $sql .= isset($stat['mtime']) ? ', mtime = '.$stat['mtime'] : '';
                        $sql .= isset($meta['md5']) ? ", md5 = '".$meta['md5']."'" : '';
                        $sql .= isset($meta['facture:type']) ? ", facture_type = \"".$meta['facture:type']."\"" : '';
                        $sql .= isset($meta['facture:author']) ? ", facture_author = \"".$meta['facture:author']."\" " : '';
                        $sql .= isset($meta['facture:client']) ? ", facture_client = \"".$meta['facture:client']."\" " : '';
                        $sql .= isset($meta['facture:identifier']) ? ", facture_identifier = \"".$meta['facture:identifier']."\" " : '';
                        $sql .= isset($meta['facture:date']) ? ", facture_date = \"".$meta['facture:date']."\" " : '';
                        $sql .= isset($meta['facture:libelle']) ? ", facture_libelle = \"".str_replace(['"', '\\'], '', $meta['facture:libelle'])."\" " : '';
                        $sql .= isset($meta['facture:HT']) && $meta['facture:HT'] ? ', facture_prix_ht = '.str_replace(',', '.', $meta['facture:HT'])." " : '';
                        $sql .= isset($meta['facture:TVA']) && $meta['facture:TVA'] ? ', facture_prix_tax = '.str_replace(',', '.', $meta['facture:TVA'])." " : '';
                        $sql .= isset($meta['facture:TTC']) && $meta['facture:TTC'] ? ', facture_prix_ttc = '.str_replace(',', '.', $meta['facture:TTC'])." " : '';
                        $sql .= isset($meta['facture:devise']) ? ", facture_devise = \"".$meta['facture:devise']."\" " : '';
                        $sql .= isset($meta['paiement:comment']) && $meta['paiement:comment'] ? ", paiement_comment = \"".str_replace(['"'], '', $meta['paiement:comment'])."\" " : '';
                        $sql .= isset($meta['paiement:proof']) ? ", paiement_proof = \"".str_replace(['"'], '', $meta['paiement:proof'])."\" " : '';
                        $sql .= isset($meta['paiement:date']) ? ", paiement_date = \"".$meta['paiement:date']."\" " : '';
                        $sql .= isset($meta['facture:date']) ? ", exercice_comptable = \"".get_exercice($meta['facture:date'])."\" " : '';
                        $sql .= " WHERE fullpath = \"$filepath\";";
                        if (!$db->exec($sql)) {
                            print_r([$filepath, $file, $extention, $stat['size'], $stat['mtime'], $meta, $sql]);
                        }
                    }
                }
            }
        }
        closedir($dh);
    }
}

function index_banque($banque_csv, $lastmod) {
    global $db;
    $imported_at = date('r', $lastmod);
    $csv = explode("\n", file_get_contents($banque_csv));
    sort($csv);
    foreach($csv as $line) {
        $data = str_getcsv($line);
        if ($data[0] == 'date' || count($data) < 7) {
            continue;
        }
        $res = $db->query("SELECT id FROM banque WHERE date = \"".$data[0]."\" AND raw = \"".$data[1]."\";");
        $row = null;
        if ($res) {
            $row = $res->fetchArray();
        }
        if (!$row || !$row[0]) {
            $sql  = "INSERT INTO banque (date, raw, imported_at) VALUES (\"$data[0]\", \"".$data[1]."\" , \"".$imported_at."\") ";
            if (!$db->exec($sql)) {
                print_r(['ERROR', $sql]);
            }
        }
        $sql = "UPDATE banque SET ";
        $sql .= 'amount = '.$data[2].', ';
        $sql .= 'type = "'.$data[3].'", ';
        $sql .= 'banque_id = "'.$data[4].'", ';
        $sql .= 'rdate = "'.$data[5].'", ';
        $sql .= 'vdate = "'.$data[6].'", ';
        $sql .= 'label = "'.$data[7].'" ';
        $sql .= " WHERE date = \"".$data[0]."\" AND raw = \"".$data[1]."\";";
        $db->exec($sql);
        if (!$db->exec($sql)) {
            print_r(['ERROR', $sql]);
        }
    }
}

function consolidate($lastmod) {
    global $db;
    $res = $db->query("SELECT id, date, raw, label FROM banque WHERE piece_id is null");
    $proof2banqueid = array();
    $md52pieceid = array();
    while ($row = $res->fetchArray()) {
        if ($row['raw']) {
            $proof2banqueid[$row['raw'].'ø'.$row['date']] = $row['id'];
        }
        if ($row['label']) {
            $proof2banqueid[$row['label'].'ø'.$row['date']] = $row['id'];
        }
    }
    $sql = "SELECT id, paiement_proof, paiement_date, fullpath, md5 FROM piece ";
    if ($lastmod) {
        $sql .= " WHERE mtime >= $lastmod";
    }
    $res = $db->query($sql);
    while ($row = $res->fetchArray()) {
        $md52pieceid[] = ['md5' => $row['md5'], 'id' => $row['id'], "fullpath" => $row['fullpath'] ];
        $banqueid = null;
        if (isset($proof2banqueid[$row['paiement_proof'].'ø'.$row['paiement_date']])) {
            $banqueid = $proof2banqueid[$row['paiement_proof'].'ø'.$row['paiement_date']];
        } else {
            $ids = array();
            foreach($proof2banqueid as $bproof => $bid) {
                $r = explode('ø', $bproof);
                if (strpos($r[0], $row['paiement_proof']) !== false || strpos($row['paiement_proof'], $r[0]) !== false) {
                    if ($r[1] == $row['paiement_date']) {
                        $ids[] = $bid;
                    }
                }
            }
            if (count($ids) == 1) {
                $banqueid = $ids[0];
            }
        }
        if ($banqueid) {
            $sql = "UPDATE piece SET banque_id = $banqueid WHERE id = ".$row['id'];
            $db->exec($sql);
            $sql = "UPDATE banque SET piece_id = ".$row['id']." WHERE id = ".$banqueid;
            $db->exec($sql);
        }
    }
    foreach($md52pieceid as $piece) {
        $sql = "UPDATE file SET piece_id = ".$piece['id']." WHERE md5 = \"".$piece['md5'].'" AND fullpath = "'.$piece['fullpath'].'"';
        $db->exec($sql);
    }
}


$db_real = new SQLite3('db/database.sqlite');
$db = new SQLite3(':memory:');
$db_real->backup($db);
$db_real->close();

$db->exec("CREATE TABLE piece (
    id INTEGER PRIMARY KEY,
    filename TEXT,
    fullpath TEXT UNIQUE,
    extention TEXT,
    size INTEGER,
    mtime INTEGER,
    md5 TEXT,
    facture_type TEXT,
    facture_author TEXT,
    facture_client TEXT,
    facture_identifier TEXT,
    facture_date DATE,
    facture_libelle TEXT,
    facture_prix_ht FLOAT,
    facture_prix_tax FLOAT,
    facture_prix_ttc FLOAT,
    facture_devise TEXT,
    paiement_comment TEXT,
    paiement_date DATE,
    paiement_proof TEXT,
    banque_id INTEGER,
    exercice_comptable TEXT,
    CONSTRAINT constraint_name UNIQUE (md5)
)");

$db->exec("CREATE TABLE file (
    id INTEGER PRIMARY KEY,
    filename TEXT,
    fullpath TEXT UNIQUE,
    extention TEXT,
    size INTEGER,
    ctime INTEGER,
    mtime INTEGER,
    md5 TEXT,
    piece_id INTEGER
)");

$db->exec("CREATE TABLE banque (
    id INTEGER PRIMARY KEY,
    date DATE,
    mtime INTEGER,
    raw TEXT,
    amount FLOAT,
    type TEXT,
    banque_id TEXT,
    rdate DATE,
    vdate DATE,
    label TEXT,
    piece_id INTEGER,
    imported_at INTEGER,
    CONSTRAINT constraint_name UNIQUE (date, raw)
)");


$res = $db->query("select mtime - 1 from piece ORDER BY mtime DESC LIMIT 1;");
$row = null;
if ($res) {
    $row = $res->fetchArray();
}
$lastmod = null ;
if ($row && $row[0]) {
    $lastmod = $row[0];
}
foreach($paths as $path) {
    index_dir($baseindexer . '/' . $path, $lastmod);
}

index_banque('https://raw.githubusercontent.com/24eme/banque/master/data/history.csv', $lastmod);

consolidate($lastmod);

$db_real = new SQLite3('db/database.sqlite');
$db->backup($db_real);
