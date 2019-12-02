function removeElement(id) {
  if(document.getElementById(id)){
    var elem = document.getElementById(id);
    return elem.parentNode.removeChild(elem);
  }
}

function addElement(parentId, elementTag, elementId)
{
  var p = document.getElementById(parentId);
  var newElement = document.createElement(elementTag);
  newElement.setAttribute('id', elementId);
  p.appendChild(newElement);
}

function showElement(parentId){
  var p = document.getElementById(parentId);
  p.style.display = "block";
}


function hideElement(parentId){
  var p = document.getElementById(parentId);
  p.style.display = "none";
}

function hex2dec(hex){
  return parseInt(hex,16);
}
