/**
 * UTILS HTML
 */

function changeSrcImage(id, url) {
  var img = document.getElementById(id);
  img.setAttribute("src", url);
}

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
    newElement.setAttribute("id", elementId);
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

function addButton(
  parentId,
  text,
  elementId = null,
  link = null,
  className = "btn btn-sm btn-primary shadow-sm mx-auto d-block"
) {
  // 1. Create the button
  var button = document.createElement("button");
  button.setAttribute("id", elementId);
  button.className += className;
  button.innerHTML = text;
  if (link) {
    button.addEventListener("click", function () {
      window.open(link);
    });
  }
  if (document.getElementById(parentId)) {
    var myDiv = document.getElementById(parentId);
    //appending button to div
    myDiv.appendChild(button);
  }
}

function computeAverage(arrayData) {
  var sum = 0;
  for (var i = 0; i < arrayData.length; i++) {
    sum += parseFloat(arrayData[i]); //don't forget to add the base
  }

  var avg = sum / arrayData.length;

  return avg;
}

/**
 * UTILS DATES
 */

/**
 * @desc format a date to the following format DD/MM/YYYY
 * @param Date date - date to change format
 * @return string new format
 */
function getFormattedDate(date) {
  var year = date.getFullYear();

  var month = (1 + date.getMonth()).toString();
  month = month.length > 1 ? month : "0" + month;

  var day = date.getDate().toString();
  day = day.length > 1 ? day : "0" + day;

  return day + "/" + month + "/" + year;
}

// parse a date in dd/mm/yyyy format
function parseDate(input) {
  var parts = input.split("/");
  var date = new Date(parts[0], parts[1] - 1, parts[2]).toString(); // Note: months are 0-based
  return date.toDateString();
}

/**
 * UTILS OTHERS
 */

/**
 * @desc convert hex to base 10 decimal
 * @param string hex - hex string to convert
 * @return
 */
function hex2dec(hex) {
  return parseInt(hex, 16);
}

function isEmpty(obj) {
  for (var key in obj) {
    if (obj.hasOwnProperty(key)) return false;
  }
  return true;
}

function searchJsonInArray(dataArr, searchField, searchVal) {
  for (var i in dataArr) {
    if (dataArr[i][searchField] == searchVal) {
      return dataArr[i];
    }
  }
  return null;
}

function computeRatioAxis(dataArr) {
  //Compute ratio display chart
  var maxAxis = Math.round(Math.max.apply(Math, dataArr) * 2);
  console.log("computeRatioAxis -> maxAxis", maxAxis);
  var minAxis = Math.round(Math.min.apply(Math, dataArr) * 2);
  console.log("computeRatioAxis -> minAxis", minAxis);

  if (-maxAxis < minAxis) {
    var rangeHighAxis = maxAxis * 2;
    var rangeLowAxis = -maxAxis * 2;
  } else if (-minAxis > maxAxis) {
    var rangeHighAxis = -minAxis * 2;
    var rangeLowAxis = minAxis * 2;
  }

  var obj = {
    rangeLow: rangeLowAxis,
    rangeHigh: rangeHighAxis,
  };

  return obj;
}

function accumulatedTable32(decimal) {
  var numb = parseInt(decimal);
  if (numb > 255 || numb < -255) {
    return null;
  }

  if (numb / 255 == 1) {
    res =
      32 * 0.125 +
      32 * 0.25 +
      32 * 0.5 +
      32 * 1 +
      32 * 2 +
      32 * 4 +
      32 * 8 +
      32 * 16;
    return res;
  }

  if (numb / 224 > 1) {
    res =
      32 * 0.125 +
      32 * 0.25 +
      32 * 0.5 +
      32 * 1 +
      32 * 2 +
      32 * 4 +
      32 * 8 +
      (numb - 224) * 16;
    return res;
  }

  if (numb / 192 > 1) {
    res =
      32 * 0.125 +
      32 * 0.25 +
      32 * 0.5 +
      32 * 1 +
      32 * 2 +
      32 * 4 +
      (numb - 192) * 8;
    return res;
  }

  if (numb / 160 > 1) {
    res =
      32 * 0.125 + 32 * 0.25 + 32 * 0.5 + 32 * 1 + 32 * 2 + (numb - 160) * 4;
    return res;
  }

  if (numb / 128 > 1) {
    res = 32 * 0.125 + 32 * 0.25 + 32 * 0.5 + 32 * 1 + (numb - 128) * 2;
    return res;
  }

  if (numb / 96 > 1) {
    res = 32 * 0.125 + 32 * 0.25 + 32 * 0.5 + (numb - 96) * 1;
    return res;
  }

  if (numb / 64 > 1) {
    res = 32 * 0.125 + 32 * 0.25 + (numb - 64) * 0.5;
    return res;
  }

  if (numb / 32 > 1) {
    res = 32 * 0.125 + (numb - 32) * 0.25;
    return res;
  }

  if (numb / 32 == 1) {
    res = 32 * 0.125;
    return res;
  }

  if (numb < 32 && numb > 0) {
    res = numb * 0.125;
    return res;
  }

  if (numb == 0) {
    res = 0;
    return res;
  }
  //NEGATIF PART
  if (numb / 255 == -1) {
    res = -(
      32 * 0.125 +
      32 * 0.25 +
      32 * 0.5 +
      32 * 1 +
      32 * 2 +
      32 * 4 +
      32 * 8 +
      32 * 16
    );
    return res;
  }

  if (numb / 224 < -1) {
    res = -(
      32 * 0.125 +
      32 * 0.25 +
      32 * 0.5 +
      32 * 1 +
      32 * 2 +
      32 * 4 +
      32 * 8 +
      (-numb - 224) * 16
    );
    return res;
  }

  if (numb / 192 < -1) {
    res = -(
      32 * 0.125 +
      32 * 0.25 +
      32 * 0.5 +
      32 * 1 +
      32 * 2 +
      32 * 4 +
      (-numb - 192) * 8
    );
    return res;
  }

  if (numb / 160 < -1) {
    res = -(
      32 * 0.125 +
      32 * 0.25 +
      32 * 0.5 +
      32 * 1 +
      32 * 2 +
      (-numb - 160) * 4
    );
    return res;
  }

  if (numb / 128 < -1) {
    res = -(132 * 0.125 + 32 * 0.25 + 32 * 0.5 + 32 * 1 + (-numb - 128) * 2);
    return res;
  }

  if (numb / 96 < -1) {
    res = -(32 * 0.125 + 32 * 0.25 + 32 * 0.5 + (-numb - 96) * 1);
    return res;
  }

  if (numb / 64 < -1) {
    res = -(32 * 0.125 + 32 * 0.25 + (-numb - 64) * 0.5);
    return res;
  }

  if (numb / 32 < -1) {
    res = -(32 * 0.125 + (-numb - 32) * 0.25);
    return res;
  }

  if (numb / 32 == -1) {
    res = -32 * 0.125;
    return res;
  }

  if (numb > -32 && numb < 0) {
    res = numb * 0.125;
    return res;
  }
}
