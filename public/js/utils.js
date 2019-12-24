
function removeElement(id) {
  if(document.getElementById(id)){
    var elem = document.getElementById(id);
    return elem.parentNode.removeChild(elem);
  }
}

function removeElementAndChilds(id){
  const parent = document.getElementById(id);
  while (parent.firstChild) {
    parent.removeChild(parent.firstChild);
  }
}

function getFormattedDate(date) {
  var year = date.getFullYear();

  var month = (1 + date.getMonth()).toString();
  month = month.length > 1 ? month : '0' + month;

  var day = date.getDate().toString();
  day = day.length > 1 ? day : '0' + day;

  return day + '/' + month + '/' + year;
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
