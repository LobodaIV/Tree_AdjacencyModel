BEGIN TRANSACTION;
CREATE TABLE `tree` (
	`id`	INTEGER PRIMARY KEY AUTOINCREMENT,
	`parent_id`	INTEGER,
	`text`	TEXT
);
INSERT INTO `tree` (id,parent_id,text) VALUES (2288,0,'root1');
INSERT INTO `tree` (id,parent_id,text) VALUES (2289,2288,'item1');
INSERT INTO `tree` (id,parent_id,text) VALUES (2290,2288,'item2');
INSERT INTO `tree` (id,parent_id,text) VALUES (2291,0,'root3');
INSERT INTO `tree` (id,parent_id,text) VALUES (2292,2291,'item4');
INSERT INTO `tree` (id,parent_id,text) VALUES (2293,2292,'item7');
INSERT INTO `tree` (id,parent_id,text) VALUES (2294,2291,'item5');
INSERT INTO `tree` (id,parent_id,text) VALUES (2295,2294,'item6');
INSERT INTO `tree` (id,parent_id,text) VALUES (2296,2295,'item10');
INSERT INTO `tree` (id,parent_id,text) VALUES (2297,2295,'item11');
COMMIT;
