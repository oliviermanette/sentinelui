/**
 * @desc remove specific element based on ID on html 
 * @param string id - the class to remove in a specific attribute
 * @return 
 */
function removeElement(id) {
  if (document.getElementById(id)) {
    var elem = document.getElementById(id);
    return elem.parentNode.removeChild(elem);
  }
}

/**
 * @desc remove specific element and all the childs based on ID on html 
 * @param string id - the class to remove in a specific attribute
 * @return 
 */
function removeElementAndChilds(id) {
  if (document.getElementById(id)) {
    const parent = document.getElementById(id);
    while (parent.firstChild) {
      parent.removeChild(parent.firstChild);
    }
  }
}

/**
 * @desc format a date to the following format DD/MM/YYYY
 * @param Date date - date to change format
 * @return string new format 
 */
function getFormattedDate(date) {

  var year = date.getFullYear();

  var month = (1 + date.getMonth()).toString();
  month = month.length > 1 ? month : '0' + month;

  var day = date.getDate().toString();
  day = day.length > 1 ? day : '0' + day;

  return day + '/' + month + '/' + year;
}

// parse a date in dd/mm/yyyy format
function parseDate(input) {
  var parts = input.split('/');
  console.log(parts);
  var date = new Date(parts[0], parts[1] - 1, parts[2]).toString(); // Note: months are 0-based
  return date.toDateString();
}

/**
 * @desc add a new element in html page 
 * @param string parentId - parent class where we want to insert new attribute
 * @param string elementTag - type of attribute to add (div, h1...)
 * @param string elementId - class for this specific attribute
 * @return 
 */
function addElement(parentId, elementTag, elementId) {
  if (document.getElementById(parentId)) {
    var p = document.getElementById(parentId);
    var newElement = document.createElement(elementTag);
    newElement.setAttribute('id', elementId);
    p.appendChild(newElement);
  }
}

/**
 * @desc show specific element in html page if it's hidden
 * @param string parentId - class we want to see
 * @return 
 */
function showElement(parentId) {
  var p = document.getElementById(parentId);
  p.style.display = "block";
}

/**
 * @desc hide specific element in html page if it's shown
 * @param string parentId - class we want to hide
 * @return 
 */
function hideElement(parentId) {
  var p = document.getElementById(parentId);
  p.style.display = "none";
}

/**
 * @desc convert hex to base 10 decimal
 * @param string hex - hex string to convert
 * @return 
 */
function hex2dec(hex) {
  return parseInt(hex, 16);
}