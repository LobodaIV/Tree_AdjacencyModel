<?php

$pdo = new PDO('sqlite:tree.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function get_tree($tree, $pid)
{
    $html = '';
 
    foreach ($tree as $row)
    {	
        if ($row['parent'] == $pid)
        {
            $html .= '<li class="tree_item" id="' . $row['title'] . '">';
            if (hasChildren($row['title']) > 0) {
            	$html .= '<label class="node"></label><span>';
            	$html .= str_replace("_"," ", $row['title']);
            	$html .= '</span>';
            } else {
            	$html .= '<span>';
            	$html .= str_replace("_"," ", $row['title']);
            	$html .= '</span>';
            }
            $html .= get_tree($tree, $row['title']);
            $html .= '</li>';
        }
    }
 
    return $html ? '<ul>' . $html . '</ul>' . "\n" : '';
}

function fetchNodes() {
	global $pdo;
	$sql = "select * from tree";
	$stmt = $pdo->query($sql);
	$nodes = array();
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$nodes[$row['id']]['parent'] = $row['parent'];
		$nodes[$row['id']]['title'] = $row['title'];
	}
	return $nodes;
}

function hasChildren($parent) {
	global $pdo;
	$sql = 'select count(*) as cnt from tree where parent="' . $parent . '"';
	$stmt = $pdo->query($sql);
	return $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
}