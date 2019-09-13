<?php
/**
 * Created by PhpStorm.
 * User: AndriiK
 * Date: 08.11.2018
 * Time: 16:03
 */

$start_time = microtime(true);
$max_exec_time = max(300, @ini_get("max_execution_time"));
if(empty($max_exec_time)) {
    $max_exec_time = 300;
}
if(!empty($_SERVER['HTTP_USER_AGENT'])){
    session_name(md5($_SERVER['HTTP_USER_AGENT']));
}
session_start();
chdir('.');

$page = 1;
$limit = 100;

if (isset($_GET['page'])) {
    $page = max(1, $_GET['page']);
}
$start = ($page-1)*$limit;
$end = $limit*$page;

$html = '';
$header = '';

$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'? 'https' : 'http';
if($_SERVER["SERVER_PORT"] == 443)
    $protocol = 'https';
elseif (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1')))
    $protocol = 'https';
elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')
    $protocol = 'https';

$root_url = $protocol . '://' . $_SERVER['HTTP_HOST'];

$url = 'https://www.googleapis.com/pagespeedonline/v3beta1/optimizeContents?url=' . $root_url . $_SERVER['REQUEST_URI'] . '&strategy=desktop';

$html .= '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">';

// $html .= '<a href="' . $url . '" target="_blank">' . $url . '</a><br/>';

$tmp_files = glob('*.*');
$count = 0;
$count_per_page = 5;

if(is_array($tmp_files) && !empty($tmp_files)) {
    for ($i=$start; $i<$end; $i++) {
        if (isset($tmp_files[$i])) {
            $html .= '<img src="' . $tmp_files[$i] . '" alt="" /><br/>';
            $count++;
        } else continue;
    }
}

$total_pages = ceil(count($tmp_files) / $limit);

$header .= '<nav aria-label="Page navigation example">';
$header .= '<ul class="pagination">';

if ($total_pages > 1) {
    
    if ($page > 1) {
        $header .= '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
    }
    if (($page-1) > 1) {
        $header .= '<li class="page-item"><a class="page-link" href="?page=' . ($page-1) . '">...</a></li>';
    }
    
    $start_page = min($page, ($total_pages-$count_per_page-1));
    $last_page = min(($page+$count_per_page), $total_pages);
    
    for ($i=$start_page; $i<$last_page; $i++) {
        if ($page == $i) {
            $header .= '<li class="page-item disabled"><a class="page-link" href="#" tabindex="-1">' . $i . '</a></li>';
        } else {
            $header .= '<li class="page-item"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
        }
        if ($i > ($page+$count_per_page-2)) break;
    }
    
    if ($total_pages > $last_page && $last_page != $page) {
        $header .= '<li class="page-item"><a class="page-link" href="?page=' . $last_page . '">...</a></li>';
    }
    $header .= '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '">' . $total_pages . '</a></li>';
}

$header .= '<li class="page-item mx-1"><a class="btn btn-primary" href="' . $url . '" target="_blank">Download Optimize Images</a></li>';
$header .= '</ul>';
$header .= '</nav>';

print $header;

if (!$count) die('404');

print $html;
exit();
