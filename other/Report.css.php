<?header('content-type: text/css'); ?>
@import "Styles.css.php";
/************ Основные стили ************/
.MainTable {
	border-spacing:  0px;
	border-collapse: separate;
	border-style:  none;
   border-bottom: #663300 1px solid;
	empty-cells:   show;
}

.padZero {
	padding: 0px;
}

#headerFix td, #mainFix td { padding: 0px; height: 0px }
#mainFix { height: 0px }

.BaseFont {
	font-size: 13px;
}


.ColCaption {
	font-family: Verdana, Tahoma, Arial, Helvetica;
	font-size:   13px;
	font-weight: normal;

	BORDER-BOTTOM: #B8B8B8 1px solid;
	BORDER-TOP: #f9f4ec 1px solid;
	BORDER-RIGHT: #B8B8B8 1px solid;
	BORDER-LEFT: #f9f4ec 1px solid;
	
	padding: 2px 2px 3px;
	text-align: center;
	background-color: /*#EAE7D5*/#EFEDE0;
}


.ColCaptionHLine {
	height: 1px;
   BORDER: 0px;

	width: 100%;
   background-color: black;

}


.DataCellgvs {
    font-family: Verdana, Tahoma, Arial, Helvetica;
	 font-size:   13px;
	 font-weight: inherit;
    border-top:    white 2px solid;
    border-right:  gray  1px solid;
    border-bottom: gray  1px solid;
    border-left:   white 2px solid;
    padding: 2px 5px 3px;
    background-color: #FFFFCC;
}

.DataCell, .DataEdit, .DataReadOnly, .aaa {
    font-family: Verdana, Tahoma, Arial, Helvetica;
	 font-size:   13px;
	 font-weight: inherit;
    border-top:    white 2px solid;
    border-right:  gray  1px solid;
    border-bottom: gray  1px solid;
    border-left:   white 2px solid;
    padding: 2px 5px 3px;
    background-color: white;
}

tr.SelectTR td:first-child {
	border-left: 1px solid black;
}

tr.SelectTR td:last-child {
	border-right: 1px solid black;
}

tr.SelectTR td{
	/*background-color: #99ddff;*/
	border-top: 1px solid black;
	border-bottom: 1px solid black;
	
}

.even
{
	BACKGROUND-COLOR: #F2F7FF;
}

/************ Группировка строк таблицы ************/

.RowGrpHead_1_1,
.RowGrpHead_1_2,
.RowGrpHead_1_3,
.RowGrpHead_1_4,
.RowGrpHead_1_5,
.RowGrpHead_1_6,
.RowGrpHead_1_7,

.RowGrpHead_2_1,
.RowGrpHead_2_2,
.RowGrpHead_2_3,
.RowGrpHead_2_4,
.RowGrpHead_2_5,
.RowGrpHead_2_6,
.RowGrpHead_2_7,

.RowGrpHead_3_1,
.RowGrpHead_3_2,
.RowGrpHead_3_3,
.RowGrpHead_3_4,
.RowGrpHead_3_5,
.RowGrpHead_3_6,
.RowGrpHead_3_7
{
    font-family: Verdana, Tahoma, Arial;
    font-size: 13px;

	 font-weight: bold;
	 
	 	BORDER-BOTTOM: #B8B8B8 1px solid;
	BORDER-TOP: #f9f4ec 1px solid;
	BORDER-RIGHT: #B8B8B8 1px solid;
	BORDER-LEFT: #f9f4ec 1px solid;
/*
    border-top:   silver 1px solid;
    border-right:  0px;
    border-bottom: 0px;
    border-left:   silver 1px solid;
*/
    padding: 5px;

    color: Black;
	 background-color: /*#93BED8*/ /*#EAE7D5*/#EFEDE0;
}

.RowGrpHead_2_1,
.RowGrpHead_2_2,
.RowGrpHead_2_3,
.RowGrpHead_2_4,
.RowGrpHead_2_5,
.RowGrpHead_2_6,
.RowGrpHead_2_7
{
	font-weight: bold;
    background-color: #CCD7FF;
}

.RowGrpHead_3_1,
.RowGrpHead_3_2,
.RowGrpHead_3_3,
.RowGrpHead_3_4,
.RowGrpHead_3_5,
.RowGrpHead_3_6,
.RowGrpHead_3_7
{
	font-weight: bold;
   background-color: #CCD7FF;
}

.RowGrpHead_1_2, 
.RowGrpHead_2_2,
.RowGrpHead_3_2
	{ padding-left: 25px; }
.RowGrpHead_1_3,
.RowGrpHead_2_3,
.RowGrpHead_3_3
	{ padding-left: 45px; }
.RowGrpHead_1_4,
.RowGrpHead_2_4,
.RowGrpHead_3_4
	{ padding-left: 65px; }
.RowGrpHead_1_5,
.RowGrpHead_2_5,
.RowGrpHead_3_5
	{ padding-left: 85px; }
.RowGrpHead_1_6,
.RowGrpHead_2_6,
.RowGrpHead_3_6
	{ padding-left: 105px; }
.RowGrpHead_1_7,
.RowGrpHead_2_7,
.RowGrpHead_3_7
	{ padding-left: 125px; }

.RowGrpFoot_1_0,
.RowGrpFoot_1_1,
.RowGrpFoot_1_2,
.RowGrpFoot_1_3,
.RowGrpFoot_1_4,
.RowGrpFoot_1_5,
.RowGrpFoot_1_6,
.RowGrpFoot_1_7
{
    font-family: Verdana, Tahoma, Arial, Helvetica;
	font-size: 13px;
	padding: 1px;
	background-color: /*#D2CDED*/white;
}

.FootSpace td {
	height: 20px;
	background-color: #ede2cd;
	padding: 0px;
}

.DataEdit {
    background-color: #FFFFDD;
}

.DataReadOnly {
   /* background-color: green; /*#EFEDE0;	*/
}

.DataEditSelected {
	background-color: #CCD7FF !important;
}

.DataChecked {
	font-size: 10; font-weight: bold; vertical-align: middle;
}

.DataUnChecked {
	font-size: 7; font-weight: normal; vertical-align: middle;
}

/************ Заголовки для строк ************/
.RowCaption_1,
.RowCaption_2,
.RowCaption_3,
.RowCaption_4,
.RowCaption_5,
.RowCaption_6,
.RowCaption_7,
.RowCaption_8,
.RowCaption_9
{
    font-family: Verdana, Tahoma, Arial, Helvetica;
	font-size:   15px;
	font-weight: normal;

    border-top:    #f9f4ec 2px solid;
    border-right:  #663300 1px solid;
    border-bottom: #663300 1px solid;
    border-left:   #f9f4ec 2px solid;

	padding: 0px 2px;

	text-align: left;
	color: navy;
	background-color: #e8e8e8;
}
.RowCaption_2 { padding-left:  9px; }
.RowCaption_3 { padding-left: 16px; }
.RowCaption_4 { padding-left: 23px; }
.RowCaption_5 { padding-left: 30px; }
.RowCaption_6 { padding-left: 37px; }
.RowCaption_7 { padding-left: 44px; }
.RowCaption_8 { padding-left: 51px; }
.RowCaption_9 { padding-left: 58px; }

/************ Ссылки, Кнопки ************/


.CarlingDataLink
{
	font-size: 13px;
	text-decoration:none;
	vertical-align: middle;
}

.CarlingDataLink:visited, .CarlingDataLink:link
{
    COLOR: #1C3664;
}

.CarlingDataLink:hover, .CarlingDataLink:active
{
    COLOR: #1C3664;
    TEXT-DECORATION: underline
}


/*
.CarlingButton {
	font-family: Arial, Verdana, Tahoma, Helvetica;
	font-weight: normal;
	font-size:   13px;

	border-top:    #f9f4ec 2px solid;
	border-right:  #663300 1px solid;
	border-left:   #f9f4ec 2px solid;
	border-bottom: #663300 1px solid;

	padding: 1px 3px 3px 2px;

	width:  80px;
	height: 24px;

	background-color: lightgrey;
}
.CarlingButton:hover:active {
	border: lightgrey 2px groove;

	padding: 2px 1px 1px 3px;
}

.CarlingButton
{
	padding: 0px;
	font-size: 11px;
	white-space: nowrap;
	BORDER-BOTTOM: #666699 1px solid;
	BORDER-TOP: #f9f4ec 1px solid;
	BORDER-RIGHT: #666699 1px solid;
	BORDER-LEFT: #f9f4ec 1px solid;
	HEIGHT: 26px;
	FONT-WEIGHT: normal;
	BACKGROUND-COLOR: #E0E0E0;
	vertical-align: middle;
}

.CarlingButton:hover 
{
	BACKGROUND-COLOR: #CCD7FF;
}

.CarlingButton:active
{
    BACKGROUND-COLOR: #CCD7FF;	
}

*/

/******* Стили для шапки и подвала документа *******/

h2 {
    font-family: Verdana, Tahoma, Arial, Helvetica;
	font-size:   17px;
	font-weight: bold;
	text-align:  center;

	margin-top:    4px;
	margin-bottom: 4px;
}

.MainInfo, .LongTitle {
    font-family: Verdana, Tahoma, Arial, Helvetica;
    font-size:   14px;
	font-weight: bold;
	text-align:  center;
}
.MainInfo { text-decoration: underline; }

.MainInfoLegenda {
    font-family: Verdana, Tahoma, Arial, Helvetica;
	font-size:   12px;
	font-weight: normal;
	text-align:  center;
}

.TableLegenda, .MainUnitInfo {
	font-size:   11px;
	text-align:  right;
}

/************ Стили для тонкой настроки ************/

.sm { /* small: компактная ячейка */
	font-size: 11px;
	padding-bottom: 0px;
	padding-top:    0px;
}

.ssm { /* super small: сверх-компактная ячейка */
	font-size: 9px;
	padding-bottom: 0px;
	padding-top:    0px;
}

.no_padding{
	padding: 0px;
}

.no_b { /* no border */
	border-style: none;
}

.nw { white-space: nowrap; }

.j { text-align: justify; }
.r { text-align: right;   }
.c { text-align: center;  }
.l { text-align: left;    }

.bl { vertical-align: baseline; }
.b  { vertical-align: bottom;   }
.m  { vertical-align: middle;   }
.t  { vertical-align: top;      }

.italic	{ font-style: italic; }
.bold	{ font-weight: bold; }

.UpperCase {
	text-transform: uppercase;
}
.LongLetSpace {
	letter-spacing: 0.2em;
}

/* Стили для подстветки дельт */
.perebor { /* Реальное значение больше лимита (дельта отрицательная) */
background-color: #FF5633;
}

.nedobor { /* Реальное значение меньше лимита (дельта положительная) */
background-color: #61A0FF;
}

.perebor_font { /* Реальное значение больше лимита (дельта отрицательная) */
	color: red;
}

.nedobor_font { /* Реальное значение меньше лимита (дельта положительная) */
	color: blue;
}

/*Подсветка отсутствующих позиций при сравнении*/

/* "Тут есть, там нет" -
	в 1-ой таблице (например, отчет по ГОЗ) есть позиция, которой нет во 2-ой таблице (например, вариант плана ГОЗ)*/
.tutYtamN, .etalonYvarN {
	background: rgb(174,255,162); /* Зеленый, подобранный по оттенку к background'у стиля DataCell */
}
/* "Тут нет, там есть" -
	в 1-ой таблице (например, отчет по ГОЗ) отсутствует позиция, которая есть во 2-ой таблице (например, вариант плана ГОЗ)*/
.tutNtamY, .etalonNvarY {
	background: rgb(255,124,116); /* Красный, подобранный по оттенку к background'у стиля DataCell */
}

/*способ привлечь внимание к ячейке отчета, не возбуждая в пользователе необоснованной тревоги*/
.report_cell_attention {
	background: #ffff79/*#f9f6e8*/;
}
