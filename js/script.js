function showFields(value) {

    let inputBlocks = document.querySelectorAll(".formInputs");

    inputBlocks.forEach((el) => {
        
        let inputs = el.querySelectorAll("input");
        if (!el.classList.contains(value)) {
            inputs.forEach((input) => {
                input.disabled = true;
            });
            el.style.display = "none";
        } else {
            inputs.forEach((input) => {
                input.disabled = false;
            });
            el.style.display = "block";
        }
    });
}

function addInputsInsert() {
    let labelBlocks = document.querySelectorAll(".formInputs.insertValues label");
    console.log(labelBlocks);
    let button = document.getElementById("insertButton")   
    inputNameNumber = (labelBlocks.length / 2) + 1;
    for (let i = 0; i < 2; i++) {
        labelClone = labelBlocks[i].cloneNode(true); 
        input = labelClone.querySelectorAll("input");
        let inputName = "columnNameInsert";
        if (i == 1) {
            inputName = "columnValueInsert";
        }
        input[0].setAttribute("name", inputName + inputNameNumber);
        input[0].value = "";
        button.parentNode.insertBefore(labelClone, button);
    }
}
function addInputsUpdate() {
    let labelBlocks = document.querySelectorAll(".formInputs.updateValues .grid label");
    console.log(labelBlocks);
    let label = document.getElementById("updateButton")   
    inputNameNumber = (labelBlocks.length / 2) + 1;
    for (let i = 0; i < 2; i++) {
        labelClone = labelBlocks[i].cloneNode(true); 
        input = labelClone.querySelectorAll("input");
        let inputName = "columnNameUpdate";
        if (i == 1) {
            inputName = "columnValueUpdate";
        }
        input[0].setAttribute("name", inputName + inputNameNumber);
        input[0].value = "";
        label.parentNode.insertBefore(labelClone, label);
    }
}

function addInputsUpdateWhere() {
    let labelBlocks = document.querySelectorAll(".formInputs.updateValues .grid-3-col label");
    console.log(labelBlocks);
    let label = document.getElementById("updateWhereButton");
    inputNameNumber = (labelBlocks.length / 3) + 1;
    for (let i = 0; i < 3; i++) {
        labelClone = labelBlocks[i].cloneNode(true); 
        input = labelClone.querySelectorAll("input");
        let inputName = "conditionUpdate";
        if (i == 1) {
            inputName = "operationUpdate";
        }
        if (i == 2) {
            inputName = "valueUpdate"
        }
        input[0].setAttribute("name", inputName + inputNameNumber);
        input[0].value = "";
        label.parentNode.insertBefore(labelClone, label);
    }
}
function insertAfter(referenceNode, newNode) {
    referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
}
function addInputsDelete() {
    let labelBlocks = document.querySelectorAll(".formInputs.deleteValues .grid-3-col label ");
    console.log(labelBlocks);
    let label = document.getElementById("deleteButton")   
    inputNameNumber = (labelBlocks.length / 3) + 1;
    for (let i = 0; i < 3; i++) {
        labelClone = labelBlocks[i].cloneNode(true); 
        input = labelClone.querySelectorAll("input");
        let inputName = "conditionDelete";
        if (i == 1) {
            inputName = "operationDelete";
        } else if (i == 2) {
            inputName = "valueDelete";
        }
        input[0].setAttribute("name", inputName + inputNameNumber);
        input[0].value = "";
        label.parentNode.insertBefore(labelClone, label);
    }
}