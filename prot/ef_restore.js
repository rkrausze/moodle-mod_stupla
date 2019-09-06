// ef_store for TEE  ------------------------------------------------
// TEE, 03.07.2002, TU Dresden, R. Krau�e, EF-Version 0.85

var mStore;
var SolvedStore;
var iHintStore;
var TargetTextStore;
var win;

function SGetElAI(nr, i)
{
  return eval("win.document.forms[0].a"+nr+"["+i+"]");
}

function SGetElA(nr)
{
  return eval("win.document.forms[0].a"+nr);
}

// Store

function SGetElAI(nr, i)
{
  return eval("win.document.forms[0].a"+nr+"["+i+"]");
}

function SGetElA(nr)
{
  return eval("win.document.forms[0].a"+nr);
}

function StoreHard(TheWin)
{
  win = TheWin;
  if ( !win.Type )
    return;
  for (var nr = 0; nr < win.Type.length; nr++)
  {
    if ( eval("win.document.sr"+nr) ) // preprocess SR (MCA)
    {
      var sr = eval("win.document.sr"+nr)
      if ( sr.modified != 0 )
        SGetElA(nr).value = sr.value;
    }
    if ( win.document.applets["fea"+nr] ) // extraprocess MPadJava
    {
      win.m[nr] = win.document.applets["fea"+nr].getMPad();
      continue;
    }
    if ( win.Type[nr] == 0 && !SGetElA(nr) ) // MCR (test wg. step)
      continue;
    else if ( win.Type[nr] == 0 || win.Type[nr] == 3) // MCR + VR
    {
      win.m[nr] = -1;
      var i = 0;
      while ( SGetElAI(nr, i) )
      {
        if ( SGetElAI(nr, i).checked )
          win.m[nr] = i;
        i++;
      }
    }
    else if ( win.Type[nr] == 1 ) // MCP
      win.m[nr] = SGetElA(nr).options.selectedIndex;
    else if ( win.Type[nr] == 2 ) // MCX
    {
      if ( win.d[nr].length == 1 )
        win.m[nr][0] = (SGetElA(nr).checked) ? "1" : "0";
      else
        for (i=0; i < win.d[nr].length; i++)
          win.m[nr][i] = (SGetElAI(nr, i).checked) ? "1" : "0";
    }
    else if ( win.Type[nr] <= 6 ) // VR - LT
      win.m[nr] = SGetElA(nr).value;
  }
  // return DataToString(win.m); // not necessary für moodle prot
}

// Restore

function Restore(TheWin)
{
  win = TheWin;
  RestoreWin();
}

function RestoreWin()
{
  if ( !win ||
       !win.document ||
       !win.document.forms[0] ||
       !win.document.forms[0].elements )
  {
    setTimeout("RestoreWin()", 500);
    return;
  }
  if ( !win.Type )
    return;
  m = mStore.split("|");
  for (var nr = 0; nr < win.Type.length; nr++)
  {
    if ( win.Type[nr] == 0 ) // MCR
    {
      m[nr] = Number(m[nr].replace(/\[mc\]/g, ""))-1;
      if ( m[nr] != -1 && SGetElAI(nr, m[nr]) != 0 )
        SGetElAI(nr, m[nr]).checked = true;
    }
    if ( win.Type[nr] == 1 ) // MCP
      SGetElA(nr).options.selectedIndex = Number(m[nr].replace(/\[mc\]/g, ""))-1;
    else if ( win.Type[nr] == 2 ) // MCX
    {
      m[nr] = m[nr].split(",");
      for (i=0; i < m[nr].length; i++)
        SGetElAI(nr, i).checked = (m[nr][i] == 1);
    }
    else if ( win.Type[nr] <= 7 ) // VR - TF
      SGetElA(nr).value = (typeof m[nr] == "string") ? m[nr].replace(/_/g, " ").replace(/\/\/\/\//g, "\r\n") : m[nr];
    else if ( win.Type[nr] == 8 ) // TFM
    {
      if ( m[nr].indexOf(";") == -1 ) {
        /(\s*)(.*)(\s*),*(.*),\2([^;]*)$/.exec(m[nr]);
        m[nr] = RegExp.$2+(RegExp.$4 != '' ? ','+RegExp.$4 : '');
      }
      else
      {
        /(\s*)(.*)(\s*);(.*),\2([^;]*)$/.exec(m[nr]);
        m[nr] = RegExp.$2+';'+RegExp.$4;
      }
      SGetElA(nr).value = m[nr].replace(/_/g, " ");
    }
    else if ( win.Type[nr] == 9 ) // TLS
      win.SetTLSEntry(nr, m[nr]);
    else if ( win.Type[nr] == 10 ) // TLM
    {
      win.m[nr] = m[nr].split(",");
      win.RefreshTLM(nr);
    }
  }
  for (var nr = 0; nr < win.Type.length; nr++)
  {
    if ( win.Type[nr] == 11 ) // SLS
      win.RefreshSLS(nr);
  }
  if (typeof win.nSolve == "object" )
  {
    for (var i = 0; i < win.nSolve.length; i++)
    {
      win.nSolve[i] = 1;
      NoDiaFlag = 1;
      win.Confirm(i);
    }
  }
  else
  {
    win.nSolve = 1;
    NoDiaFlag = 1;
    win.Confirm();
  }
//  win.Solved = SolvedStore;
//  win.iHint = iHintStore;
}

