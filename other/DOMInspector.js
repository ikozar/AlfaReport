//namespace
if ( typeof( __DOMInspector_js ) == 'undefined' )
   __DOMInspector_js = {};
/**
 * Данная библиотека позволяет просматривать JavaScript Document Object Model для HTML
 * Инспектор активизируется вызовом функции inspectDOM ( obj, rej ) 
 *
 * Copyleft by Alexsander A. Tolmachev
 * Доработал Козарь. ( rej - битовая маска из констант см. ниже, сортировка свойств по имени )
 * Доработал Малютин. Добавлено средство отладки Watcher, ф-ция alertEx(). Для включения отладчика объявите глоб. перем. $pbIsWatcher = 1
**/

WITHOUT_CONST			= 1;	// Не показывать константы
WITHOUT_FUNC			= 2;	// Не показывать функции
WITHOUT_FUNCTION		= 2;	// Не показывать функции
WITH_COMPARE			= 4;	// Показать "старые" значения, если значения отличаются
WITH_NOSORT			= 8;	// НеСортировать свойства по имени
//$pbIsWatcher = 1;
function _hsc_ ( v ) { // HTML special characters
    /*
    var esc_str = String(v);

    esc_str = esc_str.replace(/&/g,"&amp;");
    esc_str = esc_str.replace(/</g,"&lt;"); //>
    esc_str = esc_str.replace(/>/g,"&gt;");

    return esc_str;
    */

    var d = document.createElement("div");
    d.appendChild( document.createTextNode(v) );
    return d.innerHTML;
}

function _href_to_next_obj_ ( el, next_obj_str, rej ) {
    var next_href = "javascript: opener._show_prop_(" + (rej & ~WITH_COMPARE) + ',' + next_obj_str + ")";
    return (typeof(el) == "object"  &&  el !== null
            ? "<a href=\""+ next_href +"\">"+el+"</a>" + (el.nodeName ? " <i>"+el.nodeName+"</i>" : "")
			: _hsc_(el))
}

var old_obj = {};

function sortProp(a, b)
{
	if ( a.fld < b.fld )
		return -1;
	if ( a.fld > b.fld )
		return 1;
	return 0;
}

function _show_prop_ ( rej, next_obj ) {
	if ( next_obj )
		__objs[++__obj_i] = next_obj;

    if ( __obj_i <= 0 ) { //>
        __back_button.blur();
        __back_button.disabled = true;
        __obj_i = 0;
    } else {
        __back_button.disabled = false;
    }
    __obj = __objs[__obj_i];


	var propList = "<table border=1><caption>" + __obj + "</caption>";
    var propListArr = new Array();
	var fld, el, next_obj_str;
    for ( var i in __obj ) {
        try {
			fld = i;
			el = __obj[i];
			next_obj_str = "opener.__obj['"+i+"']";
        } catch ( e ) {
			fld = "<span style='color: red'>" + i + "</span>";
			el = e.toString();
            next_obj_str = "";
        }
    	var value = _href_to_next_obj_(el, next_obj_str, rej);

        if ( rej & WITHOUT_CONST && fld == fld.toUpperCase() )
//!!! Не показывать константы
        	continue;
        if ( rej & WITHOUT_FUNCTION && typeof(el) == "function" )
//!!! Не показывать функции
        	continue;

    	if ( rej & WITH_COMPARE && old_obj[fld] && old_obj[fld] != value )
    	{
//!!! Если режим WITH_COMPARE и значения отличаются
    		value += "<font color=magenta>(" + old_obj[fld] + ")</font>";
    	}
   		old_obj[fld] = _href_to_next_obj_(el, next_obj_str, rej);
//		propList += "<tr><td>" + fld + "</td><td>" + value + "</td></tr>";
		propListArr[propListArr.length] = {'fld': fld, 'value': value};
    }
    if ( !(rej & WITH_NOSORT) )
    	propListArr.sort(sortProp);
	for ( var i in propListArr )
		propList += "<tr><td>" + propListArr[i].fld + "</td><td>" + propListArr[i].value + "</td></tr>";
	propList += "</table>";
	propListArr = new Array();

    if ( typeof(__obj) == "object"  &&  typeof(__obj.length) == "number"  &&  typeof(__obj.item) == "function" )
    {
		propList += "<table border=1><caption>Collection content</caption>";
        for ( var l=0; l<__obj.length; ++l ) {  //>
			fld = "item(" + l + ")";
            try {
				el = __obj.item(l);
				next_obj_str = "opener.__obj.item("+l+")";
            } catch ( e ) {
				fld = "<span style='color: red'>" + fld + "</span>";
				el = e.toString();
	            next_obj_str = "";
            }
//			propList += "<tr><td>" + fld + "</td><td>" + _href_to_next_obj_(el, next_obj_str, rej) + "</td></tr>";
			propListArr[propListArr.length] = {'fld': fld, 'value': _href_to_next_obj_(el, next_obj_str, rej)};
        }
        if ( __obj.length == 0 ) {
            propList += "<tr><td width='200' align='center'>Empty</td></tr>";
        }
	    if ( !(rej & WITH_NOSORT) )
    	    propListArr.sort(sortProp);
		for ( var i in propListArr )
			propList += "<tr><td>" + propListArr[i].fld + "</td><td>" + propListArr[i].value + "</td></tr>";
        propList += "</table>";
    }

    __prop_disp.innerHTML = propList;
}

var __obj;
var __objs  = Array();
var __obj_i = 0;
var __back_button;
var __prop_disp;

function inspectDOM ( obj, rej )
{
	var w = window.open("","DOMInspector");
	var d = w.document;
	d.open();
	d.write(
"<html>" +
"<head><title>DOM Inspector</title></head>" +
"<body>" +
"<button id='back' type='button' onclick='opener._back_obj_(" + (rej & ~WITH_COMPARE) + ")' disabled>Back</button>" +
"<div id='display'></div>" +
"</body>" +
"</html>"
		);
	d.close();
	w.focus();

    __back_button = d.getElementById("back");
    __prop_disp   = d.getElementById("display");

    __obj = obj;
    __objs[__obj_i] = __obj;
    _show_prop_(rej);
}

function _back_obj_ (rej) {
    --__obj_i;
    _show_prop_(rej);
}

objTrace = null;

//Функции трассировки в элемент EDIT
function initTraceLog(target)
{
//inspectDOM(top.frames[2].frameElement.contentDocument)
	var objTrace = CheckAndCreateIFR( 'traceText', true, 'textarea', target );

	objTrace.style.width = "100%";
	objTrace.style.height = 400;
	top.objTrace = objTrace;
	return objTrace;
}

function traceLog( text, nobr, init )
{
	var objTrace = top.objTrace;
	if ( !objTrace )
	{
if ( 0 && !top.ttt )
{
top.ttt = 1;
inspectDOM(top, WITHOUT_FUNC | WITHOUT_CONST);
}
		return;
	}
	if ( init )
		objTrace.value = '';
	if ( typeof(text) == "object" )
	{
		var obj = text;
		text = 'Tag=' + obj.tagName + ' Id=' + obj.id;
	}
	if ( nobr )
		objTrace.value += nobr;
	else
		objTrace.value += "\n";
	objTrace.value += text;
	if ( objTrace.value.length > 20000 )
	{
		objTrace.value += "\n************ Зацикливание *************"
		throw false;
	}
}

/* ф-ция debug() - пишет логи в JSConsole
 * преимущества перед alert() : 
 *  - не прерывает действие пользователя;
 *  - можно выводить (и копировать) длинные строки (например URL);
 *  - имеет защиту от зацикливания.
 * !!! не забывайте удалять вызовы ф-ции после завершения отладки !!!
 * автор: Толмачев А.А.
 */
var _debug_counter = 0;
function debug(aText)
{
	if ( ++_debug_counter > 30000 ) throw "It looks like script is got caught in an endless loop";

	try	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	} catch(e) {
		return;
	}
	var csClass = Components.classes['@mozilla.org/consoleservice;1'];
	var cs = csClass.getService(Components.interfaces.nsIConsoleService);
	cs.logStringMessage(aText);
}

//************************************************
//					watcher
//************************************************
if ( typeof ( top.paCreatedWatchers ) == 'undefined' )
	top.paCreatedWatchers = {};

var 
	
	$pnColorCo = 1,
	$pcStringBeforeCreate = '',					//Если ещё нет body, накапливаем в этой строке.
	$pbUseStringNumber = false,
	$paWatches = {};
	
if ( navigator.userAgent.indexOf( 'Firefox' ) != -1 )
	$pbUseStringNumber = true;
	
Watcher = function ( $cWindow ) {
	this._oTraceObj = '';
	this._cWindow = $cWindow;
		
	this.trace_showhide = function()
	{
		if ( this._oTraceObj )
		{
			this._oTraceObj.style.display = !this._oTraceObj.style.display ? 'none' : '';
			SetCookie( 'trace_showhide', this._oTraceObj.style.display, expiry );
		}
	}
	
	this.trace_clear = function()
	{
		if ( this._oTraceObj )
			this._oTraceObj.innerHTML = "";
	}
	
	this.ShowHideCaller =  function()
	{
		var $laCallers = document.getElementsByName( 'log_str' );
		for ( var $i = 0; $i < $laCallers.length; $i++ )
			$laCallers.item( $i ).style.display = $laCallers.item( $i ).style.display ? '' : 'none';
		SetCookie( 'trace_showhide_caller', $laCallers.item( 0 ).style.display, expiry );			
	}
	
	this.ViewObj = function( $oObj )
	{
		inspectDOM( $paWatches[$oObj.id] );
	}
	
	this.CheckExist = function()
	{
		if ( document && document.getElementById( 'trace_into_obj' ) )
			return true;
		else
			return false;
	}
	
	this.trace_value = function( $mixedValue )
	{
		{
			try {
				throw new Error();
			}
			catch(e) { 
				//$pcStrNum = e.stack.replace( '\n', '<BR>' );
				//$pcStrNum = e.stack.replace( '@:0', '' ).match( /@(.*):([0-9]*)\n$/ )[0].replace( '@', '' );
				//$pcStrNum = e.stack.replace( '@:0', '' ).match( /@(.*):([0-9]*)\n$/ );//[0].replace( '@', '' );
				$pcStrNum = e.stack.split( '\n' );//[0].replace( '@', '' );
				for ( var $i in $pcStrNum )
				{
					//if ( $pcStrNum[$i].indexOf( 'alertEx' ) == -1 && $pcStrNum[$i].substr( 0,7 ) == 'http://' )
					if ( $i == 3 )
						break;
					
				}
				//$pcStrNum = "<FONT color=blue>" + e.fileName + '[' + e.lineNumber + "]</FONT>: ";
				$pcStrNum = "<FONT name=log_str color=blue style='display:" + GetCookie( 'trace_showhide_caller' ) + ";'>" + $pcStrNum[$i] + ": </FONT>";
			//inspectDOM( $pcStrNum );
			}
		}

		if ( typeof( this._oTraceObj.str_count ) == 'undefined' )
			this._oTraceObj.str_count = 1;
		else
			this._oTraceObj.str_count++;
		if ( typeof( $mixedValue ) == 'object' )
		{
			var $lcObj = "<BUTTON class=CarlingButton id='w" + this._oTraceObj.str_count +"' onclick='this.parentNode.parentNode.watcher.ViewObj( this )'><IMG src='/resource/icons/preview.gif'></BUTTON>&nbsp;";
			$paWatches['w' + this._oTraceObj.str_count] = {};
			for ( var $i in $mixedValue )
				$paWatches['w' + this._oTraceObj.str_count][$i] = $mixedValue[$i];
			if ( typeof( $mixedValue.length ) == "number" && typeof( $mixedValue.item ) == "function" )
				for ( var $i = 0; $i < $mixedValue.length; $i++ )
					$paWatches['w' + this._oTraceObj.str_count].$i = $mixedValue.item( $i );
		}
		else
			$lcObj = "";
		var $lcOutput = "<NOBR id=str-" + this._oTraceObj.str_count + ">" + $lcObj + $pcStrNum + $mixedValue + "</NOBR><BR>";
		
		if ( this._oTraceObj )
			this._oTraceObj.innerHTML += $lcOutput;
		else
			$pcStringBeforeCreate += $lcOutput;
		return $lcOutput;
	}
	
	//Constructor
	if ( typeof( "GetCookie" ) != 'function' )
		require_once('/spo_vniins/lib/js/cookies.js' );		
	//$paCreated[$cWindow] = new Watcher( $cWindow );
	if ( this._cWindow && typeof( top[this._cWindow] ) == 'object' )
	{
		top.paCreatedWatchers[this._cWindow] = this;
		var $loDocument = top[this._cWindow].frameElement.contentDocument;
	}
	else
		$loDocument = document;
	$loDocument.bIsWatcher = true;
	$loControl = $loDocument.createElement( 'DIV' );
	$loControl.valign = 'top';
	//$loControl.style.position = 'absolute';
	//$loControl.style.border = '1px solid black';
	$loControl.innerHTML =	'<NOBR align=top>' + 
							'<BUTTON class=CarlingButton style="width: 30px; margin: 0px; padding: 0px;" onClick="this.parentNode.parentNode.watcher.trace_clear();" title="Очистка"><IMG border=0 valign=top  src="/resource/icons/new_lastik.gif"></BUTTON>' +
							'<BUTTON class=CarlingButton style="width: 30px; " onClick="this.parentNode.parentNode.watcher.ShowHideCaller();" title="скрыть/показать точки вызова"><IMG border=0 valign=top src="/resource/icons/numbered_list.gif"></BUTTON>' +
							'<BUTTON class=CarlingButton style="width: 95%;" onClick="this.parentNode.parentNode.watcher.trace_showhide();">Глядунчег</BUTTON></NOBR>';

	this._oTraceObj = $loDocument.createElement( 'DIV' );
	this._oTraceObj.id = 'trace_into_obj';

	this._oTraceObj.setAttribute( 'style', 'border: 1px solid lightgrey; width: 100%; height: 25%; overflow: auto; display: ' + GetCookie( 'trace_showhide' ) );

	$loControl.watcher = this;
	this._oTraceObj.watcher = this;
	
	if ( typeof( $loDocument.body ) != 'undefined' )
	{
		try {
		$loDocument.body.insertBefore( this._oTraceObj, $loDocument.body.firstChild );
		$loDocument.body.insertBefore( $loControl, $loDocument.body.firstChild );
		} catch(e) { return null; }
	}
	else
		return null;
	return this;	
}


function alertEx( $mixedValue, $cWindow ) {
	if ( typeof( $pbIsWatcher ) == 'undefined' ) return;
	var $loWatcher = Create( $cWindow );
	$loWatcher.trace_value( $mixedValue );
}

function Create( $cWindow )
{
	if ( typeof( $pbIsWatcher ) == 'undefined' ) return;
	if ( !$cWindow )
	{
		$lcWindow = "watcher";
		if ( typeof( top.paCreatedWatchers["watcher"] ) != 'object' || !top.paCreatedWatchers["watcher"].CheckExist() )
			top.paCreatedWatchers["watcher"] = new Watcher( "watcher" );
	}
	else
	{
		var $lcWindow = ParseWatcherWindow( $cWindow );
		if ( typeof( top.paCreatedWatchers[$lcWindow] ) == 'undefined' || typeof( top[$lcWindow]  ) == 'undefined' || typeof( top[$lcWindow].frameElement.contentDocument.bIsWatcher ) == 'undefined' )
		top.paCreatedWatchers[$lcWindow] = new Watcher( $lcWindow );
	}
	return top.paCreatedWatchers[$lcWindow];
}

function ParseWatcherWindow( $cWindow )
{
	if ( typeof( $pbIsWatcher ) == 'undefined' ) return;
	if ( typeof( $cWindow ) == 'undefined' )
		$cWindow = self.name;
	return $cWindow;
}
