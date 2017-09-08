<?php
require __DIR__.'/vendor/autoload.php';
$pdo = new PDO('sqlite:tree.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if(isset($_POST['item'])) {
	deleteItem(strip_tags($_POST['item']));
	exit();
}

if(isset($_POST['tags'])) {
	$tree = json_decode($_POST['tags']);
	clearTable();
	$iterator = new RecursiveArrayIterator($tree);
	iterator_apply($iterator, 'save', array($iterator));
	exit();
}


function save($iterator) {

		$title = null;
		$parent = null;
		while($iterator->valid()) {

			if ($iterator->hasChildren()) {
				save($iterator->getChildren());
			} else {

				if ($iterator->key() == "title") {
					$title = $iterator->current();
				} 
				if ($iterator->key() == "parent") {
					$parent = $iterator->current();
				}

				if(isset($title) && isset($parent)) {
					if(checkIfItemExists($title)) {
						http_response_code(500);
						die();
					} else {
						insertItem($parent,$title);
					}
					
				}

			}
			$iterator->next();
		}
		
}

function insertItem($parent, $title) {
	global $pdo;
	$stmt = $pdo->prepare("insert into tree(id, parent, title) values(null,:parent,:title)");
	$stmt->bindParam(':parent', $parent);
	$stmt->bindParam(':title',$title);
	$stmt->execute();
}

function deleteItem($title) {
	global $pdo;

	if(hasChildren($title)) {
		$children = getChildren($title);
		foreach ($children as $key => $child) {
			$sql = "delete from tree where title = '$child'";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
		}

		$sql = "delete from tree where title = '$title'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();

	} else {
		$sql = "delete from tree where title ='$title'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
	}

}

function hasChildren($parent) {
	global $pdo;
	$sql = 'select count(*) as cnt from tree where parent="' . $parent . '"';
	$stmt = $pdo->query($sql);
	return $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
}

function getChildren($title) {
	global $pdo;
	$sql = "select * from tree where parent = '$title'";
	$stmt = $pdo->query($sql);

	if (!isset($children)) {
		static $children = array();
	}

	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		
		if(hasChildren($row['title'])) {
			array_push($children,$row['title']);
			getChildren($row['title'],$children);
		} else {
			array_push($children, $row['title']);
		}
	}
	return $children;
}

function checkIfItemExists($title) {
	global $pdo;
	$sql = 'select count(*) as cnt from tree where title="' . $title . '"';
	$stmt = $pdo->query($sql);
	return $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
}

function clearTable() {
	global $pdo;
	$sql = $pdo->prepare("delete from tree");
	$sql->execute();
}