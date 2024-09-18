//*****************************************************//
// The following functions are maintained by Danni
addTile = (viewer, regionalTile) => {
    console.log("reginaltile", regionalTile)
    viewer.addTiledImage({
        tileSource: regionalTile,
        x: 0,
        opacity: 0.5
    });
}
//stop making requests
removeTile = (viewer, tileName) => {
    var count = viewer.world.getItemCount();
    for (i = 0; i < count; i++) {
        tiledImage = viewer.world.getItemAt(i);
        if (tiledImage.source.queryParams.input === tileName) {
            //set selected addedtileimage opacity to 0
            tiledImage.setOpacity(0);
            // viewer.world.removeItem(tiledImage);
            break;
        }
    }
}

function annotatorTreeBtnClick(annotationLayer, userIdNameMap, treeDomId, colorPalette){
    let api = annotationLayer.APIs.annoGetAnnotators;
    getAnnotators(api).then(annotatorIds => {
        let annotatorsMap = {};
        annotatorIds.forEach(key => {
            annotatorsMap[key] = userIdNameMap[key];
        });
        if (Object.keys(annotatorsMap).length === 0){
            $(treeDomId).html('No one or model has annotated this image yet. Click the model button above to start automatic annotation, or use the manual annotation tool to annotate manually.');
        } else{
            $(treeDomId).html("")
            populateJStree(annotationLayer, annotatorsMap, treeDomId, colorPalette)
        }
        
    });
}

function populateJStree(annotationLayer, annotatorsMap, treeDomId, colorPalette){
    const treeData = convertToJstreeData(annotatorsMap);
    if ($(treeDomId).jstree(true)){
        $(treeDomId).jstree('destroy');
    }
    $(treeDomId).jstree({
        "plugins": ['checkbox', "types"],
        "types":   {
            "default": {
                "icon": "",
            },
            "file": {
                "icon" : "fa fa-eye"
            }
        },
        "core": {
            "data":treeData
        },

        "state": {
                "opened": ["all"]
            }
    });
    $(treeDomId).on("ready.jstree", function (e, data) {
        let activeIds = annotationLayer.activeAnnotators;
        $(treeDomId).off("changed.jstree") //remove changed.jstree listener before checking previously checked node
        activeIds.forEach(function(value) {
            $(treeDomId).jstree(true).select_node(value);
            $(treeDomId).jstree(true).set_icon(value, "fas fa-eye");
        });
        handCheckboxChange(annotationLayer, treeDomId, annotatorsMap, colorPalette); //add back listener
    });
}


function handCheckboxChange(annotationLayer, treeDomId, childrenNodeMap, colorPalette) {
//js tree check event
    var checkboxCooldown = false;
    $(treeDomId).on("changed.jstree", function (e, data) {
        var tablequery = {"annotator":[]};    
        //control users' clicking speed
        if (!checkboxCooldown) {
            checkboxCooldown = true;
            // Disable all checkboxes
            $('a.jstree-anchor').addClass('disabled-checkbox');
            setTimeout(function () {
                checkboxCooldown = false; // Reset cooldown flag after 1 second
                $('a.jstree-anchor').removeClass('disabled-checkbox');
                // var allNodes = $(treeDomId).jstree(true).get_json('#', { flat: true });
                var checkedNodes = data.instance.get_checked(true);
                // var checkedNodeIds = checkedNodes.map(node => node.id);
                var checkedChildrenNode = checkedNodes.filter(node => node.children.length == 0);   
                //make all children node icon to fa-eye-slash             
                Object.keys(childrenNodeMap).forEach(function(childNodeId) {
                    // Set icon for each child node
                    $(treeDomId).jstree(true).set_icon(childNodeId, "fas fa-eye-slash");
                });
                //update active annotators 
                var updatedNodeid = new Set();
                checkedChildrenNode.forEach(function (node) {
                    var currentIcon = $(treeDomId).jstree(true).get_icon(node.id);
                    if(currentIcon === "fas fa-eye-slash"){
                        $(treeDomId).jstree(true).set_icon(node.id, "fas fa-eye");
                    }
                    updatedNodeid.add(node.id);
                });
                //used to draw history annotation table (only children node under manual can be displayed in the table)
                checkedNodes.forEach(function (node) {
                    if (node.parent === 'manual' && node.children.length === 0) {
                        tablequery.annotator.push(node.id);
                    }
                });
                if (treeDomId === "#layers-left"){
                    drawNUpdateDatatable(annotationLayer.APIs.annoSearchAPI, tablequery, colorPalette);   
                }
             
                annotationLayer.updateAnnotators(updatedNodeid);

            }, 1000);
        }
   
    });
   
}

function convertToJstreeData(data) {
    var convertedData = [];
    console.log("tree data", data)
    // Create parent nodes
    var modelNode = {
        'id': 'model',
        'text': 'Model Annotation Display',
        'icon': 'fas fa-robot',
        'children': []
    };

    var manualNode = {
        'id': 'manual',
        'text': 'Manual Annotation Display',
        'icon': 'fas fa-user',
        'children': []
    };

    // Iterate over the input data object
    for (var key in data) {
        // Skip null and undefined values
        if (data[key] === null || typeof data[key] === 'undefined') {
            continue;
        }

        // Determine parent node based on key type
        var parentNode = null;
        if (!isNaN(key)) { // Check if key is a number
            parentNode = manualNode;
        } else if (typeof key === 'string') { // Check if key is a string
            parentNode = modelNode;
        }

        // Add a new node to the parent node's children array
        if (parentNode) {
            parentNode.children.push({
                'id': key,
                'text': data[key],
                'annotator': data[key],
                "icon": "fas fa-eye-slash"
            });
        }
    }

    // Add parent nodes to the converted data array
    if (modelNode.children.length > 0) {
        convertedData.push(modelNode);
    }
    if (manualNode.children.length > 0) {
        convertedData.push(manualNode);
    }

    return convertedData;
}

// Function to find the path by id
function getModelApiById(array, id) {
    for (var i = 0; i < array.length; i++) {
        if (array[i].id === id) {
            return array[i].path; // Return the path if id matches
        }
    }
    return null; // Return null if id is not found
}

function createOverlayElement(viewer) {
    var overlay = $('<div>').css({
        position: 'absolute',
        top: '0',
        right: '0',
        backgroundColor: '#fdf3d8',
        color: '#806520',
        padding: '0.75rem 1.25rem',
        borderRadius: '0.25rem',
        border: '1px solid #fceec9',
        zIndex: '9999',
        display: 'none' // Initially hide the overlay
    }).appendTo(viewer.container);
    
    return overlay;
}

function showInstructions(overlayElement, message) {
    // Display overlay with instructions
    if (!overlayElement.is(":visible")) {
        overlayElement.text(message).fadeIn();
    }
}

function hideInstructions(overlayElement) {
    // Hide overlay
    if (overlayElement.is(":visible")) {
        overlayElement.fadeOut();
    }
}

function object2w3c(selectedAnnObject){
    // konva shapes: rect, ellipse, circle, polygon
    const { id, label, annotator, shape, description} = selectedAnnObject;
    const bbox = {x0:selectedAnnObject.x0, y0: selectedAnnObject.y0, x1: selectedAnnObject.x1, y1: selectedAnnObject.y1 };

    let value, type;
    let rectflag = false;
    let labelArray = label.split(',');
    let taggingResult = [];
    if (labelArray.length > 0 && labelArray[0] !== "") {
        for (let item of labelArray) {
            taggingResult.push({
                "type": "TextualBody",
                "value": item.trim(),
                "purpose": "tagging"
            });
        }
    } 
    if (shape === "polygon") {
        const {points} = selectedAnnObject;
        const pointsString = points.reduce((acc, val, index, array) => {
            if (index % 2 === 0) {
                acc += val + ",";
            } else {
                acc += val + " ";
            }
 
            if (index === array.length - 1) {
                acc = acc.trim();
            }
 
            return acc;
        }, "");
 
        value = `<svg><polygon points="${pointsString}"></polygon></svg>`;
        type = "SvgSelector";
    } else if (shape === "ellipse") {
        const {x, y, radiusX, radiusY} = selectedAnnObject;
        type = "SvgSelector";
        value = `<svg><ellipse cx="${x}" cy="${y}" rx="${radiusX}" ry="${radiusY}"></ellipse></svg>`;
    } else if (shape === "circle") {
        const {x, y, radiusX, radiusY} = selectedAnnObject;
        type = "SvgSelector";
        value = `<svg><circle cx="${x}" cy="${y}" r="${radiusX}"></circle></svg>`;
    } else {
        rectflag = true;
        const {x, y, width, height} = selectedAnnObject;
        type = "FragmentSelector";
        value = `xywh=pixel:${x},${y},${width},${height}`
    }
 
    const w3cAnnotation = [
        {
            "type": "Annotation",
            "body": [
                {
                    "type": "TextualBody",
                    "value": description || '',
                    "purpose": "commenting"
                },
                // {
                //     "type": "TextualBody",
                //     "value": '',
                //     "purpose": "replying"
                // },
                ...taggingResult,
                // {
                //     "type" : "",
                //     "value" : bbox,
                //     "purpose" : "aiAssistant"
                // },
                {
                    "type" : "annotator",
                    "value" : annotator,
                    "purpose": "showannotator"
                }
 
            ],
            "target": {
                "selector": {
                    "type": type,
                    ...(rectflag ? {"conformsTo": "http://www.w3.org/TR/media-frags/"} : {}),
                    "value": value
                }
            },
            "id": `#${id}`
        }
    ];

    return w3cAnnotation;

}

/**********************************************/