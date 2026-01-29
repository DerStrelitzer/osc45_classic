function format_sel(input,tag1,tag2) {
  var str = document.selection.createRange().text;
  input.focus();
  var sel = document.selection.createRange();
     if (tag2) { sel.text = tag1 + str + tag2; } else { sel.text = tag1 + str; }
  return;
}
////////////////////////////////////////////////////////////////////////////////////
function insert_pic(input) {
  var str = document.selection.createRange().text;
  input.focus();
  var my_pic = prompt("Enter Image URL:","http://");
  if (my_pic != null) {
    var sel = document.selection.createRange();
	sel.text = '<img src="' + my_pic + '" alt="" />';
  }
  return;
}
////////////////////////////////////////////////////////////////////////////////////
function insert_link(input) {
  var str = document.selection.createRange().text;
  input.focus();
  var my_link = prompt("Enter URL:","http://");
  if (my_link != null) {
    var sel = document.selection.createRange();
	sel.text = '<a href="' + my_link + '">' + str + '</a>';
  }
  return;
}
////////////////////////////////////////////////////////////////////////////////////
function mouseover(el) {
  el.className = "raised";
}

function mouseout(el) {
  el.className = "button";
}

function mousedown(el) {
  el.className = "pressed";
}

function mouseup(el) {
  el.className = "raised";
}
////////////////////////////////////////////////////////////////////////////////////
function ConvertBR(input) {
  // Converts carriage returns
  // to <BR> for display in HTML
  var textstring = input.value;
  input.focus();

  var output = "";
  for (var i = 0; i < textstring.length; i++) {
    if ((textstring.charCodeAt(i) == 13) && (textstring.charCodeAt(i + 1) == 10)) {
      i++;
      output += "<br />\n";
    } else {
      output += textstring.charAt(i);
    }
  }
  input.value = output;
  return;
}
////////////////////////////////////////////////////////////////////////////////////
function htmlstrip(input){
// Strips HTML from input.
var remaining;
var textstring = input.value;
input.focus();
for (var i = 0; i < textstring.length; i++) {
    st_tag = textstring.indexOf("<");
    end_tag = textstring.indexOf(">");
    len = textstring.length;
    firstoccur = textstring.substring(0, st_tag);
    if(end_tag == -1)
        end_tag = st_tag;
    after = textstring.substring((end_tag + 1), len);
    remaining = firstoccur + after;
    st_tagcheck = remaining.indexOf("<");
    if (remaining) { textstring = remaining; }
    i++;
}
input.value = remaining;
return;
}