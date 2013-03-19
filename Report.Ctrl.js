var HRC = {version: '1.0'};

//<div class="x-form-field-wrap">
//<input type="text" value="..." class="x-form-text x-form-field x-form-empty-field" autocomplete="off"/>
//<img class="x-form-trigger x-form-arrow-trigger" src="../ext-2.0/resources/images/default/s.gif" /></div>

//alert дл€ большого текста в отдельном окне
function alertWin_(width, height)
{
	if ( !width )
		width = 300;
	if ( !height )
		height = 400;
	newwin = window.open('','win_alertText','top=0,width=' + width + ',height=' + height + ',scrolling=1,location=0,tulbar=0,resizable=1');
	return newwin;
}

function alertText(text, width, height)
{
	newwin = alertWin_(width, height)
	newwin.document.write(text);
	newwin.document.close();
	newwin.focus();
	return newwin;
}

function getAbsolutePos(el, parent)
{
	var x = 0, y = 0;
	for(; el && el!=parent; el=el.offsetParent)
	{
		x += el.offsetLeft;
		y += el.offsetTop;
	}
	return {x: x, y: y};
}

function count(arr)
{
	if ( arr == undefined )
		return 0;
	return arr.length;
}

function strlen(s)
{
	return (s+"").length;
}

function number_format(num_to_print, dec, point_char, digit_grp_char)
{
	var s = Number(num_to_print).toFixed(dec).split(".");
	if ( dec )
		s[1] = point_char + s[1];
	if ( digit_grp_char )
	{
		var cel = s[0], celF = [];
		var n = 0, k = cel.length % 3;
		if ( k == 1 && cel.charAt(0) == "-" )
			k = 4;
		while(n < cel.length )
		{
			celF.push(cel.substr(n, k));
			n += k;
			k = 3;
		}
		s[0] = celF.join(digit_grp_char);
	}
	return s[0] + s[1];
}

var preventDefaultEvent = function(event)
{
	event.preventDefault();
	event.stopPropagation();
	return false;
}


HRC.Manager = function()
{
	var oTab, oTR, oTD;
	var macReg = new RegExp("%%([^%]+)%%", "g");
	var ua = navigator.userAgent.toLowerCase();
	var isTabDom = false;
//	var isStrict=document.compatMode=="CSS1Compat",isOpera=ua.indexOf("opera")>-1
	var styleDispRow = "", classCheckVisib = "visibExp", ALLOW_LEVEL = 999;
	var arMark = {}, ar_Data = {
		RP_PARAMS: {COUNT: {}, SETTINGS: {IS_TABBED: 0}, keySET: {HEAD: "", GROUP: "TREE"}, nomSET:{}, nameLEVELS: {}, 
			ctrlLEVEL: 0, maxLEVEL: 0, hiddLEVEL: 1, stopLEVEL: ALLOW_LEVEL}}, ar_Turn = {}, hiddenColumns = [];
	var ar_Sect = {HEAD:{tSect:[""], nRows:0, cRow:0}, DUMMY:{tSect:[""], nRows:0, cRow:0}};
	var pcDecimal = 2, pcPoint = ".", pcSeparator = "\xA0";
	var nameData, rs_nom = 0, rs_key = "", sepKey = "*", sepID = "/";

	var $oReport;

	if ( window.ActiveXObject )
		isTabDom = true;
	else
	{
		var i;
		if ( (i=ua.indexOf("opera")) >=0 )
		{
			var vers = parseFloat(ua.substr(i+6));
			if ( vers < 9.26 )
				isTabDom = true;
//alert(vers + '|' + isTabDom + '|' + ua + '|' + ua.substr(i+6))
		}
	}

	var $row, iData, iMain;
	var substrStyle = function(sReg, sInBrac, pos, str)
	{
		var camelRe = /(-[a-z])/gi;
		var camelFn = function(m, a){ return a.charAt(1).toUpperCase(); };
		var sStyle = sInBrac.replace(camelRe, camelFn);
		oTD.style[sStyle] = pos.replace(macReg, substrText);
		return "";
	}
	
	var parseNumber = function(str)
	{
		if ( !str )
			return 0.0;
		str = str.toString();
		return parseFloat( str.replace( / |&nbsp;/g, "" ) );
	}

	var getTargetTag = function(event, tag)
	{
		var obj = event.target;
		if ( tag )
			while(obj && obj.tagName != tag)
				obj = obj.parentNode;
		return obj;
	}

	var getItem = function()
	{
	}

/*
	var substrText = function(sReg, sInBrac, pos, str)
	{
		switch( sInBrac )
		{
			case "PAD_LEVEL":
				evl = $row.padLevel;
				break;
			default:
				evl = number_format(parseNumber(evl), pcDecimal, pcPoint, pcSeparator);
				break;
		}
		return evl;
	}
	
	var writeTAB_ = function(ar_Data)
	{
		styleDispRow = "";
		classCheckVisib = "visibExp";
				aRows.push(ar_Sect[nBand].tSect[ar_Sect[nBand].cRow].replace(macReg, substrText));
	}
*/
	var writeTAB = function(name, aGroup, iBegin)
	{
		var aRows = new Array(), LevelFrom = -1;
		styleDispRow = "";
		classCheckVisib = "visibExp";
		if ( !iBegin )
			iBegin = 0;
		else
			LevelFrom = aGroup[iBegin-1].LevelTotal;
		for(; iBegin<aGroup.length; iBegin++)
		{
			var $row = aGroup[iBegin];
			if ( $row.LevelTotal <= LevelFrom )
				break;
			if ( $row.LevelTotal > $oReport.ar_Data['RP_PARAMS'].stopLEVEL )
				continue;
			else
				$oReport.ar_Data['RP_PARAMS'].stopLEVEL = ALLOW_LEVEL;
			var nBand = $row.band;
			if ( name == "GROUP" )
				nBand = $row.group;
			else if ( nBand == undefined )
				nBand = name;
			$oReport.ar_Data.RP_PARAMS.nomSET[name] = iBegin;
//			$row.rIndex = rIndex;

			if ( isTabDom )
			{
				makeBAND(nBand);
			}
			else
			{
				if ( arMark[nBand] )
					aRows.push(arMark[nBand](nBand, iBegin+1, $row, ''));
			}
		}
		return aRows.join("");
	}
	
	var aMaskRow = [];

	var getSwitchImg = function(pCollapse)
	{
		if ( pCollapse )
			return "visibClp";
		else
			return "visibExp";
	};
	
	var isClass = function(obj, clsTest)
	{
		return obj.className.indexOf(clsTest) >= 0;
	};
	
	var changeLastClass = function(obj, clsRem, clsAdd)
	{
		if ( !obj )
			return;
		var cls = obj.className;
		if ( clsRem )
		{
			var i = cls.indexOf(clsRem);
			if ( i >= 0 )
			{
				while(i >= 0 && cls.charAt(i-1) == " ")
					i--;
				cls = cls.substring(0, i);
				if ( clsAdd == clsRem )
					clsAdd = "";
			}
		}
		else if ( cls.indexOf(clsAdd) >= 0 )
			return;
		obj.className = cls + " " + clsAdd;
	};
	
	var setSwitchImg = function(oTD, pCollapse)
	{
		if ( pCollapse )
			changeLastClass(oTD, "visib", "visibClp");
		else
			changeLastClass(oTD, "visib", "visibExp");
	};
	
	var doMakeTAB = function(oTab, header)
	{
//		if ( !oTab )
//			oTab = document.getElementById("MainTable");
		$oReport.selectedRow = null;
		$oReport.selectedCell = null;
		$oReport.rowValue = null;
		var aAtr = ["rowspan","colspan","style","class"];
	
		if ( isTabDom )
		{
		}
		else
		{
			var sInn = "";
//			var sInn = writeTAB("MAIN", ar_Data.MAIN.DUMMY).replace(/##/g, '"');
			if ( header )
				sInn += writeTAB("HEAD", [0]).replace(/##/g, '"');
			sInn += writeTAB("GROUP", ar_Data.GROUP.TREE).replace(/##/g, '"');
			if ( ar_Data.RP_PARAMS.SETTINGS.IS_TABBED )
				sInn = '<tbody id="ctrlTAB/MAIN">' + sInn + "</tbody>";
			oTab.innerHTML = sInn;
		}
	};

	evalTry = function(text)
	{
		try
		{
			eval(text);
		}
		catch(e)
		{
			alertText(e + ' в строке:' + e.lineNumber + "<textarea style='width:100%;height:98%;'>\n\n" + 
//			text.unescapeHTML() +
//			text.replace(/<(\/?[^>]+)>/gi, '&lt;$&gt;') + 
			text + 
//			alert(e + ' в строке:' + e.lineNumber + text + 
				"\n================ STACK =================\n" + e.stack.replace('\n', "\n"));
			return false;
		}
		return true;
	};

	return {
		arMark: arMark,
		ar_Data: ar_Data,
		ALLOW_LEVEL: ALLOW_LEVEL,
		getSwitchImg: getSwitchImg,
		selectedRow: null,
		selectedCell: null,
		rowValue: null,
		field: null,
		value: null,
		edDiv: null,
		edElems: {curEdEl: null, input: null},

		isColVisib: function(nCell, colspan)
		{
			for(var iC=0; iC<colspan; iC++, nCell++)
				if ( hiddenColumns[nCell] )
					return false;
			return true;
		},
		
		switchCol: function(event, nCell, mask, colspan)
		{
			preventDefaultEvent(event);
			this.killSelection();
			var vMas = this.isColVisib(nCell, colspan) ? 1 : 0;
			for(var iC=0; iC<colspan; iC++, nCell++)
				if ( iC >= mask.length || mask.charAt(iC) == "1" )
					hiddenColumns[nCell] = vMas;
//this._aMaskCol = aMaskCol;
			doMakeTAB(oTab, arMark.HEAD);
		},
		
		getDataTR: function(oTR)
		{
			var aID = oTR.id.split(sepID);
			return this.getDataRow(aID[0], aID[1], aID[2]);
		},

		getDataRow: function(nameData, rs_key, rs_nom)
		{
			var row = this.ar_Data[nameData];
			if ( rs_key != "" )
				row = row[rs_key];
			return row[rs_nom];
		},

		getDataValue: function($row, field)
		{
			if ( !field )
				return '';
			var aFld = field.split(sepID);
			if ( aFld.length == 2 )
				return $row[aFld[0]][aFld[1]];
			else
				return $row[aFld[0]];
		},

		setDataValue: function($row, field, value)
		{
			if ( !field )
				return;
			var aFld = field.split(sepID);
			if ( aFld.length == 2 )
				$row[aFld[0]][aFld[1]] = value;
			else
				$row[aFld[0]] = value;
		},

		calcParent: function(oTR, $row, field, delta)
		{
			if ( !delta )
				return;
			var aFld = field.split(sepID);
			var name = "GROUP", key = "TREE", nom = "", id;
			var aFldS = {}, cFldS = 1;
			if ( aFld.length == 2 )
			{
				field = aFld[1];
				var aTurns = aFld[0].split(sepKey);
				var k = "", sep = "";
				for(var i=0; i<aTurns.length; i++)
				{
					k += sep + aTurns[i];
					sep = sepKey;
					aFldS[k + sepID + field] = k;
					cFldS++;
				}
			}
			aFldS[field] = ""; 
			
			while(true)
			{
				var value;
				for(var i=0, iFldS=0; i<oTR.cells.length && iFldS<cFldS; i++)
				{
					var atrField = oTR.cells[i].getAttribute("field");
					switch(aFldS[atrField])
					{
						case undefined:
							continue;
						case "":
							value = ($row[field] += delta);
							break;
						default:
							value = ($row[aFldS[atrField]][field] += delta);
							break;
					}
					oTR.cells[i].innerHTML = number_format(value, 2, ".", "\xA0");
					iFldS++;
				}

				var RS_CALL;
				if ( (RS_CALL=$row.nomUpGroup) )
				{
					nom = RS_CALL;
				}
				else if ( (RS_CALL=$row.RS_CALL) )
				{
					name = RS_CALL.name;
					key = RS_CALL.key;
					nom = RS_CALL.nom;
//					if ( typeof($row) == "object" )
				}
				else
					break;
				$row = ar_Data[name][key][nom];
				id = name + sepID + key + sepID + nom;
				oTR = document.getElementById(id);
			}
		},

		insertTR: function(sInn, oTR)
		{
			var tabTemp = document.getElementById("tabTmpCreate");
			tabTemp.innerHTML = sInn;
			var oPar = oTR.parentNode;
			oTR = oTR.nextSibling;
			for(var i=0, len=tabTemp.rows.length; i<len; i++)
			{
				oPar.insertBefore(tabTemp.rows[0], oTR);
			}
		},

		switchRow: function(event)
		{
			preventDefaultEvent(event);
			this.killSelection();
			var oTR = getTargetTag(event, "TR");
			var $row = this.getDataTR(oTR);
//			var count = $row.pCount + $row.pCountSet;
			var disp = !isClass(oTR.cells[0], "visibExp");
			var clsFrom = "HIDDEN", clsTo = "";
			if ( !disp )
			{
				clsTo = clsFrom;
				clsFrom = "";
				$row.isCollapsed = 1;
			}
			else
				$row.isCollapsed = 0;
			setSwitchImg(oTR.cells[0], !disp);
			ar_Data.RP_PARAMS.stopLEVEL = ALLOW_LEVEL;
			if ( $row.pCountSet == 0  && $row.RS_HIDDEN )
			{
				ar_Data.RP_PARAMS.hiddLEVEL = this.getHiddLevel(nameData, $row) + 1;
				var sInn = "";
				sInn = this.printSetBand(nameData, $row.RS_HIDDEN.band, $row.RS_HIDDEN.name, $row.RS_HIDDEN.key, $row, "");	//$row.rub);
				this.insertTR(sInn, oTR);
			}
			if ( nameData == "GROUP" && $row.pCount == 1 )
			{
				ar_Data.RP_PARAMS.hiddLEVEL = this.getHiddLevel(nameData, $row) + 1;
				var sInn = "";
				sInn = writeTAB(nameData, ar_Data.GROUP.TREE, rs_nom+1);
				this.insertTR(sInn, oTR);
			}
			else
			{
				var ctrlLevel = parseInt(oTR.getAttribute("hidd_level"));
				var level;
				for(;;)
				{
					oTR = oTR.nextSibling;
					if ( !oTR || ( (level=oTR.getAttribute("hidd_level")) && level <= ctrlLevel ) )
						break;
					changeLastClass(oTR, clsFrom, clsTo);
					if ( disp && level && !isClass(oTR.cells[0], "visibExp") )
					{
//==== ќткрываем, а внутри закрыто
						var $rowChild = this.getDataTR(oTR);
						oTR = oTab.rows[oTR.rowIndex + $rowChild.pCount + $rowChild.pCountSet - 1];
					}
				}
			}
//alert($row.pCount);			
		},

		getHiddLevel: function(name, $row)
		{
			if ( $row.LevelTotal == undefined ) 
				$row.LevelTotal = 1;
			name += "-" + $row.LevelTotal;
			var RP_PARAMS = this.ar_Data.RP_PARAMS;
			if ( !RP_PARAMS.nameLEVELS[name] )
			{
				RP_PARAMS.nameLEVELS[name] = ++RP_PARAMS.ctrlLEVEL;
				RP_PARAMS.maxLEVEL = Math.max(RP_PARAMS.maxLEVEL, RP_PARAMS.ctrlLEVEL);
			}
			else
				RP_PARAMS.ctrlLEVEL = RP_PARAMS.nameLEVELS[name];
			return RP_PARAMS.ctrlLEVEL;
		},

		setData: function(name, val)
		{
			switch(name)
			{
				case "hiddenColumns":
					hiddenColumns = val;
					break;
				default:
					ar_Data[name] = val;
			}
		},

		setFunc: function(name, val)
		{
//			evalTry("arMark." + name + " = function($name, $nomRow, $row, $rowMAIN, $rubr_group) {\n" + val.replace(/;/g, ";\n") + "}");
			arMark[name] = val;
		},

		countPrintChild: function($row, $data, $field)
		{
			if ( !$field )
				$field = "pCount";
			for(var i=$row.nomUpGroup; i!=undefined; i=$data[i].nomUpGroup)
				$data[i][$field] += $row[$field];
		},
	
		printSetBand: function($callName, $band, $name, $key, $rowCall, $rub)
		{
			var $text = "";
			var $arData;
			if ( $key )
				$arData = this.ar_Data[$name][$key];
			else
				$arData = this.ar_Data[$name];
			if ( $arData == undefined )
				return '';
			this.ar_Data.RP_PARAMS.COUNT[$name] = $arData.length;
			this.ar_Data.RP_PARAMS.keySET[$name] = $key;
			if ( $arData )
			{
				if ( $rowCall.LevelTotal >= $oReport.ar_Data.RP_PARAMS.stopLEVEL )
					$rowCall.RS_HIDDEN = {name: $name, key: $key, band: $band};
				else
				{
					var pCountSet = 0, nPrev;
					var keyCall = this.ar_Data.RP_PARAMS.keySET[$callName];
					var nomCall = this.ar_Data.RP_PARAMS.nomSET[$callName];
					var aRows = [];
					for(var $nom in $arData)
					{
						if ( typeof($arData[$nom]) == "function" )
							continue;
						this.ar_Data.RP_PARAMS.nomSET[$name] = $nom;
						var $row = $arData[$nom];
						$row.RS_CALL = {name: $callName, key: keyCall, nom: nomCall};
						aRows.push(this.arMark[$band]($name, ++$nom, $row, $rub));	// . sprintf('%03d', $nom));
						pCountSet += $row.pCount + $row.pCountSet;
						if ( $row.nomUpGroup != undefined )
						{
							this.countPrintChild($row, $arData);
						}
					}
					$text = aRows.join("");
					$rowCall.pCountSet = pCountSet;
					if ( $callName == "GROUP" )
						this.countPrintChild($rowCall, ar_Data[$callName][keyCall], "pCountSet");
					else if ( $rowCall.nomUpGroup != undefined )
					{
						this.countPrintChild($rowCall, ar_Data[$callName][keyCall]);
					}
				}
			}
			return $text;
		},

		setHiddLevel: function(lvl)
		{
			ar_Data.RP_PARAMS.hiddLEVEL = lvl; 
			ar_Data.RP_PARAMS.maxLEVEL = 0;
			ar_Data.RP_PARAMS.ctrlLEVEL = 0;
		},

		alert: function(s)
		{
			alert(s);
		},

		makeFRM: function()
		{
			this.arMark = arMark;
//			this.ar_Data = ar_Data;
			ar_Turn = ar_Data.ar_Turn;
			this.ar_Turn = ar_Turn;
			this.hiddenColumns = hiddenColumns;
			this.sepID = sepID;
			this.edtElements = {};
			this.countClick = 0;

			$oReport = this;
			arMark.$oReport = $oReport;
			this.edDiv = null;
			this.edDivTYPE = "";

			this.number_format = number_format;
			if ( !oTab )
				oTab = document.getElementById("MainTable");
			doMakeTAB(oTab, arMark.HEAD);
		},
		
		killSelection: function(event)
		{
			changeLastClass(this.selectedCell, "SEL_TD", "");
			changeLastClass(this.selectedRow, "SEL_TR", "");
//			this.edDiv.style.display = "none";
			this.rowValue = null;
		},
		
		makeWidget: function(field, type)
		{
			var tplWidget = {
				ALL: {
					getCtrlElement: function() { return this.firstChild; },
					setValue: function(value, text) { this.getCtrlElement().value = value; },
					getValue: function() { return this.getCtrlElement().value; }
					},
				INPUT: {
					text: '<input type="text" class="x-form-text x-form-field x-form-empty-field">'
					},
				COMBO: {
					text: '<div id="CMB_WRAP" class="x-form-field-wrap">' +
						'<input type="text" value="..." autocomplete="off" class="x-form-text x-form-field x-form-empty-field"/>' +
						'<img class="x-form-trigger x-form-arrow-trigger" src="../ext-2.0/resources/images/default/s.gif" /></div>',
					makeWidget: function(field) {
						if ( !ar_Data.CMB_LIST[field].combo )
						{
							var fName = ar_Data.CMB_LIST[field].fName;
							var fKey = ar_Data.CMB_LIST[field].fKey;
							var url = 'getComboData.php?req_combo[]=' + field;
							var fields = [fKey, fName];
							ar_Data.CMB_LIST[field].combo = new Ext.form.ComboTree({
								tpl: '<tpl for="."><div ext:qtip="{' + fName + '}" class="x-combo-list-item">{' + fName + '}</div></tpl>',
/*
								mode: 'local',
								store: new Ext.data.SimpleStore({
									fields: [field, fName],
									data : ar_Data[ar_Data.CMB_LIST[field].data],
									id: 0
								}),
*/
								mode: 'remove',
								fields: fields,
								url: url,
								store: new Ext.data.JsonStore({
									url : url,
									fields: fields
								}),
								valueField: fKey,
								displayField: fName,
								typeAhead: true,
								triggerAction: 'all',
								emptyText: '...',
								selectOnFocus: true,
								autocomplete: "on"
							});
						}
						var combo = ar_Data.CMB_LIST[field].combo;
						var elInput = Ext.get("CMB_WRAP").dom.firstChild;
						combo.rendered = false;
						combo.applyToMarkup(elInput);
						combo.wrap._domCtrl = elInput;
						var flEl = combo.el;
//						flEl.on('focus', combo.onFocus, elInput.parentNode);
//						Ext.get(elInput.nextSibling).on('click', combo.onTriggerClick, combo);
/*
						if ( e.type == 'click' )
						   combo.onTriggerClick(e, combo.el.dom);
						else
						   combo.onFocus(e, combo.el.dom);
*/
					},
					getCtrlElement: function() { return this.firstChild.firstChild; },
					setValue: function(value, text) { this.getCtrlElement().value = text; },
					getValue: function() { return this.getCtrlElement().value; }
					}
			};
			var widget;
			var oRegTABS = document.getElementById("regTABS");
			var typeW = type;
			if ( !type )
			{
				if ( ar_Data.CMB_LIST[field] )
				{
					typeW = field;
					type = 'COMBO';
				}
				else
					typeCust = type = 'INPUT';
			}
			if ( this.edDiv && this.edDivTYPE != typeW )
			{
//				while(oRegTABS.hasChildNodes())
				oRegTABS.removeChild(this.edDiv);
				this.edDiv = null;
			}
			if ( !this.edtElements[typeW] )
			{
				widget = document.createElement("DIV");
				widget.className = "ED_DIV";
				widget.style.display = "none";
				if ( tplWidget[type].text )
					widget.innerHTML = tplWidget[type].text;
				widget.getCtrlElement = ( tplWidget[type].getCtrlElement || tplWidget["ALL"].getCtrlElement );
				widget.setValue = ( tplWidget[type].setValue || tplWidget["ALL"].setValue );
				widget.getValue = ( tplWidget[type].getValue || tplWidget["ALL"].getValue );
				this.edtElements[typeW] = widget;
			}
			else
				widget = this.edtElements[typeW];
			if ( !this.edDiv )
			{
				this.edDivTYPE = typeW;
				this.edDiv = widget;
				oRegTABS.appendChild(widget);
				if ( tplWidget[type].makeWidget )
					tplWidget[type].makeWidget(field);
			}
			return widget;
		},
		
		isEditind: function()
		{
			return ( this.edDiv && this.edDiv.style.display == "" );
		},
		
		selectCell: function(event)
		{
			preventDefaultEvent(event);
			var pos = 0;
			var oTD = getTargetTag(event, "TD");
			var oTR = oTD.parentNode;
			if ( this.selectedCell != oTD )
			{
				this.countClick = 0;
//==== «акончить с редактированием
//				if ( this.rowValue )
				if ( this.isEditind() )
				{
//					var value = this.edElems.input.value;
					var value = this.edDiv.getValue();
					if ( typeof(this.value) == "number" )
					{
						if ( (pos=value.indexOf(",")) >= 0 )
							value = value.substr(0, pos) + "." + value.substr(pos+1);
						if ( (pos=value.indexOf(".")) >= 0 )
							value = parseFloat(value);
						else
							value = parseInt(value);
//						this.setValue(this.rowValue, this.field, value);
						var delta = value - this.value;
						this.calcParent(this.selectedRow, this.rowValue, this.field, delta);
//						value = number_format(value, 2, ".", "\xA0");		//????????????????????????????
					}
					else
					{
						this.setDataValue(this.rowValue, this.field, value);
						this.selectedCell.innerHTML = value;
					}
					this.edDiv.style.display = "none";
					this.rowValue = null;
				}
				changeLastClass(this.selectedCell, "SEL_TD", "");
				changeLastClass(oTD, "", "SEL_TD");
				this.selectedCell = oTD;
				this.field = oTD.getAttribute('field');
				if ( this.selectedRow != oTR )
				{
					changeLastClass(this.selectedRow, "SEL_TR", "");
					changeLastClass(oTR, "", "SEL_TR");
					this.selectedRow = oTR;
				}
			}
//			if ( this.field && ( 1 || this.selectedCell == oTD ) )
			if ( this.field && ++this.countClick >= 1 )
			{
//				if ( !this.rowValue )
				if ( !this.isEditind() )
				{
					this.rowValue = this.getDataTR(oTR);
					this.value = this.getDataValue(this.rowValue, this.field);
//					oTD.innerHTML = '<input type="text" size="11" value="' + this.value + '"/>';
					var widget = this.makeWidget(this.field);
					var aPos = getAbsolutePos(oTD, this.edDiv.parentNode);
					this.edDiv.style.left = aPos.x + "px"; 
					this.edDiv.style.top = aPos.y + "px";
					this.edDiv.style.width = oTD.offsetWidth - 2 + "px";
					this.edDiv.style.height = oTD.offsetHeight - 2 + "px";
////					this.edElems.input.style.marginTop = (oTD.offsetHeight - 2 - this.edElems.input.offsetHeight)/2 + "px";
					this.edDiv.setValue(this.value, oTD.innerHTML);
					var rangeOffset = event.rangeOffset;
					if ( typeof(this.value) == "number" )
					{
						var str = oTD.innerHTML.replace(/&nbsp;/g, " ");
//						while( (pos=oTD.innerHTML.indexOf("\xA0", pos+1)) >= 0 )
						while( (pos=str.indexOf(" ", pos+1)) >= 0 && pos <= rangeOffset )
							rangeOffset--;
					}
					var ctrl = this.edDiv.getCtrlElement();
//					ctrl.style.marginTop = (oTD.offsetHeight - 22)/2 + "px";
					this.edDiv.style.display = "";
					ctrl.selectionStart = rangeOffset;
					ctrl.selectionEnd = rangeOffset;
					ctrl.focus();
				}
			}
		}
	};	

}();

