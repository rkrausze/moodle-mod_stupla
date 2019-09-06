// string comparision ------------------------------------------------

function HTML2Text(s)
{
  var res = "";
  while ( true )
  {
    var i = s.search(/</);
    if  ( i == -1 )
    {
      res += s;
      break;
    }
    res += s.slice(0, i);
    s = s.slice(i+1, s.length);
    i = s.search(/>/);
    if  ( i == -1 )
      break;
    s = s.slice(i+1, s.length);
  }
  res = res.replace(/&auml;/, "�");
  res = res.replace(/&ouml;/, "�");
  res = res.replace(/&uuml;/, "�");
  res = res.replace(/&Auml;/, "�");
  res = res.replace(/&Ouml;/, "�");
  res = res.replace(/&Ouml;/, "�");
  res = res.replace(/&szlig;/, "�");
  return res;
}

function HTML2MetaHTML(s)
{
  var res = s.replace(/&auml;/g, unescape("%E4"));
  res = res.replace(/&ouml;/g, unescape("%F6"));
  res = res.replace(/&uuml;/g, unescape("%FC"));
  res = res.replace(/&Auml;/g, unescape("%C4"));
  res = res.replace(/&Ouml;/g, unescape("%D6"));
  res = res.replace(/&Uuml;/g, unescape("%DC"));
  res = res.replace(/&szlig;/g, unescape("%DF"));
  return res;
}

// Verbesserter Algorhithmus vom Jens
function DoStrCmp(s1, s2)
{
  var lS1 = s1.length;
  var lS2 = s2.length;
  if ( lS1 == 0 || lS2 == 0 )
    return (lS1 == lS2) ? 1.0 : 0.0;
  diff = new Array();
  for (var i = 0; i < lS1+1; i++)
    diff[i] = i;
  for (var i = 0; i < lS2; i++) {
    var diag = diff[0];
    diff[0]++;
    for (var j = 1; j < lS1+1; j++) {
      var min = Math.min(diff[j-1], diff[j]) + 1;
      if ( s1.charAt(j-1) == s2.charAt(i) )
        min = Math.min(diag, min);
      else
        min = Math.min(diag+1, min);
      diag = diff[j];
      diff[j] = min;
    }
  }
  var v = diff[lS1];
  return 1-v/Math.max(lS1, lS2);
}

function StrCmp(s1, s2)
{
  return DoStrCmp(CivilizeBlanks(HTML2Text(s1).toUpperCase()), CivilizeBlanks(HTML2Text(s2).toUpperCase()));
}

function CivilizeBlanks(s)
{
  while ( s.length > 0 && s.charAt(0) == " " )
    s = s.slice(1, s.length);
  var i = 1;
  while ( i < s.length )
  {
    if ( s.charAt(i) == " " )
      if ( s.charAt(i-1) == " " )
      {
        var s1 = s;
        s = s1.slice(0, i-1) + s1.slice(i, s1.length);
        continue;
      }
    i++;
  }
  while ( s.length > 0 && s.charAt(s.length-1) == ' ' )
    s = s.slice(0, s.length-1);
  return s;
}

function IronUml(s)
{
  var Uml = new Array("%E4", "ae", "%C4", "Ae",
    "%F6", "oe", "%D6", "Oe",
    "%FC", "ue", "%DC", "Ue",
    "%DF", "ss");
  var res = s;
  for (var iIr = 0; iIr < 14; iIr += 2)
    res = eval('res.replace(/'+unescape(Uml[iIr])+'/g, "'+Uml[iIr+1]+'")');
  return res;
}

function StrCmpV(s1, s2, val)
{
  var S1 = CivilizeBlanks(HTML2Text(s1));
  var S2 = CivilizeBlanks(HTML2Text(s2));
  if ( val >= 4 )
    val -= 4;
  else
  {
    S1 = IronUml(S1);
    S2 = IronUml(S2);
  }
  if ( val >= 2 )
    val -= 2;
  else
  {
    S1 = S1.toUpperCase();
    S2 = S2.toUpperCase();
  }
  return DoStrCmp(S1, S2) >= val;
}

function StrConvV(s, val)
{
  var S = CivilizeBlanks(HTML2Text(s));
  if ( val >= 4 )
    val -= 4;
  else
    S = IronUml(S);
  if ( val < 2 )
    S = S.toUpperCase();
  return S;
}

function GetBestInList(s, list)
{
  var j = 0;
  var val = 0, val1;
  for (var i = 0; i < list.length; i++)
    if ( (val1 = StrCmp(s, list[i])) > val )
    {
      j = i;
      val = val1;
    }
  return list[j];
}

// PreciseFeedback

function SetColEl(el, val)
{
  SetNoColEl(el);
  el.value = el.value + (val ? '[+]' : '[-]');
}

function SetNoColEl(el)
{
  var s = el.value;
  var l = s.length;
  if ( l > 3 && (s.substring(l-3) == "[+]" || s.substring(l-3) == "[-]") )
    el.value = s.substring(0, l-3);
}

function RemovePM(s)
{
  var l = s.length;
  return ( l > 3 && (s.substring(l-3) == "[+]" || s.substring(l-3) == "[-]") ) ? s.substring(0, l-3) : s;
}

// LongText ----------------------------------------------------------

var LTBestWord = "";
var LTBestWordIsNegativ;
var sSatzZeichen = '".,!?;:()'

function LT_LC(s, map)
{
  var res = "";
  var n;
  for (var i = 0; i < s.length; i++)
    if ( (n = map.indexOf(s.charAt(i))) == -1 || n % 2 == 1 )
      res += s.charAt(i);
    else
      res += map.charAt(n+1);
  return res;
}

function CheckLT(arr, s)
{
  var res = 1.0;
  LTBestWordIsNegativ = 1;
  // Satzzeichen trennen
  var j = -2;
  var s1 = s;
  while ( j < s1.length )
  {
    if ( sSatzZeichen.indexOf(s1.substr(j, 1)) != -1 )
    {
      s1 = s1.substr(0, j) + ' ' + s1.substr(j, 1) + ' ' + s1.substr(j+1)
      j += 2;
    }
    j++;
  }
  // Worte �bersetzen
  var Word = CivilizeBlanks(s1).split(" ");
  var sWord = "";
  for (var j = 0; j < Word.length; j++)
  {
    Word[j] = StrConvV(Word[j], arr[2]);
    var kBest = 0;
    var valBest = 0.0;
    for (var k = 0; k < arr[0].length; k++)
    {
      var val = DoStrCmp(StrConvV(arr[0][k], arr[2]), Word[j]);
      if ( val > valBest )
      {
        valBest = val;
        kBest = k;
      }
    }
    if ( valBest < (arr[2] % 2) )
      return 0.0;
    res *= 1-(1-valBest)/2;
    sWord += String.fromCharCode(kBest+'1'.charCodeAt(0));
  }
  if ( res < 0.5 )
    return 0.0;
  // Vergleich mit den einzelnen S�tzen
  var iBest = 0;
  var valBest = 0.0;
  for (var i = 3; i < arr.length; i++)
  {
    // normal
    var s1Word = arr[i][0];
    if ( s1Word.charAt(0) == '0' )
      s1Word = s1Word.substr(1);
    var valiBest = DoStrCmp(LT_LC(s1Word, arr[1]), sWord);
    // Alternativen
    for (var j = arr[i].length-1; j > 0; j--)
    {
      var s2Word = s1Word;
      for (var k = 2; k < arr[i][j].length; k++)
      {
        var s3Word = s1Word.substr(0, arr[i][j][0])+arr[i][j][k]+s1Word.substr(arr[i][j][0]+arr[i][j][1]);
        var val = DoStrCmp(LT_LC(s3Word, arr[1]), sWord);
        if ( val > valiBest )
        {
          valiBest = val;
          s2Word = s3Word;
        }
      }
      s1Word = s2Word;
    }
    if ( valiBest > valBest )
    {
      iBest = i;
      valBest = valiBest;
      LTBestWord = s1Word;
      LTBestWordIsNegativ = (arr[i][0].charAt(0) == '0') ? 1 : 0;
    }
  }
  return (LTBestWordIsNegativ == 1) ? 0 : res*valBest;
}

function GetLTBestSolve(arr, s)
{
  LTBestWord = "";
  if ( arguments.length > 1 )
  {
    if ( lay )
      s = RemovePM(s);
    if ( s != "" )
      CheckLT(arr, s);
  }
  if ( LTBestWordIsNegativ == 1 || LTBestWord == "" )
    LTBestWord = arr[3][0];
  var res = "";
  var noSpace = 1;
  var HCin = 0; // Hochkomma
  for (var i = 0; i < LTBestWord.length; i++)
  {
    var sWord = arr[0][LTBestWord.charCodeAt(i)-'1'.charCodeAt(0)];
    if ( sSatzZeichen.indexOf(sWord) != -1 )
      noSpace = 1;
    if ( sWord == '"' )
    {
      HCin = 1 - HCin;
      noSpace = (HCin == 0) ? 1 : 0;
    }
    if ( i != 0 && noSpace == 0 )
      res += " ";
    res += sWord;
    noSpace = (sWord == '"' && HCin == 1) ? 1 : 0;
  }
  return res;
}

// plane -------------------------------------------------

var Flight = 0;
var Token;
var SourceObj = 0;
var SourceColor;
var SourceNr;
var Sourcei;
var IsFive = 1;//( navigator.appVersion.indexOf("MSIE 4") == -1) ? 1 : 0;

function TakeOff(token, sourceObj)
{
  Token = token;
  SourceObj = sourceObj;
  SourceNr = -1; Sourcei = -1;
  if ( Token == "" || Solved != -2 )
    return false;
  Flight = 2;
  token = '<FONT face="Arial" size="3" color="#FFD600"><I><B>' +
    token + '</B></I></FONT>';
  var win1 = top.data.display;
  var doc1 = win1.document;
  if ( lay )
  {
    var doc = doc1.lplane.document;
    doc.open();
    doc.clear();
    doc.write('<TABLE border=0 cellpadding=3><TR><TD>' + token + '</TD></TR></TABLE>');
    doc.close();
    doc1.lplane.visibility = "show";
    win1.captureEvents(Event.MOUSEMOVE);
    win1.onmousemove = lMovePlane;
    win1.captureEvents(Event.MOUSEDOWN);
    win1.onmousedown = OnFlight;
  }
  else if ( ie )
  {
    doc1.all.plane1.style.zIndex = 3;
    doc1.all.plane2.innerHTML = token;
    if ( SourceObj != 0 )
    {
      SourceColor = SourceObj.style.backgroundColor;
      SourceObj.style.backgroundColor = "#FFD600";
    }
    doc1.all.frame.style.setAttribute("cursor", "crosshair", false);
    doc1.all.plane1.style.setAttribute("cursor", "crosshair", false);
  }
  else // DOM
  {
    var pl = doc1.getElementById("plane");
    pl.style.zIndex = 3;
    pl.innerHTML = token
    if ( SourceObj != 0 )
    {
      SourceColor = SourceObj.style.backgroundColor;
      SourceObj.style.backgroundColor = "#FFD600";
    }
    doc1.getElementById("frame").style.cursor = "crosshair";
    win1.onmousemove = lMovePlane;
  }
  return true;
}

function CrashDown()
{
  Flight = 0;
  var win1 = top.data.display;
  var doc1 = win1.document;
  if ( lay )
  {
    doc1.lplane.visibility = "hide";
    win1.onmousemove=0;
    win1.releaseEvents(Event.MOUSEMOVE);
    win1.onmousedown=0;
    win1.releaseEvents(Event.MOUSEDOWN);
  }
  else
  {
    var pl = ie ? doc1.all.plane1 : doc1.getElementById("plane");
    pl.style.left = -220;
    pl.style.top = -220;
    pl.style.cursor = "";
    if ( SourceObj != 0 )
      SourceObj.style.backgroundColor = SourceColor;
    if ( ie )
      doc1.all.frame.style.removeAttribute("cursor", false);
    else
    {
      doc1.getElementById("frame").style.cursor = "";
      win1.onmousemove=0;
    }
  }
}

function MovePlane()
{
  var win = top.data.display;
  var doc = win.document;
  var pl = doc.all.plane1.style;
  if ( IsFive == 1)
  {
    pl.left = win.event.x+doc.body.scrollLeft+1;
    pl.top = win.event.y+doc.body.scrollTop+1;
  }
  else
  {
    pl.left = win.event.x+1;
    pl.top = win.event.y+1;
  }
}

function lMovePlane(ev)
{
  var pl = lay ? top.data.display.document.lplane : top.data.display.document.getElementById("plane").style;
  pl.left = ev.pageX+1;
  pl.top = ev.pageY+1;
}

function OnFlight()
{
  if ( Flight == 2 )
    Flight = 1;
  else if ( Flight == 1 )
    CrashDown();
}

// service functions ------------------------------------------------

function GetTag(name)
{
  return ie ? eval('top.data.display.document.all.'+name) : top.data.display.document.getElementById(name);
}

function GetEl(name)
{
  return eval('top.data.display.document.forms[0].'+name);
}

function GetTagAI(nr, i) {
  return GetTag('a'+nr+'_'+i);
}

function GetElA(id) {
  return eval('top.data.display.document.forms[0].a'+id);
}

function GetElAI(id, i) {
  return eval('top.data.display.document.forms[0].a'+id)[i];
}



// code_tar - code for targets  -------------------------------------

var TFSynUsed = new Array();
var Check = new Array();

function PrintTF(nr, xl, yl, subType)
{
  if ( arguments.length > 3 )
    SubType[nr] = (arguments.length > 3) ? subType : 7;
  if ( top.data.display.document.layers )
    top.data.display.document.write('<TEXTAREA name="a', nr, '"',
      ( xl != -1 ) ? (' cols=' + (xl/8)) : "20",
      ( yl != -1 ) ? (' rows=' + (yl/8)) : "1",
      ' onblur="top.control.m[', nr, '] = value;return true;" wrap=virtual>'+m[nr]+'</TEXTAREA>\r\n',
      '<A href="javascript:top.control.InTF(', nr, ', this);">&nbsp;H&nbsp;</A>');
  else
    top.data.display.document.write('<TEXTAREA name="a', nr, '" id=a', nr,
      ( xl != -1 ) ? (' cols=' + (xl/8)) : "20",
      ( yl != -1 ) ? (' rows=' + (yl/8)) : "1",
      ' onblur="top.control.m[', nr, '] = value;return true;" wrap=virtual onclick="top.control.InTF(', nr, ', this);return true;">'+m[nr]+'</TEXTAREA>\r\n');
  TFSynUsed[nr] = -1;
}

function SITakeOff(s)
{
  if ( Flight == 0 )
    TakeOff(s, 0);
  else
    CrashDown();
}

function InTF(nr, obj)
{
  if ( Flight != 0 )
  {
    CrashDown();
    m[nr] = Token;
    SetT(nr, m[nr]);
  }
  else if ( lay )
    if ( m[nr] != "" )
      TakeOff(m[nr], obj);
}


function SetT(nr, s)
{
  if ( GetEl("a" + nr) )
  {
    GetEl("a" + nr).value = HTML2Text(s); // HTML2Text, falls Formatierungen drin
  }
  else
    GetTag("a" + nr).innerHTML = s;
}

// PreciseFeedback

function SetColT(nr, val)
{
  if ( GetTag("a" + nr) )
    GetTag("a" + nr).style.backgroundColor = val ? GREEN : RED;
  else if ( GetEl("a"+nr) )
    SetColEl(GetEl("a" + nr), val);
}

function SetNoColT(nr)
{
  if ( GetEl("a"+nr) )
    SetNoColEl(GetEl("a" + nr));
  if ( GetTag("a" + nr) )
    GetTag("a" + nr).style.backgroundColor = (GetEl("a" + nr) ? "#FFFFFF" : "#CECEFF");
}

// TM ---------------------------------------------------------------

function PrintTFM(nr, xl, yl, subType)
{
  if ( arguments.length > 3 )
    SubType[nr] = (arguments.length > 3) ? subType : 15;
  if ( top.data.display.document.layers )
    top.data.display.document.write('<TEXTAREA name="a', nr, '"',
      ( xl != -1 ) ? (' cols=' + (xl/8)) : "20",
      ( yl != -1 ) ? (' rows=' + (yl/8)) : "1",
      ' onblur="top.control.m[', nr, '][0] = value;return true;" wrap=virtual>'+m[nr][0]+'</TEXTAREA>\r\n',
      '<A href="javascript:top.control.InTFM(', nr, ', this);">&nbsp;H&nbsp;</A>');
  else
    top.data.display.document.write('<TEXTAREA name="a', nr, '" id=a', nr,
      ( xl != -1 ) ? (' cols=' + (xl/8)) : "20",
      ( yl != -1 ) ? (' rows=' + (yl/8)) : "1",
      ' onblur="top.control.m[', nr, '][0] = value;return true;" wrap=virtual onclick="top.control.InTFM(', nr, ', this);return true;">'+m[nr][0]+'</TEXTAREA>\r\n');
  TFSynUsed[nr] = new Array();
}

function InTFM(nr, obj)
{
  if ( Flight != 0 )
  {
    CrashDown();
    m[nr][0] += (m[nr][0] != "" ? "; " : "")+Token;
    SetT(nr, m[nr][0]);
  }
}

function CheckTFM(nr)
{
  Check[nr] = new Array("check");
  m[nr] = new Array(m[nr][0].replace(/\s*\[\+\]\s*/g, "").replace(/\s*\[-\]\s*/g, "").replace(/\s*\[=\]\s*/g, ";").replace(/;+/g, ";"));  // restliche evtl. vorhandene Felder l�schen
  if ( m[nr][0] == '' )
    return 0;
  // zerlegen in Token
  var tok = m[nr][0].replace(/\r/g, " ").replace(/\n/g, " ").split(";");
  for (var i = 0; i < tok.length; i++)
    m[nr][i+1] = CivilizeBlanks(tok[i]);
  Check[nr][0] = tok.length;
  // AbstreichFeld f�r die harten Synonymgruppen
  var TFMRegoniced = new Array(); // nr. des passenden Wortes, 0 .. noch frei
  for (var k = 0; k < d[nr].length; k++)
    TFMRegoniced[k] = 0;
  var nOK = 0, nAdd = 0;
  // einzelne m-Werte durchchecken
  for (var j = 1; j < m[nr].length; j++) {
    var run = true;
    TFSynUsed[nr][j] = 0;
    // noch frei Syn-Listen
    for (var k = 0; k < d[nr].length && run; k++)
      if ( TFMRegoniced[k] == 0 )
        for (i = 0; i < d[nr][k].length && run; i++)
          if ( StrCmp(m[nr][j], d[nr][k][i]) >= 0.9 )
          {
            Check[nr][j] = 2;
            TFMRegoniced[k] = j;
            TFSynUsed[nr][j] = -k;
            m[nr][j] = d[nr][k][i]; // Rechtschreibkorr.
            nOK++;
            run = false;
          }
    // bereits benutzte Syn-Listen
    for (var k = 0; k < d[nr].length && run; k++)
      if ( TFMRegoniced[k] != 0 )
        for (i = 0; i < d[nr][k].length && run; i++)
          if ( StrCmp(m[nr][j], d[nr][k][i]) >= 0.9 )
          {
            Check[nr][j] = 2;
            TFSynUsed[nr][j] = TFMRegoniced[k];
            m[nr][j] = d[nr][k][i]; // Rechtschreibkorr.
            run = false;
          }
    if ( run )
      nAdd++;
  }
  m[nr][0] = m[nr].slice(1, m[nr].length).join("; ");
  GetEl("a" + nr).value = HTML2Text(m[nr][0]);
  return nOK/(d[nr].length+nAdd);
}

function SolveTextTFM(nr)
{
  if ( TFSynUsed[nr].length == 0 )
    CheckTFM(nr);
  var s = "";
  var TFMRegoniced = new Array(); // nr. des passenden Wortes, 0 .. noch frei
  for (var k = 0; k < d[nr].length; k++)
    TFMRegoniced[k] = -1;
  for (var i = 1; i < TFSynUsed[nr].length; i++)
    if ( Check[nr][i] >= 2 ) {
      TFMRegoniced[-TFSynUsed[nr][i]] = 1;
      s += (s == "" ? "" : "; ") + m[nr][i];
    }
  for (var k = 0; k < d[nr].length; k++)
    if ( TFMRegoniced[k] == -1 )
      s += (s == "" ? "" : "; ") + d[nr][k][0];
  m[nr][0] = s;
  return s;
}

function SetColTFM(nr)
{
  var s = "";
  var plus = 0;
  var minus = 0;
  var syn = 0;
  for (var i = 1; i < m[nr].length; i++)
    if ( TFSynUsed[nr][i] < 1 )
    {
      if ( s != "" )
        s += "; ";
      s += m[nr][i];
      for (var j = i+1; j < m[nr].length; j++)
        if ( TFSynUsed[nr][j] == i ) {
          s += " [=] " + m[nr][j];
          syn++;
        }
      s += "[" + (Check[nr][i] >= 2 ? '+' : '-') +"]"
      if ( Check[nr][i] >= 2 )
        plus++;
      else
        minus++;
  }
  if ( top.data.display.document.layer || syn != 0 || (plus > 0 && minus > 0) )
    GetEl("a" + nr).value = HTML2Text(s);
  if ( !top.data.display.document.layer )
    if ( plus > 0 && minus > 0 )
      GetTag("a" + nr).style.backgroundColor = YELLOW;
    else
      GetTag("a" + nr).style.backgroundColor = (plus > 0) ? GREEN : RED;
}

function SetNoColTFM(nr, val)
{
  GetEl("a" + nr).value = HTML2Text(m[nr][0]);
  if ( !top.data.display.document.layer )
    GetTag("a" + nr).style.backgroundColor = "#FFFFFF";
}


// code_list - code for list-targets  -------------------------------

var SubType = new Array();
var Check = new Array();
var Spring = new Array();

var newTakeOff = 0;

function SITakeOff(s)
{
  if ( Flight == 0 )
    TakeOff(s, 0);
  else
    CrashDown();
}

function EntryToText(entry) {
  if ( entry == "\x01-1" )
    return "";
  else if ( entry.charCodeAt(0) == 1 ) {
    var a = entry.split("\x01");
    return d[a[2]][a[1]];
  }
  else
    return entry;
}

function Entry2CmpText(s) {
/*  if ( top.data.display.document.layers )
    return HTML2Text(EntryToText(s));
  else*/
    return HTML2MetaHTML(EntryToText(s));
}

function InformListSLS(nr, entry)
{
  if ( entry == "\x01-1" )
    return;
  if ( entry.charCodeAt(0) == 1 ) {
    var a = entry.split("\x01");
    if ( (""+m[a[2]]) != "-1" && m[a[2]][a[1]] == -1 )
      m[a[2]][a[1]] = nr;
  }
}

// TLS --------------------------------------------------------------

function PrintTLS(nr, xl, yl, subType, asTD)
{
  if ( arguments.length > 3 )
    SubType[nr] = (arguments.length > 3) ? subType : 3;
  Spring[nr] = "";
  if ( top.data.display.document.layers )
  {
    top.data.display.document.write(asTD ? '<TD>' : "",
      '<TEXTAREA name="a', nr, '"',
      ( xl != -1 ) ? (' cols=' + (xl/8)) : "20",
      ( yl != -1 ) ? (' rows=' + (yl/8)) : "2",
      ' onfocus="top.control.InLay(', nr, '); return true;"',
      ' onblur="top.control.OutLay(', nr, '); return true;"',
      ' wrap=virtual>', EntryToShowLay(nr, d[nr][1], ""), '</TEXTAREA>\r\n',
      '<A href="javascript:top.control.InTLS(', nr, ', this);">&nbsp;H&nbsp;</A>',
      asTD ? '</TD>' : '');
  }
  else
  {
    sCont = " &nbsp; ";
    if ( d[nr][1] != "\x01-1" )
      sCont = EntryToText(d[nr][1]);
    else if ( !ie )
      for (var i = 1; i < xl/8; i++)
        sCont += "&nbsp; ";
    if ( asTD )
      Spring[nr] = '<DIV style="width:'+xl+'px;height:0px;"><B></B></DIV>';
    top.data.display.document.write(asTD ? '<TD valign="top"' : '<SPAN', ' id=a', nr, ' style="background-color:#CECEFF',
      ( xl != -1 && !asTD) ? (";width:" + xl) : "",
      ( yl != -1) ? (";height:" + yl) : "",
      ';" onclick="top.control.InTLS(', nr, ', this)">', sCont, asTD ? Spring[nr]+'</TD>\r\n' : '</SPAN>\r\n');
  }
}

function InTLS(nr, obj)
{
  var list = d[nr][0];
  if ( Flight == 0 )
  {
    if ( m[nr] != "\x01-1" )
    {
      var s = m[nr];
      var posi = -1;
      var list = -1;
      if ( s.charCodeAt(0) == 1 ) {
        var a = s.split("\x01");
        posi = a[1];
        list = a[2];
        s = d[list][posi];
      }
      if ( !TakeOff(s, obj) )
        return;
      SourceNr = list; Sourcei = posi;
      SetTLS(nr, -1, 0);
      if ( list != -1 )
        RefreshSLS(list);
    }
  }
  else
  {
    CrashDown();
    if ( SourceNr != list && (SubType[nr] & 1 == 0) )
      return;
    SetTLS(nr, SourceNr == -1 ? Token : Sourcei, SourceNr);
    if ( SourceNr != -1 ) //??? ist das notwendig?
      RefreshSLS(SourceNr);
  }
}

function SetTLS(nr, val, sourceNr)
{
  var list = d[nr][0];
  if ( m[nr] != "\x01-1" && m[nr].charCodeAt(0) == 1 ) {      // remove old TLS-Contens
    var a = m[nr].split("\x01");
    if ( (""+m[a[2]]) != "-1" && m[a[2]][a[1]] == nr ) {// SLS and not during "Solve"
      m[a[2]][a[1]] = -1;
      RefreshSLS(a[2]);
    }
  }
  var s = "";
  if ( sourceNr == -1 )
  {
    m[nr] = val;
    s = val;
    val = -1;
  }
  else
  {
    m[nr] = "\x01"+val+(val != -1 ? "\x01"+sourceNr : "");
    if ( val != -1 )
      s = d[sourceNr][val];
    else if ( !lay )
      s = " &nbsp; ";
  }
  if ( GetEl("a"+nr) )
    GetEl("a" + nr).value = (val == -1) ? "" : ("" + (val+1) + ". " + HTML2Text(s));
  else
    GetTag("a" + nr).innerHTML = s+Spring[nr];
  if ( sourceNr != -1 && val != -1 && ((""+m[sourceNr]) != "-1") )
  {
    var old = m[sourceNr][val];  // remove new TLS contens from old position
    m[sourceNr][val] = nr;
    if ( old != -1 )
      SetTLS(old, -1);
  }
}

function SetTLSEntry(nr, entry) {
  if ( entry == "\x01-1" )
    SetTLS(nr, -1, 0);
  else if ( entry.charCodeAt(0) == 1 ) {
    var a = entry.split("\x01");
    SetTLS(nr, a[1], a[2]);
  }
  else
    SetTLS(nr, entry, -1);
}

function EntryToShowLay(nr, entry, sAdd) {
  var res = "";
  if ( entry == "\x01-1" )
    return res;
  else if ( entry.charCodeAt(0) == 1 ) {
    var a = entry.split("\x01");
    if ( a[2] == d[nr][0] )
      res += (1*a[1]+1)+". ";
    res += d[a[2]][a[1]];
  }
  else
    res = entry;
  return HTML2Text(res)+sAdd;
}

function EntryToEditLay(nr, entry) {
  if ( entry.charCodeAt(0) == 1 )
  {
    var a = entry.split("\x01");
    if ( a[2] == d[nr][0] )
      return 1*a[1]+1;
    return d[a[2]][a[1]];
  }
  else
    return entry;
}

function InLay(nr)
{
  if ( Solved != -2 )
    return;
  if ( m[nr] != "\x01-1" )
    GetEl("a"+nr).value = "" + EntryToEditLay(nr, m[nr]);
  else
    GetEl("a"+nr).value = "";
}

function OutLay(nr)
{
  if ( Solved != -2 )
    return;
  var list = d[nr][0];
  var j = GetEl("a" + nr).value - 1;
  if ( j >= 0 && j < d[list].length )
    SetTLS(nr, j, list);
  else if ( SubType[nr] & 1 == 1 )
    SetTLS(nr, CivilizeBlanks(GetEl("a" + nr).value), -1);
  else
  {
    SetTLS(nr, -1);
    GetEl("a" + nr).value = "ung�ltiger Wert";
  }
}

function CheckTLS(nr) {
  if ( d[nr].length <= 2 && Entry2CmpText(m[nr]) == "" )
  {
    Check[nr] = 2;
    return 1;
  }
  for (var i = 2; i < d[nr].length; i++)
    if ( Entry2CmpText(m[nr]) == Entry2CmpText(d[nr][i]) ) {
      Check[nr] = 2;
      return 1;
    }
  Check[nr] = 0;
  return 0;
}

function SolveTextTLS(nr, xl, yl, subType, asTD)
{
  var j = 2;
  for (var i = 2; i < d[nr].length; i++)
    if ( Entry2CmpText(m[nr]) == Entry2CmpText(d[nr][i]) )
    {
      j = i;
      break;
    }
  return (asTD ? '<TD' : "<SPAN") + ' style="background-color:#CECEFF' +
      (xl != -1 && !asTD ? (";width:" + xl) : "") +
      (yl != -1 ? (";height:" + yl) : "") +
      ';"><B>' + (d[nr].length <= 2 ? "" : EntryToText(d[nr][j])) + '</B>' + (asTD ? Spring[nr]+'</TD>\r\n' : '</SPAN>\r\n');
}

// PreciseFeedback

function SetColTLS(nr, val)
{
  if ( arguments.length < 2 )
    val = Check[nr];
  if ( GetEl("a"+nr) )
    GetEl("a" + nr).value = "[" + (val >= 2 ? "+" : '-') + "] " + GetEl("a" + nr).value;
  else
    GetTag("a" + nr).style.backgroundColor = (val >= 2) ? GREEN : RED;
}

function SetNoColTLS(nr)
{
  if ( GetEl("a"+nr) )
  {
    if ( GetEl("a" + nr).value.substr(0, 1) == '[' )
      GetEl("a" + nr).value = GetEl("a" + nr).value.substr(4);
  }
  else
    GetTag("a" + nr).style.backgroundColor = "#CECEFF";
}

// TLM --------------------------------------------------------------

function TLMString(nr)
{
  var list = d[nr][0];
  var s = "";
  for (var i = 1; i <= m[nr][0]; i++)
  {
    if ( i > 1 )
      s += (lay) ? "\r\n" : "<BR>";
    if ( lay )
      s += EntryToShowLay(nr, m[nr][i], "");
    else
      s += '<SPAN style="background-color:#CECEFF" onclick="top.control.InTLMP(' + nr + ', top.control.m['+nr+']['+i+']+\'\', this)"> &nbsp;' +
           EntryToText(m[nr][i]) + '&nbsp; </SPAN>';
  }
  return s;
}

function PrintTLM(nr, xl, yl, subType, asTD)
{
  if ( arguments.length > 3 )
    SubType[nr] = (arguments.length > 3) ? subType : 11;
  Spring[nr] = "";
  if ( top.data.display.document.layers )
  {
    top.data.display.document.write(asTD ? '<TD>' : "",
      '<TEXTAREA name="a', nr, '"',
      ( xl != -1 ) ? (' cols=' + (xl/8)) : "20",
      ( yl != -1 ) ? (' rows=' + (yl/8)) : "2",
      ' onfocus="top.control.InLayM(', nr, '); return true;"',
      ' onblur="top.control.OutLayM(', nr, '); return true;"',
      ' wrap=virtual>', TLMString(nr), '</TEXTAREA>\r\n',
      '<A href="javascript:top.control.InTLM(', nr, ', this);">&nbsp;H&nbsp;</A>',
      asTD ? '</TD>' : "");
  }
  else {
    var sTag = asTD ? 'TD' : (top.data.display.document.all ? 'SPAN' : 'DIV');
    top.data.display.document.write('<', sTag, asTD ? ' valign="top"' : '', ' id=a', nr, ' style="background-color:#CECEFF',
      ( xl != -1 && !asTD) ? (";width:" + xl) : "",
      ( yl != -1) ? (";height:" + yl) : "",
      ';" onclick="top.control.InTLM(', nr, ', this)">', TLMString(nr), '&nbsp;',
       asTD ? '<DIV style="width:'+xl+'px;height:0px;"><B></B></DIV>' : '',
       '</', sTag, '>\r\n');
  }
}

function RefreshTLM(nr)
{
  var s = TLMString(nr);
  if ( GetEl("a"+nr) )
    GetEl("a" + nr).value = s;
  else
  {
    if ( s == "" ) s = "&nbsp;"+Spring[nr];
    GetTag("a" + nr).innerHTML = s;
  }
}

function InTLM(nr, obj)
{
  var list = d[nr][0];
  if ( Flight != 0 && newTakeOff == 0 )
  {
    CrashDown();
    if ( SourceNr != list && (SubType[nr] & 1 == 0) )
      return;
    m[nr][0]++;
    m[nr][m[nr][0]] = (SourceNr != -1) ? "\x01"+Sourcei+"\x01"+SourceNr : Token;
    RefreshTLM(nr);
    if ( SourceNr != -1 && (""+m[SourceNr]) != "-1" )
      m[SourceNr][Sourcei] = nr;
    RefreshSLS(list);
  }
  newTakeOff = 0;
}

function RemoveTLM(nr, entry)
{
  var j = 1;
  while ( j < m[nr][0] )
    if ( m[nr][j] == entry )
      break;
    else
      j++
  while ( j < m[nr][0] )
  {
    m[nr][j] = m[nr][j+1];
    j++;
  }
  m[nr][0]--;
}

function InTLMP(nr, entry, obj)
{
  var list = d[nr][0];
  if ( Flight == 0 ) {
    var s = entry;
    var posi = -1;
    var list = -1;
    if ( s.charCodeAt(0) == 1 ) {
      var a = s.split("\x01");
      posi = a[1];
      list = a[2];
      s = d[list][posi];
    }
    newTakeOff = 1;
    if ( !TakeOff(s, obj) )
      return;
    SourceNr = list; Sourcei = posi;
    RemoveTLM(nr, entry);
    RefreshTLM(nr);
    if ( list != -1 && (""+m[list]) != "-1" ) {
      m[list][posi] = -1;
      RefreshSLS(list);
    }
    newTakeOff = 0;
  }
}

function InLayM(nr)
{
  if ( Solved != -2 )
    return;
  var s = "";
  for (var i = 1; i <= m[nr][0]; i++)
    s += EntryToEditLay(nr, m[nr][i]) + "; ";
  GetEl("a"+nr).value = s;
}

function OutLayM(nr)
{
  if ( Solved != -2 )
    return;
  var list = d[nr][0];
  var n = GetEl("a" + nr).value.split(";");
  m[nr][0] = 0;
  if ( n )
    for (var j = 0; j < n.length; j++)
    {
      n[j] = CivilizeBlanks(n[j]);
      if ( isNaN(Number(n[j])) ) {
        m[nr][++m[nr][0]] = n[j];
        continue;
      }
      i = n[j]-1;
      if ( i < 0 || i >= d[list].length )
        continue;
      m[nr][++m[nr][0]] = "\x01"+i+"\x01"+list;
      if ( (""+m[list]) != "-1" )
      {
        var old = m[list][i];
        if ( old != nr && old != -1 ) // remove the old
        {
          if ( d[old][1].length ) //TLM
          {
            RemoveTLM(old, "\x01"+i+"\x01"+old);
            RefreshTLM(old);
          }
          else
            SetTLS(old, -1);
        }
        m[list][i] = nr;
      }
    }
  RefreshTLM(nr);
  RefreshSLS(list);
}

function CheckTLM(nr)
{
  sMailAdd = '';
  if ( d[nr][2].length == 0 )
    return (m[nr][0] == 0) ? 1 : 0;
  var n = 0;
  Check[nr] = new Array();
  mAsText = new Array();
  for (var j = 1; j <= m[nr][0]; j++) {
    Check[nr][j] = 0;
    mAsText[j] = Entry2CmpText(m[nr][j]);
  }
  for (var i = 0; i < d[nr][2].length; i++)
  {
    dAsText = Entry2CmpText(d[nr][2][i]);
    flag = 1;
    for (var j = 1; j <= m[nr][0]; j++)
      if ( Check[nr][j] == 0 && mAsText[j] == dAsText )
      {
        n++;
        Check[nr][j] = 2;
        flag = 0;
        break;
      }
    if ( flag == 1 )
      sMailAdd += (sMailAdd == '' ? '' : ', ') + '[?] ' + dAsText;
  }
  for (var j = 1; j <= m[nr][0]; j++)
    sMailAdd += (sMailAdd == '' ? '' : ', ') + (Check[nr][j] == 2 ? '[+] ' : '[-] ')+mAsText[j];
  return n / ((d[nr][2].length > m[nr][0]) ? d[nr][2].length : m[nr][0]);
}

function SolveTextTLM(nr, xl, yl, subType, asTD)
{
  var s = "", res;
  var sTrenn = top.data.display.document.layers ? "\r\n" : "<BR>";
  for (var i = 0; i < d[nr][2].length; i++)
    s += (s == "" ? "" : sTrenn) + EntryToText(d[nr][2][i]);
  if ( top.data.display.document.layers )
  {
    return (asTD ? '<TD>' : "") +
      '<TEXTAREA name="a' + nr + '"' +
      (xl != -1 ? (' cols=' + (xl/8)) : "20") +
      (yl != -1 ? (' rows=' + (yl/8)) : "2") +
      ' wrap=virtual>' + s + '</TEXTAREA>\r\n' +
      (asTD ? '</TD>' : "");
  }
  else {
    var sTag = (asTD ? 'TD' : (top.data.display.document.all ? 'SPAN' : 'DIV'));
    return '<' + sTag + (asTD ? ' valign="top"' : '') +
      ' id=a' + nr + ' style="background-color:#CECEFF' +
      (xl != -1 && !asTD ? (";width:" + xl) : "") +
      (yl != -1 ? (";height:" + yl) : "") +
      ';"><B>' + s + '</B>'+
      (asTD ? '<DIV style="width:'+xl+'px;height:0px;"><B></B></DIV>' : '') +
      '</' + sTag + '>\r\n';
  }
}

// PreciseFeedback

function TLMStringCol(nr)
{
  var list = d[nr][0];
  var s = "";
  for (var i = 1; i <= m[nr][0]; i++)
  {
    if ( i > 1 )
      s += (lay) ? "\r\n" : "<BR>";
    if ( lay )
      s += EntryToShowLay(nr, m[nr][i], Check[nr][i] >= 2 ? '[+]' : '[-]');
    else
      s += '<SPAN style="background-color:' + (Check[nr][i] >= 2 ? GREEN : RED) + '" onclick="top.control.InTLMP(' + nr + ', top.control.m['+nr+']['+i+']+\'\', this)"> &nbsp;' +
           EntryToText(m[nr][i]) + '&nbsp; </SPAN>';
  }
  return s;
}

function SetColTLM(nr)
{
  var s = TLMStringCol(nr);
  if ( GetEl("a"+nr) )
    GetEl("a" + nr).value = s;
  else {
    if ( s == "" ) s = "&nbsp;";
    GetTag("a" + nr).innerHTML = s;
  }
}

function SetNoColTLM(nr)
{
  RefreshTLM(nr);
}

// SLS, SLM ---------------------------------------------------------

var Cols = new Array();
function RefreshSLS(nr)
{
  if ( nr != -1 && !lay && (""+m[nr]) != "-1" )
    GetTag("a" + nr).innerHTML = SLSString(nr);
}

function SLSString(nr)
{
  var s = "";
  var nx = 0;
  var ny = 0;
  //alert(((""+m[nr]) == "-1") ? "tz" : "tzt");
  for (var i = 0; i < d[nr].length; i++)
    if ( (""+m[nr]) == "-1" || m[nr][i] == -1 )
    {
      if ( ny == Cols[nr] )
      {
        s += '</TR><TR>\r\n';
        ny = 0; nx++;
      }
      if ( lay )
        s += '<TD bgcolor=#CECEFF><A href="javascript:top.control.InSLSP(' + nr + ', ' + i + ', this);">' +
             (i+1) + '. ' + d[nr][i] + '</A></TD>\r\n';
      else
        s += '<TD bgcolor=#CECEFF onclick="top.control.InSLSP(' + nr + ', ' + i + ', this)"> &nbsp;'
             + d[nr][i] + '&nbsp; </TD>\r\n';
      ny++;
    }
  if ( nx + ny > 0 )
  {
    for (var i = ny; i <= Cols[nr]; i++)
      s += '<TD> &nbsp; </TD>';
    s = '<TR>' + s + '</TR>'
  }
  return '<TABLE cellspacing=2 border=0>' + s + '</TABLE>';
}

function PrintSLS(nr, cols)
{
  Cols[nr] = ( cols ) ? cols : 1;
  top.data.display.document.write('<DIV id=a', nr, ' onclick="top.control.InSLS(', nr, ')"> ',
    SLSString(nr), '</DIV>');
}

function InSLSP(nr, i, obj)
{
  if ( Flight == 0 )
  {
    if ( !TakeOff(d[nr][i], obj) )
      return;
    SourceNr = nr;
    Sourcei = i;
    newTakeOff = (lay) ? 0 : 1;
  }
}

function InSLS(nr)
{
  if ( Flight && newTakeOff == 0 )
    CrashDown();
  newTakeOff = 0;
}

function ResetSLS(nr)
{
  for (var i = 0; i < m[nr].length; i++)
    m[nr][i] = -1;
}
// ZO ---------------------------------------------------------------

var Names = new Array();

function PrintZO(nr, names, cols)
{
  Names[nr] = names;
  top.data.display.document.writeln('<TABLE border=2 cellpadding=2>\r\n');
  for (var i = 0; i < names.length; i++)
    if ( lay )
    {
      top.data.display.document.writeln('<TR><TD>', names[i], '</TD><TD>');
      PrintTLS(nr+i+1, 400, 16);
      top.data.display.document.writeln('</TD></TR>');
    }
    else
    {
      Spring[nr+i+1] = '<span style="width:200px;height:0px;"><B></B></span>';
      top.data.display.document.writeln('<TR><TD>', names[i],
        '</TD><TD id=a', nr+i+1, ' style="background-color:#CECEFF;" onclick="top.control.InTLS(', nr+i+1,
        ', this)"> &nbsp; ', Spring[nr+i+1], '</TD></TR>');
    }
  top.data.display.document.writeln('</TABLE>\r\n');
  PrintSLS(nr, cols);
}

function SolveTextZO(nr)
{
  var s = '<TABLE border=2 cellpadding=2>\r\n';
  for (var i = 0; i < Names[nr].length; i++)
    s += '<TR><TD>'+Names[nr][i]+'</TD><TD><B>'+
         EntryToText(d[nr+i+1][2])+'</B></TD></TR>';
  s += '</TABLE>';
  return s;
}
// FZO ---------------------------------------------------------------

var Names = new Array();
var FZOnTarget = new Array();
var FZOVar = new Array();
var FZOVarTar = new Array();
var FZOVarInfo = new Array();
var FZOVarCoord = new Array();
var FZOVarText = new Array();
var FZOVarSubType = new Array();
var FZOVarListIndex = new Array();
var FZOVarValue = new Array();

var FZO_f = 4;
var FZO_m = 8;
var FZO_u = 32;
var FZO_fm = FZO_f + FZO_m;

var HardCheck = new Array(HardCheckTLS, HardCheckTF, HardCheckTLM, HardCheckTFM);
var ClearM = new Array(ClearMTLS, ClearMTF, ClearMTLM, ClearMTFM);
var HardCheckSolveText = new Array(HardCheckSolveTextTLS, HardCheckTF, HardCheckTLM, HardCheckTFM);

function PrintFZO(nr, nx, names, subType, dim, fzoVar)
{
  if ( arguments.length < 4 || !subType.length )
    SubType[nr] = 0;
  else
    for (var i = 0; i < subType.length; i++)
      SubType[i+nr] = subType[i];
  if ( arguments.length < 5 )
    dim = -1;
  cols = Math.floor(SubType[nr] / 2);
  SubType[nr] %= 2;
  Names[nr] = names;
  FZOnTarget[nr] = names.length-1;
  if ( arguments.length >= 6 )
  {
    FZOVarInfo[nr] = fzoVar[0];
    FZOVar[nr] = fzoVar[1];
    FZOVarTar[nr] = new Array();
    for (var j = 0; j < FZOVar[nr].length; j++)
      FZOVarTar[nr][j] = new Array();
    FZOVarCoord[nr] = fzoVar[2];
    FZOVarText[nr] = fzoVar[3];
    FZOVarSubType[nr] = fzoVar[4];
    FZOVarListIndex[nr] = fzoVar[5];
    FZOVarValue[nr] = new Array();
    for (var j = 0; j < FZOVarInfo[nr][0]; j++)
      FZOVarValue[nr][j] = -1;
  }
  top.data.display.document.writeln(names[0].replace(/onclick\=\"/gi, 'onclick="top.control.'));
  var lx = Math.round(90 / nx) * 8;
  for (var i = 1; i < names.length; i++)
  {
    var xl = ((""+dim) == "-1" || dim[i+i-2] == -1) ? lx : dim[i+i-2];
    var yl = ((""+dim) == "-1" || dim[i+i-1] == -1) ? 16 : dim[i+i-1];
    if ( (SubType[nr+i] & FZO_f) != 0 )
      ((SubType[nr+i] & FZO_m) != 0 ? PrintTFM : PrintTF)(nr+i, xl, yl, SubType[nr+i]);
    else
      if ( top.data.display.document.layers )
        ((SubType[nr+i] & FZO_m) != 0 ? PrintTLM : PrintTLS)(nr+i, xl, yl, SubType[nr+i]);
      else {
        Spring[nr+i] = '<span style="width:'+(((""+dim) == "-1" || dim[i+i-2] == -1) ? Math.round(30 / nx) * 8 : dim[i+i-2])+'px;height:0px;"><B></B></span>';
        if ( (SubType[nr+i] & FZO_m) != 0 ) {
          var s = TLMString(nr+i);
          if ( s == "" ) s = "&nbsp;"+Spring[nr+i];
          top.data.display.document.write('<SPAN id=a', nr+i, '>', s, '</SPAN>');
        }
        else
          top.data.display.document.write(m[nr+i] != "\x01-1" ? EntryToText(m[nr+i]) : ' &nbsp;', Spring[nr+i]);
      }
    top.data.display.document.writeln(names[i].replace(/onclick\=\"/gi, 'onclick="top.control.'));
  }
  PrintSLS(nr, cols > 0 ? cols : 1);
}

function CheckFZO(nr, nTarget)
{
  if ( FZOVar[nr] )
  {
    FZOVarTar[nr] = new Array();
    for (var j = 0; j < FZOVar[nr].length; j++)
      FZOVarTar[nr][j] = new Array();
  }
  // HardCheck ... harte Zuordnungen pr�fen, FZOVarTar f�llen
  for (var i = nr+1; i <= nr+nTarget; i++)
    if ( (SubType[i] & FZO_u) == 0 )
      HardCheck[(SubType[i] >> 2) & 3](nr, i, false);
  // VAR-Zeug
  if ( FZOVar[nr] )
  {
    // "Abgestrichen"-Feld erzeugen, f�r vorgeschriebene Felder (Var) und tats�chliche (VarTar)
    var Done = new Array();
    var DoneTar = new Array();
    for (var v1 = 0; v1 < FZOVar[nr].length; v1++)
      if ( IsFZOVarTarget(nr, v1) )
      {
        Done[v1] = new Array();
        for (var v2 = 0; v2 < FZOVar[nr][v1].length; v2 += 2)
          Done[v1][v2] = 0
        DoneTar[v1] = new Array();
        for (var v3 = 0; v3 < FZOVarTar[nr][v1].length; v3 += 5)
          DoneTar[v1][v3] = 0
      }
    // passend harte Felder schon mal rausnehmen
    for (var v1 = 0; v1 < FZOVar[nr].length; v1++)
      if ( IsFZOVarTarget(nr, v1) )
        for (var v2 = 0; v2 < FZOVar[nr][v1].length; v2 += 2)
          if ( FZOVar[nr][v1][v2] >= -1 && FZOVar[nr][v1][v2+2] >= -1 )
            for (var v3 = 0; v3 < FZOVarTar[nr][v1].length; v3 += 5)
              if ( FZOMatch(FZOVar[nr][v1][v2], FZOVar[nr][v1][v2+1], FZOVarTar[nr][v1][v3], FZOVarTar[nr][v1][v3+1], 0) )
              {
                Done[v1][v2] = 1;
                DoneTar[v1][v3] = 1;
                Check[FZOVarTar[nr][v1][v3+2]][FZOVarTar[nr][v1][v3+3]] = 3;
                ApplyCorr(nr, FZOVarTar[nr][v1][v3+2], FZOVarTar[nr][v1][v3+3], FZOVarTar[nr][v1][v3+4]);
              }
    // Var's mit den wahrscheinlichsten Werten f�llen
    var vec = GetVarVectors(nr, Done, DoneTar);
    var Vars = new Array();
    for (var v = 0; v < FZOVarInfo[nr][0]; v++)
    {
      // Maximum im Verteilungsvektor finden
      var j = 0;
      var sum = vec[v][0];
      for (var v1 = 1; v1 < FZOVarInfo[nr][1]; v1++) {
        sum += vec[v][v1];
        if ( vec[v][v1] > vec[v][j] )
          j = v1;
      }
      FZOVarValue[nr][v] = j;
      if ( sum != 1 && vec[v][j] <= 1 ) // wenn Variable nicht vorkommt oder immer
        j = -1;                      // nur einfach, dann gibt es kein matchen
      Vars[v] = j;                   // Ausnahme: kommt genau einmal vor (sum == 1)
    }
    var VarOK = new Array();
    var VarValUsed = new Array();
    SolveVarCollision(Vars, vec, nr, nTarget, VarOK, VarValUsed);
    // Items durchgehen, checken und abstreichen
    if ( FZOVarInfo[nr][0] >= 0 )
      for (var v1 = 0; v1 < FZOVar[nr].length; v1++)
        if ( IsFZOVarTarget(nr, v1) )
          for (var v2 = 0; v2 < FZOVar[nr][v1].length; v2 += 2)
            if ( Done[v1][v2] == 0 )
              for (var v3 = 0; v3 < FZOVarTar[nr][v1].length; v3 += 5)
                if ( DoneTar[v1][v3] == 0 )
                  if ( FZOMatch(FZOVar[nr][v1][v2], FZOVar[nr][v1][v2+1], FZOVarTar[nr][v1][v3], FZOVarTar[nr][v1][v3+1], Vars) )
                  {
                    Done[v1][v2] = 1;
                    DoneTar[v1][v3] = 1;
                    var nr1 = FZOVarTar[nr][v1][v3+2];
                    var i = FZOVarTar[nr][v1][v3+3];
                    ApplyCorr(nr, nr1, i, FZOVarTar[nr][v1][v3+4]);
                    Check[nr1][i] = 3;
                    // TFSynUsed angeben
                    if ( (SubType[nr1] & FZO_fm) == FZO_fm ) {
                      TFSynUsed[nr1][i] = -100-v1;
                      for (var j = 1; j < i; j++)
                        if ( TFSynUsed[nr1][j] == -100-v1 )
                          TFSynUsed[nr1][i] = j;
                    }
                  }
  }
  // Free Targets neu drucken
  for (var i = nr+1; i <= nr+nTarget; i++)
    if ( (SubType[i] & FZO_u) == 0 )
      if ( (SubType[i] & FZO_f) != 0 )
        SetT(i, ((SubType[i] & FZO_m) != 0) ? m[i].slice(1, m[i].length).join("; ") : m[i]);
  // jetzt aus dem ganzen einen Zahlenwert basteln
  var nOk = 0;
  var nCount = 0;
  // Harte Werte zusammenz�hlen
  for (var i = 1; i <= nTarget; i++)
    if ( (SubType[nr+i] & FZO_u) == 0 )
      if ( (SubType[nr+i] & FZO_f) != 0 ) { // TF, TFM
        if ( (SubType[nr+i] & FZO_m) != 0 ) { // TFM
          var nOk1 = 0;   // counten ohne Var's
          var nCount1 = 0;
          for (var j = 1; j <= m[nr+i].length; j++)
            if ( Check[nr+i][j] % 2 == 0 )
            {
              nCount1++;
              if ( Check[nr+i][j] == 2 )
                nOk1++
            }
          nOk += nOk1;
          nCount += nOk1+(d[nr+i].length-nOk1)+(nCount1-nOk1); // richtige+(fehlende)+(zuviele)
        }
        else { // TF
          if ( m[nr+i] == "" ) // leer
          {
            if ( d[nr+i].length != 0 )
              nCount++;
          }
          else if ( Check[nr+i][1] % 2 == 0 ) // was drin und kein Var
          {
            if ( Check[nr+i][1] == 2 )
              nOk++;
            if ( d[nr+i].length != 0 )
              nCount++;
          }
        }
      }
      else if ( (SubType[nr+i] & FZO_m) != 0 ) { // TLM
        var nOk1 = 0;   // counten ohne Var's
        var nCount1 = 0;
        for (var j = 1; j <= m[nr+i][0]; j++)
          if ( Check[nr+i][j] % 2 == 0 )
          {
            nCount1++;
            if ( Check[nr+i][j] == 2 )
              nOk1++
          }
        nOk += nOk1;
        nCount += nOk1+(d[nr+i][2].length-nOk1)+(nCount1-nOk1); // richtige+(fehlende)+(zuviele)
      }
      else if ( (SubType[nr+i] & FZO_f) == 0 ) { // TLS
        if ( m[nr+i] == "\x01-1" ) // leer
        {
          if ( d[nr+i][2] != "\x01-1" )
            nCount++;
        }
        else if ( Check[nr+i][1] % 2 == 0 ) // was drin und kein Var
        {
          if ( Check[nr+i][1] == 2 )
            nOk++;
          if ( d[nr+i][2] != "\x01-1" )
            nCount++;
        }
      }
  // Var-Werte sammeln
  if ( FZOVar[nr] )
    for (var v1 = 0; v1 < FZOVar[nr].length; v1++)
      if ( IsFZOVarTarget(nr, v1) )
      {
        var nOk1 = 0;
        for (var v2 = 0; v2 < FZOVar[nr][v1].length; v2 += 2)
          if ( Done[v1][v2] == 1 )
            nOk1++;
        nOk += nOk1;
        nCount += nOk1+(FZOVar[nr][v1].length/2-nOk1)+(FZOVarTar[nr][v1].length/5-nOk1); // richtige+(fehlende)+(zuviele)
//        alert(nOk1+" "+(nOk1+(FZOVar[nr][v1].length/2-nOk1)+(FZOVarTar[nr][v1].length/5-nOk1)));
      }
  return (nCount > 0) ? nOk/nCount : 0;
}

// Hilfsfunktionen wegen Var

function IsFZOVarTarget(fzoNr, i)
{
  return !(!FZOVar[fzoNr] || !FZOVar[fzoNr][i] || !FZOVar[fzoNr][i][1] || (FZOVarSubType[fzoNr][i] & FZO_u) != 0);
}

function IsFZOVarTargetU(fzoNr, i)
{
  return !(!FZOVar[fzoNr] || !FZOVar[fzoNr][i] || !FZOVar[fzoNr][i][1] || (FZOVarSubType[fzoNr][i] & FZO_u) == 0);
}

function HardCheckTLM(fzoNr, nr, addMissing)
{
  Check[nr] = new Array("check");
  for (var j = 1; j <= m[nr][0]; j++)
  {
    iSource = -1; // Nr. des Quell-items
    var a = m[nr][j].split("\x01");
    if ( a.length > 2 && a[2] == fzoNr ) {
      iSource = a[1];
      if ( IsFZOVarTarget(fzoNr, iSource) )
      {
         var offsetCoord = (nr-fzoNr-1)*2;
         FZOVarTar[fzoNr][iSource] = FZOVarTar[fzoNr][iSource].concat(
           new Array(FZOVarCoord[fzoNr][offsetCoord++], FZOVarCoord[fzoNr][offsetCoord], nr, j, ""));
         Check[nr][j] = 1;
      }
      else
        Check[nr][j] = 0;
    }
    else {
      Check[nr][j] = OfferTextToVarsL(fzoNr, nr, m[nr][j], j) ? 1 : 0;
    }
  }
  // harte Werte vergleichen
  for (var i = 0; i < d[nr][2].length; i++)
  {
    var missing = true;
    for (var j = 1; j <= m[nr][0]; j++)
      if ( Entry2CmpText(m[nr][j]) == Entry2CmpText(d[nr][2][i]) && Check[nr][j] == 0 )
      {
        Check[nr][j] = 2;
        InformListSLS(nr, d[nr][2][i]);
        missing = false;
        break;
      }
    if ( addMissing && missing ) {
      m[nr][++m[nr][0]] = d[nr][2][i];
      InformListSLS(nr, d[nr][2][i]);
      Check[nr][m[nr][0]] = 2;
    }
  }
}

function HardCheckTLS(fzoNr, nr, addMissing)
{
  Check[nr] = new Array("check");
  if ( m[nr] == "\x01-1" ) {
    if ( addMissing && d[nr][2] != "\x01-1" ) {
      //m[nr] = d[nr][2];
      SetTLSEntry(nr, d[nr][2]);
      Check[nr][1] = 2;
    }
    return;
  }
  iSource = -1; // Nr. des Quell-items
  var a = m[nr].split("\x01");
  if ( a.length > 2 && a[2] == fzoNr ) {
    iSource = a[1];
    if ( IsFZOVarTarget(fzoNr, iSource) )
    {
      var offsetCoord = (nr-fzoNr-1)*2;
        FZOVarTar[fzoNr][iSource] = FZOVarTar[fzoNr][iSource].concat(
           new Array(FZOVarCoord[fzoNr][offsetCoord++], FZOVarCoord[fzoNr][offsetCoord], nr, 1, ""));
      Check[nr][1] = 1;
    }
    else
      Check[nr][1] = 0;
  }
  else {
    Check[nr][1] = OfferTextToVarsL(fzoNr, nr, m[nr], 1) ? 1 : 0;
  }
  // wenn ein Wert da, dann hart vergleichen
  if ( Entry2CmpText(m[nr]) == Entry2CmpText(d[nr][2]) ) {
    Check[nr][1] = 2;
    InformListSLS(nr, d[nr][2]);
  }
  else if ( addMissing && d[nr][2] != "\x01-1" ) {
//    m[nr] = d[nr][2];
    SetTLSEntry(nr, d[nr][2]);
    Check[nr][1] = 2;
  }

//  if ( m[nr] == d[nr][2] )
//    Check[nr][1] = 2;
}

function HardCheckTF(fzoNr, nr, addMissing)
{
  Check[nr] = new Array("check", 0);
  TFSynUsed[nr] = -1;
  if ( m[nr] == '' ) {
    if ( addMissing && d[nr].length > 0 ) {
      m[nr] = d[nr][0];
      Check[nr][1] = 2;
    }
    return;
  }
  // Harter Wert?
  if ( d[nr].length > 0 )
    for (i = 0; i < d[nr].length; i++)
      if ( StrCmp(m[nr], d[nr][i]) >= 0.9 )
      {
        Check[nr][1] = 2;
        m[nr] = d[nr][i]; // Rechtschreibung
        TFSynUsed[nr] = i;
        return;
      }
  // Testen auf Var-Wert
  if ( OfferTextToVarsF(fzoNr, nr, m[nr], 1) )
    Check[nr][1] = 1;
  else if ( addMissing && d[nr].length > 0 ) {
    m[nr] = d[nr][0];
    Check[nr][1] = 2;
  }
}

function HardCheckTFM(fzoNr, nr, addMissing)
{
  Check[nr] = new Array("check");
  m[nr] = new Array(m[nr][0].replace(/\s*\[\+\]\s*/g, "").replace(/\s*\[-\]\s*/g, "").replace(/\s*\[=\]\s*/g, ";").replace(/;+/g, ";"));  // restliche evtl. vorhandene Felder l�schen
  if ( m[nr][0] == '' ) {
    if ( addMissing )
      for (i = 0; i < d[nr].length; i++) {
        m[nr][i+1] = d[nr][i][0];
        Check[nr][i+1] = 2;
        TFSynUsed[nr][i+1] = -i;
      }
    return;
  }
  // zerlegen in Token
  var tok = m[nr][0].replace(/\r/g, " ").replace(/\n/g, " ").split(";");
  for (var i = 0; i < tok.length; i++)
    m[nr][i+1] = CivilizeBlanks(tok[i]);
  Check[nr][0] = tok.length;
  // AbstreichFeld f�r die harten Synonymgruppen
  var HardTFMRegoniced = new Array(); // nr. des passenden Wortes, 0 .. noch frei
  for (var k = 0; k < d[nr].length; k++)
    HardTFMRegoniced[k] = 0;
  // einzelne m-Werte durchchecken
  for (var j = 1; j < m[nr].length; j++) {
    // Harter Wert?
    var run = true;
    TFSynUsed[nr][j] = 0;
    // noch frei Syn-Listen
    for (var k = 0; k < d[nr].length && run; k++)
      if ( HardTFMRegoniced[k] == 0 )
        for (i = 0; i < d[nr][k].length && run; i++)
          if ( StrCmp(m[nr][j], d[nr][k][i]) >= 0.9 )
          {
            Check[nr][j] = 2;
            HardTFMRegoniced[k] = j;
            TFSynUsed[nr][j] = -k;
            m[nr][j] = d[nr][k][i]; // Rechtschreibkorr.
            run = false;
          }
    // bereits benutzte Syn-Listen
    for (var k = 0; k < d[nr].length && run; k++)
      if ( HardTFMRegoniced[k] != 0 )
        for (i = 0; i < d[nr][k].length && run; i++)
          if ( StrCmp(m[nr][j], d[nr][k][i]) >= 0.9 )
          {
            Check[nr][j] = 2;
            TFSynUsed[nr][j] = HardTFMRegoniced[k];
            m[nr][j] = d[nr][k][i]; // Rechtschreibkorr.
            run = false;
          }
    // Var-Wert?
    if ( run )
      Check[nr][j] = OfferTextToVarsF(fzoNr, nr, m[nr][j], j) ? 1 : 0;
  }
  if ( addMissing )
    for (var k = 0; k < d[nr].length; k++)
      if ( HardTFMRegoniced[k] == 0 ) {
        var j = m[nr].length;
        m[nr][j] = d[nr][k][0];
        Check[nr][j] = 2;
        TFSynUsed[nr][j] = -k;
      }
}

function FZOPossibleMatch(val, itemVal)
{
  return ( val == itemVal || itemVal < 0);
}

function FZOPossibleMatchItem(x, y, fzoVar)
{
  for (var v2 = 0; v2 < fzoVar.length; v2 += 2)
    if ( FZOPossibleMatch(x, fzoVar[v2]) && FZOPossibleMatch(y, fzoVar[v2+1]) )
      return true;
  return false;
}

function Coord2Tar(x, y, nr, nTarget) { // darf kein Star sein
  // Variablen einsetzen
  if ( x <= -2 )
    x = FZOVarValue[nr][-2-x];
  if ( y <= -2 )
    y = FZOVarValue[nr][-2-y];
  // Werte suchen
  for (var i = 0; i < nTarget; i++)
    if ( FZOVarCoord[nr][i+i] == x && FZOVarCoord[nr][i+i+1] == y )
      return nr+1+i;
  return -1;
}

function OfferTextToVarsF(fzoNr, nr, s, i)
{
  var flag = false;
  if ( FZOVar[fzoNr] ) {
    var offsetCoord = (nr-fzoNr-1)*2;
    var x = FZOVarCoord[fzoNr][offsetCoord++];
    var y = FZOVarCoord[fzoNr][offsetCoord];
    for (var v1 = 0; v1 < FZOVar[fzoNr].length; v1++)
      if ( IsFZOVarTarget(fzoNr, v1) && FZOPossibleMatchItem(x, y, FZOVar[fzoNr][v1]) )
        for (var j = 1; j <= FZOVarText[fzoNr][v1][0]; j++) {
          if ( StrCmp(s, FZOVarText[fzoNr][v1][j]) >= 0.9 )
          {
            FZOVarTar[fzoNr][v1] = FZOVarTar[fzoNr][v1].concat(new Array(x, y, nr, i, FZOVarText[fzoNr][v1][j]));
            flag = true;
            break;
          }
        }
  }
  return flag;
}

function OfferTextToVarsL(fzoNr, nr, s, i)
{
  var s1 = HTML2MetaHTML(s);
  var flag = false;
  if ( FZOVar[fzoNr] ) {
    var offsetCoord = (nr-fzoNr-1)*2;
    var x = FZOVarCoord[fzoNr][offsetCoord++];
    var y = FZOVarCoord[fzoNr][offsetCoord];
    for (var v1 = 0; v1 < FZOVar[fzoNr].length; v1++)
      if ( IsFZOVarTarget(fzoNr, v1) && FZOPossibleMatchItem(x, y, FZOVar[fzoNr][v1]) )
        for (var j = 1; j <= FZOVarText[fzoNr][v1][0]; j++) {
          if ( s1 == HTML2MetaHTML(FZOVarText[fzoNr][v1][j]) )
          {
            FZOVarTar[fzoNr][v1] = FZOVarTar[fzoNr][v1].concat(new Array(x, y, nr, i, ""));
            flag = true;
            break;
          }
        }
  }
  return flag;
}

function FZOMatch(x, y, xTar, yTar, vars)
{
  if ( x >= 0 && x != xTar )
    return false;
  if ( x < -1 && vars[-2-x] != xTar )
    return false
  if ( y >= 0 && y != yTar )
    return false;
  if ( y < -1 && vars[-2-y] != yTar )
    return false;
  return true;
}

function ClearMTLM(fzoNr, nr)
{
  var p = 1;
  for (var j = 1; j <= m[nr][0]; j++)
    if ( Check[nr][j] >= 2 )
      m[nr][p++] = m[nr][j];
  m[nr][0] = p-1;
}

function ClearMTLS(fzoNr, nr)
{
  if ( Check[nr][1] < 2 )
    m[nr] = "\x01-1";
}

function ClearMTF(fzoNr, nr)
{
  if ( Check[nr][1] < 2 )
    m[nr] = "";
}

function ClearMTFM(fzoNr, nr)
{
  var p = 1;
  var m1 = new Array()
  for (var j = 1; j < m[nr].length; j++)
    if ( Check[nr][j] >= 2 )
      m1[p++] = m[nr][j];
  m1[0] = m1.slice(1, m1.length).join("; ");
  m[nr] = m1;
}

function AddVarValue(fzoNr, tarNr, v1)
{
  if ( (SubType[tarNr] & FZO_f) == FZO_f ) {
    if ( (SubType[tarNr] & FZO_m) == FZO_m ) {
      m[tarNr][m[tarNr].length] = FZOVarText[fzoNr][v1][1];
      Check[tarNr][m[tarNr].length-1] = 3;
    }
    else {
      m[tarNr] = FZOVarText[fzoNr][v1][1];
      Check[tarNr][1] = 3;
    }
  }
  else if ( (SubType[tarNr] & FZO_m) == FZO_m ) {
    for (var i = 1; i < FZOVarText[fzoNr][v1].length; i++) {
      m[tarNr][++m[tarNr][0]] = FZOVarText[fzoNr][v1][i];
      Check[tarNr][m[tarNr][0]] = 3;
    }
  }
  else {
    m[tarNr] = FZOVarText[fzoNr][v1][1];
    Check[tarNr][1] = 3;
  }
}

function AddUVarValue(fzoNr, tarNr, v1)
{

  if ( (SubType[tarNr] & FZO_f) != 0 ) {
    m[tarNr][0] += (m[tarNr][0] != "" ? "; " : "")+FZOVarText[fzoNr][v1].slice(1, FZOVarText[fzoNr][v1].length).join("; ");
    SetT(tarNr, m[tarNr][0]);
  }
  else {
    for (var i = 1; i < FZOVarText[fzoNr][v1].length; i++)
      m[tarNr][++m[tarNr][0]] = FZOVarText[fzoNr][v1][i];
    RefreshTLM(tarNr);
  }
}

function AddUVarValueSolveText(fzoNr, tarNr, v1)
{

  if ( (SubType[tarNr] & FZO_f) != 0 ) {
    m[tarNr][1] += (m[tarNr][1] != "" ? "; " : "")+FZOVarText[fzoNr][v1].slice(1, FZOVarText[fzoNr][v1].length).join("; ");
//    alert(FZOVarText[fzoNr][v1].slice(1, FZOVarText[fzoNr][v1].length).join("; "));
    m[tarNr][0] += (m[tarNr][0] != "" ? "; " : "")+FZOVarText[fzoNr][v1].slice(1, FZOVarText[fzoNr][v1].length).join("; ");
  }
  else {
    for (var i = 1; i < FZOVarText[fzoNr][v1].length; i++)
      m[tarNr][++m[tarNr][0]] = FZOVarText[fzoNr][v1][i];
  }
}

function PossibleVarValue(v, val, nr, nTarget)
{
  for (var v1 = 0; v1 < FZOVar[nr].length; v1++)
    if ( IsFZOVarTarget(nr, v1) || IsFZOVarTargetU(nr, v1) )
      for (var v2 = 0; v2 < FZOVar[nr][v1].length; v2 += 2)
        if ( FZOVar[nr][v1][v2] == -2-v && FZOVar[nr][v1][v2+1] >= 0 ) {
          if ( Coord2Tar(val, FZOVar[nr][v1][v2+1], nr, nTarget) == -1 )
            return false;
        }
        else if ( FZOVar[nr][v1][v2] >= 0 && FZOVar[nr][v1][v2+1] == -2-v ) {
          if ( Coord2Tar(FZOVar[nr][v1][v2], val, nr, nTarget) == -1 )
            return false;
        }
  return true;
}

function GetTargetFill(nr)
{
  if ( (SubType[nr] & FZO_u) != 0 )
    return 0;
  var v = ((SubType[nr] & FZO_m) != 0) ? 1000 : 1;
  if ( (SubType[nr] & FZO_m) == 0 ) { // Single
    if ( Check[nr][1] >= 2 )
      v--;
  }
  else { // Multi
    var l = ((SubType[nr] & FZO_f) != 0) ? m[nr].length : m[nr][0];
    for (var i = 1; i < l; i++)
      if ( Check[nr][i] >= 2 )
        v--;
  }
  return v;
}

function GetVarVectors(nr, Done, DoneTar)
{
  var vec = new Array();
  for (var v = 0; v < FZOVarInfo[nr][0]; v++) {
    // Verteilungsvector bereitstellen
    vec[v] = new Array();
    for (var v1 = 0; v1 < FZOVarInfo[nr][1]; v1++)
      vec[v][v1] = 0;
    // Verteilungsvector f�llen
    for (var v1 = 0; v1 < FZOVar[nr].length; v1++)
      if ( IsFZOVarTarget(nr, v1) )
        for (var v2 = 0; v2 < FZOVar[nr][v1].length; v2 += 2)
          if ( Done[v1][v2] == 0 )
            if ( FZOVar[nr][v1][v2] == -2-v ) {
              for (var v3 = 0; v3 < FZOVarTar[nr][v1].length; v3 += 5)
                if ( DoneTar[v1][v3] == 0 )
                  vec[v][FZOVarTar[nr][v1][v3]]++;
            }
            else if ( FZOVar[nr][v1][v2+1] == -2-v ) {
              for (var v3 = 0; v3 < FZOVarTar[nr][v1].length; v3 += 5)
                if ( DoneTar[v1][v3] == 0 )
                  vec[v][FZOVarTar[nr][v1][v3+1]]++;
            }
  }
  return vec;
}

function SolveVarCollision(Vars, vec, nr, nTarget, VarOK, VarValUsed)
{
  // Variablen Kollision aufl�sen, Variablen mit absteigender Priorit�t einordnen und best�tigen
  for (var v = 0; v < FZOVarInfo[nr][0]; v++)
    VarOK[v] = 0;
  for (var v1 = 0; v1 < FZOVarInfo[nr][1]; v1++)
    VarValUsed[v1] = 0;
  var run = true;
  while ( run ) {
    var i = -1;
    for (var v = 0; v < FZOVarInfo[nr][0]; v++)
      if ( Vars[v] != -1 && VarOK[v] == 0 && (i == -1 || vec[v][Vars[v]] > vec[i][Vars[i]]) )
        i = v;
    if ( i == -1 )
      break;
    // i ist jetzt die zu betrachtende Variable
    // testen, ob Kollision
    while ( true ) {
      var j = 0;
      for (var v1 = 1; v1 < FZOVarInfo[nr][1]; v1++)
        if ( vec[i][v1] > vec[i][j] )
          j = v1;
      if ( vec[i][j] < 0 ) {
        alert("Can not fullfill this exercise properly. Sorry.");
        Vars[i] = 0;
        VarOK[i] = 1;
        VarValUsed[0] = 1;
        break;
      }
      // test auf Kollision
      if ( VarValUsed[j] != 0 || PossibleVarValue(i, j, nr, nTarget) == false )
        vec[i][j] = -1;
      else {
        Vars[i] = j;
        VarOK[i] = 1;
        VarValUsed[j] = 1;
        break;
      }
    }
  }
}

function FZOInformListVar(fzoNr, varNr, tarNr)
{
  if ( (""+m[fzoNr]) != "-1" && FZOVarListIndex[fzoNr][varNr] != -1 && m[fzoNr][FZOVarListIndex[fzoNr][varNr]] == -1 )
    m[fzoNr][FZOVarListIndex[fzoNr][varNr]] = tarNr;
}

function ApplyCorr(fzoNr, tarNr, tari, val)
{
  if ( (SubType[tarNr] & FZO_f) == 0 || val == "" )
    return;
  if ( (SubType[tarNr] & FZO_m) != 0 )
    m[tarNr][tari] = val;
  else
    m[tarNr] = val;
}

function Clone(a)
{
  if ( a.length )
  {
    var b = new Array();
    for (var i = 0; i < a.length; i++)
      b[i] = Clone(a[i]);
    return b;
  }
  else
    return a;
}

function SolveTextFZO(nr, nTarget)
{
  if ( FZOVar[nr] )
  {
    FZOVarTar[nr] = new Array();
    for (var j = 0; j < FZOVar[nr].length; j++)
      FZOVarTar[nr][j] = new Array();
  }
  //HardCheckes + fehlende harte Werte drannaddieren
  for (var i = nr+1; i <= nr+nTarget; i++)
    if ( (SubType[i] & FZO_u) == 0 )
      HardCheckSolveText[(SubType[i] >> 2) & 3](nr, i, true);
  // Variablen ermitteln
  // "Abgestrichen"-Feld erzeugen, f�r vorgeschriebene Felder (Var) und tats�chliche (VarTar)
  if ( FZOVar[nr] )
  {
    var Done = new Array();
    var DoneTar = new Array();
    for (var v1 = 0; v1 < FZOVar[nr].length; v1++)
      if ( IsFZOVarTarget(nr, v1) )
      {
        Done[v1] = new Array();
        for (var v2 = 0; v2 < FZOVar[nr][v1].length; v2 += 2)
          Done[v1][v2] = 0
        DoneTar[v1] = new Array();
        for (var v3 = 0; v3 < FZOVarTar[nr][v1].length; v3 += 5)
          DoneTar[v1][v3] = 0
      }
    // passend harte Felder schon mal rausnehmen
    for (var v1 = 0; v1 < FZOVar[nr].length; v1++)
      if ( IsFZOVarTarget(nr, v1) )
        for (var v2 = 0; v2 < FZOVar[nr][v1].length; v2 += 2)
          if ( FZOVar[nr][v1][v2] >= -1 && FZOVar[nr][v1][v2+2] >= -1 )
            for (var v3 = 0; v3 < FZOVarTar[nr][v1].length; v3 += 5)
              if ( FZOMatch(FZOVar[nr][v1][v2], FZOVar[nr][v1][v2+1], FZOVarTar[nr][v1][v3], FZOVarTar[nr][v1][v3+1], 0) )
              {
                Done[v1][v2] = 1;
                DoneTar[v1][v3] = 1;
                Check[FZOVarTar[nr][v1][v3+2]][FZOVarTar[nr][v1][v3+3]] = 3;
                FZOInformListVar(nr, v1, FZOVarTar[nr][v1][v3+2]);
                ApplyCorr(nr, FZOVarTar[nr][v1][v3+2], FZOVarTar[nr][v1][v3+3], FZOVarTar[nr][v1][v3+4]);
              }
    // Var's mit den wahrscheinlichsten Werten f�llen
    var Vars = new Array();
    var vec = GetVarVectors(nr, Done, DoneTar);
    for (var v = 0; v < FZOVarInfo[nr][0]; v++)
    {
      // Maximum im Verteilungsvector finden
      var j = 0;
      for (var v1 = 1; v1 < FZOVarInfo[nr][1]; v1++)
        if ( vec[v][v1] > vec[v][j] )
          j = v1;
      Vars[v] = (vec[v][j] > 0 ) ? j : -1;
    }
    var VarOK = new Array();
    var VarValUsed = new Array();
    SolveVarCollision(Vars, vec, nr, nTarget, VarOK, VarValUsed);
    // restliche Variablen f�llen
    for (var v = 0; v < FZOVarInfo[nr][0]; v++)
      if ( VarOK[v] == 0 ) {
        Vars[v] = 0;
        for (var v1 = 0; v1 < FZOVarInfo[nr][1]; v1++)
          if ( VarValUsed[v1] == 0 && PossibleVarValue(v, v1, nr, nTarget) == true ) {
            Vars[v] = v1;
            VarValUsed[v1] = 1;
            break;
          }
          VarOK[v] = 1;
      }
    FZOVarValue[nr] = Vars;
    // Items durchgehen, checken und abstreichen, in die m's einf�llen, ggf. drannaddieren
    for (var v1 = 0; v1 < FZOVar[nr].length; v1++)
      if ( IsFZOVarTarget(nr, v1) )
        for (var v2 = 0; v2 < FZOVar[nr][v1].length; v2 += 2) {
          if ( Done[v1][v2] == 0 )
            for (var v3 = 0; v3 < FZOVarTar[nr][v1].length; v3 += 5)
              if ( DoneTar[v1][v3] == 0 )
                if ( FZOMatch(FZOVar[nr][v1][v2], FZOVar[nr][v1][v2+1], FZOVarTar[nr][v1][v3], FZOVarTar[nr][v1][v3+1], Vars) )
                {
                  Done[v1][v2] = 1;
                  DoneTar[v1][v3] = 1;
                  var nr1 = FZOVarTar[nr][v1][v3+2];
                  var i = FZOVarTar[nr][v1][v3+3];
                  Check[nr1][i] = 3;
                  ApplyCorr(nr, nr1, i, FZOVarTar[nr][v1][v3+4]);
                  FZOInformListVar(nr, v1, nr1);
                }
          if ( Done[v1][v2] == 0 && FZOVar[nr][v1][v2] != -1 && FZOVar[nr][v1][v2+1] != -1 ) { // Wert dranaddieren
            var tarNr = Coord2Tar(FZOVar[nr][v1][v2], FZOVar[nr][v1][v2+1], nr, nTarget);
            AddVarValue(nr, tarNr, v1);
            Done[v1][v2] = 1;
          }
        }
    // Star-eintr�ge setzen ( es sind nur noch dranzuaddierende �brig )
    var TargetFill = new Array();
    for (var v1 = 0; v1 < FZOVar[nr].length; v1++)
      if ( IsFZOVarTarget(nr, v1) )
        for (var v2 = 0; v2 < FZOVar[nr][v1].length; v2 += 2)
          if ( Done[v1][v2] == 0 ) {
            if ( TargetFill.length == 0 )
              for (var i = 1; i <= nTarget; i++)
                TargetFill[i] = GetTargetFill(nr+i);
            var tarMax = -1;
            for (var x = (FZOVar[nr][v1][v2] == -1) ? 0 : FZOVar[nr][v1][v2]; x <= ((FZOVar[nr][v1][v2] == -1) ? FZOVarInfo[nr][1]-1 : FZOVar[nr][v1][v2]); x++)
              for (var y = (FZOVar[nr][v1][v2+1] == -1) ? 0 : FZOVar[nr][v1][v2+1]; y <= ((FZOVar[nr][v1][v2+1] == -1) ? FZOVarInfo[nr][1]-1 : FZOVar[nr][v1][v2+1]); y++) {
                tarNr = Coord2Tar(x, y, nr, nTarget);
                if ( tarNr != -1 ) {
                  if ( tarMax == -1 || TargetFill[tarNr-nr] > TargetFill[tarMax-nr] )
                    tarMax = tarNr;
                }
              }
            if ( tarMax == -1 )
              alert("Can not fullfill this exercise properly. Sorry.");
            else {// drannaddieren
              AddVarValue(nr, tarMax, v1);
              FZOInformListVar(nr, v1, tarMax);
              TargetFill[tarMax]--;
            }
          }
  }
  // aus den m's die falschen Werte rausstreichen
  for (var i = nr+1; i <= nr+nTarget; i++)
    if ( (SubType[i] & FZO_u) == 0 )
      ClearM[(SubType[i] >> 2) & 3](nr, i);
  // u's drucken
  // harte u's
  for (var i = 1; i <= nTarget; i++)
    if ( (SubType[nr+i] & FZO_u) == FZO_u )
      if ( (SubType[nr+i] & FZO_f) != 0 ) {
        m[nr+i] = new Array(0, "");
        for (var j = 0; j < d[nr+i].length; j++)
          m[nr+i][1] += (m[nr+i][1] == "" ? "" : "; ") +d[nr+i][j].join("; ");
      }
      else {
        m[nr+i][0] = 0;
        for (var j = 0; j < d[nr+i][2].length; j++)
          m[nr+i][++m[nr+i][0]] = d[nr+i][2][j];
      }
  // Var u's
  if ( FZOVar[nr] ) {
    // Vars
    for (var v1 = 0; v1 < FZOVar[nr].length; v1++)
      if ( IsFZOVarTargetU(nr, v1) )
        for (var j = 0; j < FZOVar[nr][v1].length; j += 2)
          if ( FZOVar[nr][v1][j] != -1 && FZOVar[nr][v1][j+1] != -1) {
            var tarNr = Coord2Tar(FZOVar[nr][v1][j], FZOVar[nr][v1][j+1], nr, nTarget);
            AddUVarValueSolveText(nr, tarNr, v1);
          }
    // Stars
    for (var v1 = 0; v1 < FZOVar[nr].length; v1++)
      if ( IsFZOVarTargetU(nr, v1) )
        for (var v2 = 0; v2 < FZOVar[nr][v1].length; v2 += 2)
          if ( FZOVar[nr][v1][v2] == -1 || FZOVar[nr][v1][v2+1] == -1) {
            var tarMax = -1;
            for (var x = (FZOVar[nr][v1][v2] == -1) ? 0 : FZOVar[nr][v1][v2]; x <= ((FZOVar[nr][v1][v2] == -1) ? FZOVarInfo[nr][1]-1 : FZOVar[nr][v1][v2]); x++)
              for (var y = (FZOVar[nr][v1][v2+1] == -1) ? 0 : FZOVar[nr][v1][v2+1]; y <= ((FZOVar[nr][v1][v2+1] == -1) ? FZOVarInfo[nr][1]-1 : FZOVar[nr][v1][v2+1]); y++) {
                tarNr = Coord2Tar(x, y, nr, nTarget);
                if ( tarNr != -1 && (SubType[tarNr] & FZO_u) != 0 )
                  if ( (FZOVarSubType[nr][v1] & FZO_f) != 0 ) {
                    if ( tarMax == -1 || m[tarMax][0].length > m[tarNr][0].length )
                      tarMax = tarNr;
                  }
                  else
                    if ( tarMax == -1 || m[tarMax][0] > m[tarNr][0] )
                      tarMax = tarNr;
              }
            if ( tarMax == -1 )
              alert("Can not fullfill this exercise properly. Sorry.");
            else // drannaddieren
              AddUVarValueSolveText(nr, tarMax, v1);
          }
  }
  // Ergebnisstring
  var res = Names[nr][0];
  for (var i = 1; i < Names[nr].length; i++)
  {
    var resadd = "";
    if ( (SubType[i] & FZO_f) != 0 )
      resadd = ( (SubType[nr+i] & FZO_m) != 0 ) ? m[nr+i].slice(1, m[nr+i].length).join("; ") : m[nr+i];
    else if ( (SubType[nr+i] & FZO_m) != 0 )
      resadd = TLMString(nr+i);
    else
      resadd = EntryToText(m[nr+i]);
    res += (resadd == "" ? "&nbsp;" : '<B>'+resadd+'</B>') + Names[nr][i];
  }
  return res.replace(/onclick="/ig, 'oclick="');
}


function SetTLSSolveText(nr, val, sourceNr)
{
  var list = d[nr][0];
  if ( m[nr] != "\x01-1" && m[nr].charCodeAt(0) == 1 ) {      // remove old TLS-Contens
    var a = m[nr].split("\x01");
    if ( m[a[2]] != -1 && m[a[2]][a[1]] == nr ) {// SLS and not during "Solve"
      m[a[2]][a[1]] = -1;
    }
  }
  var s = "";
  if ( sourceNr == -1 )
  {
    m[nr] = val;
    s = val;
    val = -1;
  }
  else
  {
    m[nr] = "\x01"+val+(val != -1 ? "\x01"+sourceNr : "");
    if ( val != -1 )
      s = d[sourceNr][val];
    else if ( !lay )
      s = " &nbsp; ";
  }
  if ( sourceNr != -1 && val != -1 && (m[sourceNr] != -1) )
  {
    var old = m[sourceNr][val];  // remove new TLS contens from old position
    m[sourceNr][val] = nr;
    if ( old != -1 )
      SetTLSSolveText(old, -1);
  }
}

function SetTLSEntrySolveText(nr, entry) {
  if ( entry == "\x01-1" )
    SetTLSSolveText(nr, -1, d[nr][0]);
  else if ( entry.charCodeAt(0) == 1 ) {
    var a = entry.split("\x01");
    SetTLSSolveText(nr, a[1], a[2]);
  }
  else
    SetTLSSolveText(nr, entry, -1);
}


function HardCheckSolveTextTLS(fzoNr, nr, addMissing)
{
  Check[nr] = new Array("check");
  if ( m[nr] == "\x01-1" ) {
    if ( addMissing && d[nr][2] != "\x01-1" ) {
      //m[nr] = d[nr][2];
      SetTLSEntrySolveText(nr, d[nr][2]);
      Check[nr][1] = 2;
    }
    return;
  }
  iSource = -1; // Nr. des Quell-items
  var a = m[nr].split("\x01");
  if ( a.length > 2 && a[2] == fzoNr ) {
    iSource = a[1];
    if ( IsFZOVarTarget(fzoNr, iSource) )
    {
      var offsetCoord = (nr-fzoNr-1)*2;
        FZOVarTar[fzoNr][iSource] = FZOVarTar[fzoNr][iSource].concat(
           new Array(FZOVarCoord[fzoNr][offsetCoord++], FZOVarCoord[fzoNr][offsetCoord], nr, 1, ""));
      Check[nr][1] = 1;
    }
    else
      Check[nr][1] = 0;
  }
  else {
    Check[nr][1] = OfferTextToVarsL(fzoNr, nr, m[nr], 1) ? 1 : 0;
  }
  // wenn ein Wert da, dann hart vergleichen
  if ( Entry2CmpText(m[nr]) == Entry2CmpText(d[nr][2]) ) {
    Check[nr][1] = 2;
    InformListSLS(nr, d[nr][2]);
  }
  else if ( addMissing && d[nr][2] != "\x01-1" ) {
    SetTLSEntrySolveText(nr, d[nr][2]);
    Check[nr][1] = 2;
  }
}