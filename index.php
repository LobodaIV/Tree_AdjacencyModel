<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <style>
    ul {
        list-style: none;
    }

    .nodes .node {
        color: red;
        background-image: url("plus.gif");
        background-position: left center;
        background-repeat: no-repeat;
        background-size: 10px;
        padding-left: 12px;
    }

    .nodes .open {
        background-image: url("minus.gif");
    }
    
    .nodes ul li {
        background-image: url(treeview-default-line.gif);
        background-repeat: no-repeat;
        padding-left: 20px;
        margin-left: -42px;
    }
    .nodes li > span {
        display: inline-block;
    }
    </style>
</head>
<body>
<div class="notif">You can rename element by double click.</div>
<div class="result"></div>
<div class="nodes">
<?php 
    error_reporting(E_ALL);
    include("BuildTree.php");
    $nodes = fetchNodes();
    echo get_tree($nodes,'');
?>
</div>

<button id="save">Save</button>
<button id="add">Add new branch</button>
<button id="trash">To remove drop here</div>
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>

function Tree(ul, parent){
    var tags = [];
    ul.children("li").each(function(){
        
        var subtree = $(this).children("ul");
        var node = {
            "title": $(this).attr("id"),
        };

        if( typeof parent !== undefined ) {
                node.parent = parent;
        }

        if ($(this).parent().attr("class") == "tree") {
            node.parent = "";
        }

        if(subtree.length > 0) {
            parent = $(this).attr("id");
            node.children = Tree(subtree,parent);
            tags.push(node);
        } else {
            tags.push(node);
        }

    });
    return tags;
}

$(document).ready(function(){
    
    run();

    function addBranch(ul) {
        elem = $(ul);
        var newElem = "<li id='new_root' class='tree_item'><label></label><span>new root</span>"
            + "<ul>"
            +    "<li id='new_branch' class='tree_item'><label></label><span>new branch</span>"
            +        "<ul>"
            +            "<li id='new_item' class='tree_item'><span>new item</span></li>"
            +        "</ul>"
            +    "</li>"
            +"</ul>"
            +"</li>";

        $(elem).append(newElem);
        run();

    }

    function runDroppable() {
        $("li.tree_item span, #trash").droppable({
            tolerance: "pointer",
            hoverClass: "tree_hover",
            cancel: '.editable',
            drop: function(event, ui){

		        if(event.target.id == "trash") {
                    droppedId = $(ui.draggable).attr("id");
                    $("#" + droppedId).hide();
                    $.ajax({
                        method: "POST",
                        url: "/tree/Tree.php",
                        data: {item:droppedId},
                        success: function(data) {
                            $(".result").html("Removed");
                            hideMsg();
                            $(".tree").slideUp();
                            $(".tree").slideDown();
                        }
                    });
                }

                var dropped = ui.draggable; //элемент который будем двигать
                dropped.css({top: 0, left: 0});
                var me = $(this).parent();//тэг span элемента на который будет выполнен drop

                if (me == dropped) {
                    return;
                }

                var subbranch = $(me).children("ul");

                if( subbranch.length == 0 && !(event.target.id == "trash") ) {
                    
                    me.find("span").after("<ul></ul>");//добавить новый элемент ul сразу после span
                    subbranch = me.find("ul"); //получаем доступ к созданному элементу ul
                }

                var oldParent = dropped.parent();
                subbranch.eq(0).append(dropped);
                
                //проверяем если есть label чтобы добавить иконку сворачивания
                if(!subbranch.siblings("label.node").length) {
                    subbranch.siblings("span").before("<label></label>");
                }

                var oldBranches = $("li", oldParent);
                if (oldBranches.length == 0) {
                    //уделение root если нет элементов внутри
                    $(oldParent).closest("li.tree_item").get(0).id;
                    var toRemove = $(oldParent).closest("li.tree_item").get(0).id;
                    $("#" + toRemove).remove();

                }

                runFolding();
            }
        });
    }

    function runDraggable() {
        $("li.tree_item").draggable({
            opacity: 0.5,
            revert: true,
            distance: 5,
        });
    }

    function makeEditable() {
        $('span').on('dblclick', function() {
            $(this).focus().attr('contentEditable', true).addClass("editable");
        }).blur(
            function() {
                $(this).attr('contentEditable', false).removeClass("editable");
                //remove white spaces
                var text = $(this).text();
                var newText = text.replace(/\s+/g,'_');
                $(this).parent().attr("id", newText);
        });
    }

    function prepareFolding(ul) {
        $(ul).children().each(function() {
            if ($(this).has("label").has("span").has("ul").length > 0) {
                $(this).children("label").addClass("node");
                subtree = $(this).children("ul");
                prepareFolding(subtree);
            }
        });
    }

    function makeFolding(elem) {

        $("*").find(".node").off('click');
        
        $("*").find(".node").on('click', function() {
            $(this).next().next("ul").toggle();
            $(this).toggleClass('open');
        });

    }

    function runFolding() {
        prepareFolding($("#root"));
        makeFolding();
    }

    function run() {
        $(".nodes").children("ul").attr("id","root").attr("class","tree");
        runDroppable();
        runDraggable();
        runFolding();
        makeEditable();
    }

    function hideMsg() {
        var msg = $(".result");
        if (msg.html().length > 0) {
            msg.css("display","block");
            msg.hide(2500);
        }
    }

    $("#add").on('click',function(e) {
        e.stopPropagation();
        e.preventDefault();
        addBranch($(".tree"));
    });

    //save to Database
    $("#save").on('click',function(){
        var tree = JSON.stringify(Tree($("#root")));
        $.ajax({
            method: "POST",
            url: "/tree/Tree.php",
            cache: false,
            data: {tags:tree},
            success: function(data) {
                $(".result").html("Saved");
                hideMsg();
                $(".tree").slideUp();
                $(".tree").slideDown();
            },
            statusCode: {
                500: function() {
                    alert("You can't add elements with same name. Please rename new elements!");
                }
            },
        });

    });



});

</script>
</body>
</html>
