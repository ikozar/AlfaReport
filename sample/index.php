<?php
if ( $argv )
{
	foreach($argv as $a)
	{
		$arr = split('=', $a);
		$_GET[$arr[0]] = $arr[1];
	}
}

$aInfRazd=array('menu'=>'menu', 'title'=>'title', 'titleFull'=>'titleFull', 'cont'=>'cont');
$forum = '�����';
$sDownload = '��������';
if ($_GET['lang'] == 'e') {
	foreach($aInfRazd as $k=>$v)
		$aInfRazd[$k] = $v . '_e';
	$forum = 'Forum';
	$sDownload = 'Download';
}
$ch=$_GET['chapt'];
$isAll=$_GET['all'];
$isPrepared=$_GET['prepared'];
echo $_GET['impl'];
if (!$_GET['impl']) {
	$_GET['impl'] = 'Java';
}
//echo $_SERVER['REMOTE_ADDR'];
 if ( !isset($_GET['all']) && $_SERVER['REMOTE_ADDR'] == '127.0.0.1' )
 	$isAll = 1;
$readMe='<a class="local" href="../ReadMe.txt" style="color: Crimson;">��. ReadMe.txt</a>';
$dnLoad='<a class="local hl" href="../urp.source.zip">';
$dnLoadDBI='<a class="local hl" href="../dump-insert.zip">';
$mail="<a class=\"local\" 
	href=\"m&#97;i&#108;t&#111;&#58;";
$sWebmoney = 'R248895016120 (�����), Z354953144449 (USD), E204313335288 (EUR)';
if ( $isAll )
{
	$mail .= "k&#111;z&#97;r_x&#64;m&#97;i&#108;";
	$reclama = '<div style="width:468px; height:60px"></div>';
/*
	$reclama = '
<!-- start onep.ru -->
<iframe src=http://onepage.ru/ads/468x60.php?site_url=alfa-report.onep.ru frameborder=0 vspace=0 hspace=0 marginwidth=0 marginheight=0 scrolling=no width=468 height=60></iframe>
<!-- end   onep.ru -->
';
*/
}
else
	$mail .= "ik&#111;z&#97;r&#64;int&#46;spb";
$mail .= "&#46;ru?subject=ReportBuilder\">";

if ($_GET['lang'] == 'e')
	$sMail .= 'Mail';
else
	$sMail .= '�������� ������';

function checkImpl($i) {
	return $_GET['impl'] == $i ? 'checked="checked"' : '';
}
 
function goTo($rej, $dop='') {
	$lang = $_GET['lang'];
	if (substr($dop, 0, 6) == '&lang=') {
		$lang = substr($dop, 6);
	} elseif ($lang == 'e') {
		$dop .= '&lang=' . $lang;
	}
	$impl = $_GET['impl'];
	$dop .= '&impl=' . $impl;
	if ( substr($rej, 0, 6) == 'sample' ) {
		if ( $_GET['prepared'] ) {
			if ($lang == 'e')
				$rej .= '_e';
			return $rej . '.html';
		} else {
			return '../exec.Report.php?urp_tpl=sample/' . $rej . '.urp.xml' . $dop;
		}
	}
	else {
		if ( $_GET['prepared'] ) {
			if ($lang == 'e')
				$rej .= '_e';
			return $rej . '.html?' . $dop;
		}
		else
			return 'index.php?chapt=' . $rej . $dop;
	}
}

$a_ch = array(
'index'=>array(
		'menu'=>'�������',
		'title'=>'�������',
		'titleFull'=>'ReportBuilder. ������� ��������. ����������� �������� ������� � HTML. Download ReportBuilder.',
		'menu_e'=>'Main Page',
		'title_e'=>'Main Page',
		'titleFull_e'=>'ReportBuilder. Main Page. Peculiarity reporting in HTML. Download ReportBuilder.',
		'cont'=>'
<h1>��� ����� ReportBuilder?</h1>
ReportBuilder ��� ����������� ��������� HTML �������. ����������� ������� ������ � ������� (������) ������ ��������� �������� � XML �����.

<h1>������ ������� "��������" ReportBuilder?</h1>
�������� ReportBuilder ��� ���������� � ������� ������� ������� �����������-�������������� ������. �� ��� ���� ����������� ������� 400 ��������������� ������������� ������� (�������������� ������ �� ������ �������� �����), � �����, ������ ������� ������
<a style="color: Crimson;" href="image/samp_use.jpg">(��. snapshoot).</a>
��� ��� ������� � ������� ������������.<BR/>

<h1>��� ReportBuilder ����� �� ������ ����������� � ����� � ���� �����������?</h1>
ReportBuilder ����� �� MS SQL Reporting Service ���, ��� ��� � � MSRS ������ ������ ����������� �� ���� ��������� ���������, � �� ������� ��������� ������� � ������ �����.<BR/>
����������� ReportBuilder:
<ul>
<li>
��������� ����������� DataSet ��� ������. ������� ����� � ReportBuilder ��������� ��������� DataSet �� ��������� ��������������� DataSet. ������� DataSet "MAIN" ������������ ��� ������������ �������� (DETAL) ������ ������. �� ��������� (<b>Link</b>) �� ������ DataSet �� �������� ����/����� (��� Foreign Key � ��).
��� DataSet, � ���� �������, ��������� �� ������ DataSet, ������ ����������� ������ �� ���� DataSet �� ���������� (<a style="color: Crimson;" href="requ_graph.html">��. ��������� � ������� �2</a>).
������ ��� ������ ������ Parent DataSet � Child DataSet ����� ���� ��� ���� ������, ��� � ��������� (<a href="http://msdn.microsoft.com/ru-ru/library/ms159106.aspx">���� SubDetal � MSSRS</a>). ��������� � �������� ���� DataSet ������� �������������� �������� <b>%%DataSet1.DataSet2...DataSetN.���_����%%</b>.
����������� ������ ������������ ��������� �������� ORM ��� DOM ������ XML.<BR/>
������, ��� ��������� ������� ��� ������ DataSet ����������� ������ ���� ������ � ��, ������� �������� ��� ������ �� ������� ��������� Parent DataSet (��� ����������� �������� ������� � URL �������� urp_print_query=1).<BR/>
��� ��������� 1) ��������� ������� ������� - "�������� � ��������", 2) �������� �������� ���� ����������� �������� ������, 3) �������� ������� ��������� �������������� ��������
</li>
<li>
������ ������������ ���������. ��� ��������� �������, XML ��������� ������ ������� ���������������� � ����� PHP �������, ������� ������������� � ������������ ��� ������ � ����� ������ ��������������� ������ (��� ��������� ���� ������� ������� � URL �������� urp_print_band=1). ���, � ����� �������, ��������� �������� ������� ������������ ���������, � ������ �������, ��������� ������������ � ���� ������� ������ ����� PHP ����������� (� �������������� ���������������� ��� ������� � ������) ��� ���������� ����� ������-������ ��������� ������ � �� ����������� � ���������. ������ �� SubDetal DataSet 
</li>
<li>
����������� ������ ��������� ����� SubDetal DataSet � ����� (<b>SetBand</b>) ���� � ������ ������/������ ������������ ������, ���� � ����� ������ (��. ������ �2). �.�. �������� ������������� �� ��������� �������.
</li>
<li>
����������� �� ���������� ��������������� - �����������/������������� ��������, �����, ���������� ������� �������� � �.�. (��. ������ �3);
</li>
<li>
����������� �� ������������� ������������ ������ (��. ������ �4);
</li>
<li>
������� �������� ������������ ������ (�� ����. ����, ���-������ ������ �������� ����� ������������ ������ � ������ ������������ � �������� ����� - ���� ����� �����������)
</li>
</ul>

<h1>����������� ReportBuilder</h1>
� ReportBuilder ���� ��� ����������� ��� ������������ ������� � ������������� ��������� �������:
<ul>
<li>
������������� ������ (����� ������ ����� ���� ��������������) � ���������� ������ � �� ������� � ��������� ��� "�������" (������ �����������) �����;
</li>
<li>
������������ ������� ������ (�����-������)
</li>
<li>
���������� ��������������� ������� (� ����������� - �������������� ������, ��� ����������, �� �����);
</li>
<li>
����������� ����� ��� �� ��������, ��� � �� �������;
</li>
<li>
������� ����������� �� �������� ������������� �������;
</li>
<li>
������������� � ����� � ����� ����� ������ ��������� �����/�������� SubDetal DataSet � ����� � ������ ������/������ ������� ��� � ���� ������.
</li>
</ul>
�������� ��. <a class="local" href="' . goTo('ability') . '" style="color: Crimson;">"�����������"</a>
<BR/>

<h1>�� ��� ������� ReportBuilder, ��� ��� ����� ������������?</h1>
ReportBuilder ������� �� <a href="http://www.php.net/">PHP</a> � JavaScript. ��� ������ ����� ����� ��������� ��� ����������� WEB �������.
���� �� ����������� ����������� � ���������� � ������������� "�������������" WEB �������, ���� ��������������� ������ WEB ������ <a href="http://www.myserverproject.net/">MyServer</a>. ��� ��������� ������ PHP-�������� ��� ���������� ������� MIME Type ��� ���������� php:<BR/>
MIME type: application x-httpd-php<BR/>
Action: Run as FastCGI<BR/>
Manager: D:/local/PHP/php-cgi.exe (���� � ����� PHP).<BR/>
ReportBuilder ����� ����� ��������� � ��������, ��� ����� ���������� ������� � ��������� ������ "����_�_PHP\php.exe exec.Report.php urp_tpl=���_�����_�������" (��� ��������� �������). �������������� HTML ���� ����� �������� �� ��������� ������ PHP (�� ��������� ������ �������� �� ���� ������ ��������� ����� "|"-pipe ��� ��������� � ���� "&gt;").<BR/>
������� AlfaReport ������������ ��� ���� Postgres � MySQL (� ������������ ���� ����� �� � ��������� �������).<BR/>
���� �� ����������� ������ ����, ����������� � AlfaReport, �� �������� �������, �.�. ��� ��������� � �� AlfaReport ���������� ����� PHP Data Objects (PDO). � ��������� ����� � PHP ���� PDO-�������� ��� ����: MS SQL Server, Sybase, Firebird, Interbase 6, IBM DB2, IBM Informix Dynamic Server, Oracle Call, ODBC v3 (unixODBC), SQLite.

<h1>���� �� ���������� �������� ������������ �������� ������?</h1>
����������� ��������� ���� ���. ������� ����������� � XML ����� � �������� ���������, ��� ��������� XML (� ������������ ���� XSD).<BR/>
� �� �� �����, ����� XML ����� ������������, �������� ���� ������
��� ���������� ������, "�������" �������� � ������� �������� <b>"&lt;SwitchBand&gt;"</b>, ������������ PHP ���, ������� ����� ����������� ������ �� ������ ������ �� � ��������� ����������� ������.

<h1>���� �� � ReportBuilder API ��� ������������� �������� ������?</h1>
ReportBuilder ���������������� ��� openSource, ��������������, �� ������ ������������ ������ ������������ ������ ��� ��� �����������������, ������ �� ��� ������, ����� ����������� ����������� XML-������, �� ������ �������� ����� ����������� �����

<h1>������ ������ ������ HTML?</h1>
��� �������� ���������� ������, ������� ������� � ���� ����� ������� (��� Cell) ������� ������� ��������� �������, �������� ��� ������������� (�������/�������� ��������). ��� ���, ��� ����������� HTML � CSS ��������� � ��������� (��� ������� ������) ���������� ������ �������������� ����������, ��������, ������������ � �� (� ����� ����� ������ ����� ������������ ������� HTML �������� - DIV, SPAN � ��.).<BR/>
����� ���������� ������ ��� �� ��������, ��� � �� ������� (colspan, rowspan).<BR/>
����� ������ ������������� ������.<BR/>
����� HTML ����� ��������� �� ���� ��� ������������.<BR/>
��� � ��� �������� � HTML, ��� ��� � �������. ����� ������� ���� ������ � FireFox (���� ������� ����� ������� - THEAD �� ����� ����). ��� �������, ��� ������ ����� ������� HTML ��������� � OpenOffice � Word (swriter.exe) ��� Excel (scalc.exe) ������� � URL �������� urp_out=oo_text ��� oo_calc (���� MIME �������� �� ���������, �� ������ ��� ���������� ����� ���� ������� ���� � swriter.exe � scalc.exe).
������, � OpenOffice ���� ����������� �������������� ����� � PDF.

<h1>� ����� ��� ��������, ����� HTML, ����� ������� �����?</h1>
����� HTML ����� ����� ���� ����� ������� � XML �������.
� ��������� ������ ���� ���������� XSL ��������������� XML-������ � ������ XML MS Office 2003, ��� �������� � MS ��� OpenOffice ��� ������ ��������������.

' . ( $isAll ? '
<h1>��� ���������������� ReportBuilder?</h1>
ReportBuilder ���������������� �� �������� <a href="http://www.gnu.org/licenses/gpl-3.0.html"><img src="http://gplv3.fsf.org/gplv3-127x51.png" alt="GNU General Public License v3"/></a>.<br/>
' : '') . '

' . ( $isAll ? '
<h1 style="margin-bottom: 0px;">�������������:</h1>
'.$readMe.'
<h1 style="margin-bottom: 0px;">�������� ReportBuilder:</h1>
<span id="refDN">'.$dnLoad.'latest sources ReportBuilder</a></span><br/>
<span>'.$dnLoadDBI.'dump DBSample (insert mode)</a></span>
' : '') . '

' . ( $isAll ? '
<p>
'.$mail.'<span style="color: Crimson;">�����</span></a> � ������������� ��������� ��� ���������, ��������� � �����������.
</p>
' : '') . '

',
		'cont_e'=>'
<h1>What is AlfaReport?</h1>
AlfaReport it is a builder of table grouping and pivor reports in HTML. Definition of data sets and a section report template is specifying in XML a file.

<h1>At what stage of "maturity" is AlfaReport?</h1>
AlfaReport�s prototype has been developed in the large project of centre of situation analysis. On its basis it has been developed about 400 interrelated reports (refining data on a cell by a click), and also master of summary tables 
<a style="color: Crimson;" href="image/samp_use.jpg">(see snapshoot).</a>
All this is actively and successfully used.<BR/>

<h1>In what aspects is AlfaReport similar to other builders and what are its features?</h1>
AlfaReport it is similar to MS SQL Reporting Service as in it as well as in MSRS the template of the report is formed on the basis of tabulated structure, instead of a method of drawing of the table with lines.<BR/>
Features of AlfaReport:
<ul>
<li>
philosophy of organization DataSet for the report. A good form in AlfaReport is splitting big DataSet into set interconnected DataSets. Main DataSet "MAIN" is used for formation of the basic (DETAL) section of the report. It links (Link) to others DataSet on value of a field/fields (as Foreign Key in a DB). These DataSet, in turn, refer to other DataSets, and links to one DataSet from several  (<a style="color: Crimson;" href="requ_graph.html">see the explanatory for example �2</a>)are allowed. And for every line Parent DataSet in Child DataSet both one line, and set (<a href="http://msdn.microsoft.com/ru-ru/library/ms159106.aspx">similar SubDetal in MSSRS</a>)are accepted. The reference(manipulation) to value of field of DataSet of the descendant is carried out with macros <b>%%DataSet1.DataSet2...DataSetN.���_����%%</b>.
The Data structure represents some analogy ORM or DOM to model XML.<BR/>
And, for reception of sample for any DataSet only one query to a DB which chooses all the lines to which Parent DataSet refers (for logging of queries set in URL parameter urp_print_query=1) is carried out.<BR/>
It allows 1) to simplify complex queries - "divide and rule", 2) to speed up a phase of data reading, 3) to raise a degree of repeated queries usability
</li>
<li>
way of a document formation. At processing a template, the XML structure of a template sections is transformed to PHP function text which is compiled and used for printing the text of corresponding section (to view a code of function set parameter urp_print_band=1 in URL) in the report of the. It, on the one hand, allows to accelerate process of formation of the document, on the other hand, allows to use any PHP build in a body of a template of section (with use of macrosubstitutions for access to data) for realization any business-logic of data processing and their display in the document. Data from SubDetal DataSet 
</li>
<li>
ability of a conclusion of set of lines SubDetal DataSet in a cycle (SetBand) or in different lines/cells of the formed report, or in one cell (see an example �2).Thus the necessity for the enclosed reports disappears
</li>
<li>
abilities to realization of interactivity - collapse/expand columns, lines, linking of reports by links, etc. (see an example �3)
</li>
<li>
abilities of dynamic formation of the report (see an example �4)
</li>
<li>
high speed of formation of the report (ideally. If, somebody can compare time of formation of the report to other builder and compare time - I will be very grateful)
</li>
</ul>

<h1>AlfaReport abilities</h1>
There is all necessary for formation of complex and non-standard tabulared reports in AlfaReport:
<ul>
<li>
grouping of the report (any groups can be hierarchical) with reception of results and printing of them in heading or "cellar" (the footer) groups
</li>
<li>
creating of pivot tables (cross-tables)
</li>
<li>
realization of interactivity of reports (in prospect - editing of the report, as they say, on-site)
</li>
<li>
merging of cells both in columns, and in the lines
</li>
<li>
wide abilities of creation non-standard reports
</li>
<li>
use and printing the set of lines/values SubDetal DataSet in a cycle in different lines/cells of the table or in one cell in any place of the report
</li>
</ul>
In detail see <a class="local" href="' . goTo('ability') . '" style="color: Crimson;">"Abilities"</a>
<BR/>

<h1>On what basis is AlfaReport developed, how it can be used?</h1>
AlfaReport is written on <a href="http://www.php.net/">PHP</a> and JavaScript. Its start is better to execute under control of a WEB server. If you suffer difficulties with installation and configuration of "industrial" WEB a server, I can recommend light WEB server <a href="http://www.myserverproject.net/">MyServer</a>. For setup of a call of PHP-scripts it is necessary for you to add MIME Type for extension php:<BR/>
MIME type: application x-httpd-php<BR/>
Action: Run as FastCGI<BR/>
Manager: D:/local/PHP/php-cgi.exe (���� � ����� PHP).<BR/>
It is also possible to start AlfaReport locally, for this purpose it is necessary to type in a command line "Path_to_PHP \php.exe exec. Report.php urp_tpl=name_of_template_file" (or to start a process). It is possible to receive the resulting HTML file from output PHP stream (to transfer to an input of other program from a command line through "|"-pipe or to direct to a file "&gt;").<BR/>
Examples of AlfaReport are adapted to DBMS Postgres and MySQL (there are dumps with test data in distibutives).<BR/>
If you use another DBMS, it will be easy to port into AlfaReport, as AlfaReport uses PHP Data Objects package (PDO) to address to DB. Now there are the following PDO-drivers for DBMS in PHP: MS SQL Server, Sybase, Firebird, Interbase 6, IBM DB2, IBM Informix Dynamic Server, Oracle Call, ODBC v3 (unixODBC), SQLite.

<h1>Is there a visual designer to creating of report templates?</h1>
While there is no visual designer. Templates are formed in XML file in text editor, or XML editor (there is XSD in the distribution kit).<BR/>
At the same time, through XML it is possible to use, for example one template for several sections, implementing differences with the help of <b>"&lt;SwitchBand&gt;"</b> element, and to use PHP code which can cardinally change not only composition but also structure of the received report.

<h1>Is there API for dynamic creation of the report in AlfaReport?</h1>
AlfaReport is distributed as openSource, so you can use classes of report formation as to you like, however in my opinion, it is easier to dynamically form a XML-template, on the basis of which the report will be formed.

<h1>Why HTML format was chosen?</h1>
While creating a column report, setting a template in the form of table cells (tag Cell) is much more convenient than drawing sticks, especially at support (insert/delete a columns). And abilities of HTML and CSS allow (provided having enough knowledge) to achieve any formatting of a frame, spaces, the arrangement and so on easily (in any place of the report it is possible to use usual HTML elements - DIV, SPAN and etc.).<BR/>
It is possible to unite cells both in columns, and in lines (colspan, rowspan).<BR/>
It is possible to make interactive reports.<BR/>
It is easy to keep HTML report on a disk or to publish it in WEB.<BR/>
But what�s a problem with HTML, is that it has difficulties with a printing. An attempt to solve the problem is only in FireFox (there is a hyphenation of a table cap - THEAD on a new sheet). As a variant, for printing it is possible to open HTML page in OpenOffice in Word (swriter.exe) or Excel (scalc.exe) having added parameter urp_out=oo_text or oo_calc in URL (if MIME of a browser are not set up, than it will be necessary to specify obviously a path to swriter.exe and scalc.exe at first time). By the way, in OpenOffice there is an ability to export the report in PDF.

<h1>In what other formats, except for HTML, it is possible to create the report?</h1>
Except for HTML the report can be received also in XML format. At the moment XSL converter of the XML-report to format XML MS Office 2003 for opening in MS or OpenOffice without loss of formatting is being developed.

<h1>How AlfaReport is distributed?</h1>
AlfaReport is distributed under the license <a href="http://www.gnu.org/licenses/gpl-3.0.html"><img src="http://gplv3.fsf.org/gplv3-127x51.png" alt="GNU General Public License v3"/></a>.<br/>

<!--h1 style="margin-bottom: 0px;">�������������:</h1>
'.$readMe.'-->
<h1 style="margin-bottom: 0px;">Downloading AlfaReport:</h1>
<span id="refDN">'.$dnLoad.'latest sources ReportBuilder</a></span><br/>
<span>'.$dnLoadDBI.'dump DBSample (insert mode)</a></span>

<p>
'.$mail.'<span style="color: Crimson;">The author</span></a> will listen to all claims, wishes and offers with pleasure.
</p>
',
),

'ability'=>array(
		'hideX'=>1,
		'menu'=>'�����������',
		'title'=>'�����������, ����������� ���������',
		'titleFull'=>'ReportBuilder. ����������� ����������� �������, ����������� ���������.',
		'menu_e'=>'Ability',
		'title_e'=>'Ability, evolution',
		'titleFull_e'=>'ReportBuilder. Ability of report builder, planing evolution.',
		'cont'=>'
<h1>�����������:</h1>
<ul>
<li>
����� � ������ ����� (� �.�. ������������� � ����� ������� ��������) � ������� ��� � ��������� ��� � "�������" (������ �����������) �����;
</li>
<li>
������������ ������� ������ (�����-������);
</li>
<li>
������������� ������� ���������� ������ � ���� "����������" ������;
</li>
<li>
���������� ��������������� ������� (� ����������� - �������������� ������, ��� ����������, �� �����);
</li>
<li>
����������� ����� ��� �� ��������, ��� � �� �������;
</li>
<li>
��������� ������� � �� �� ��������� ��������������� ��������;
</li>
<li>
����� �������� ���� SubDetal � ����� � ������ ������/������ ������������ ������, ���� � ����� ������;
</li>
<li>
������������� � ����� � ����� ����� ������ ��������� �����/�������� SubDetal DataSet � ����� � ������ ������/������ ������� ��� � ���� ������;
</li>
<li>������������ ������ � ���� ��������� HTML, XML, Microsoft Office 2003 XML (� �����������) ��� JSON (JavaScript Object Notation + JavaScript �������� �������);
</li>
<li>
������� ����������� �� �������� ������������� �������.
</li>
</ul>

<h1>����������� ���������:</h1>
<ul>
<li>������� ReportBuilder � ��������� UTF-8;</li>
<li>���������� ������� ������������ "���������" ����� ������ ��� �������������;</li>
<li>������������� �������������� �������� ����� ������� � ���������� 
������ � ����������� �������� � �� ����� Ajax (� �������� ��� ��� ����������� ��� ������, �������� ����������,-
�������� ������ � ���������);</li>
<li>���������� ��� ����� � �����-������ ���������� ��������, ��������� � �������� ��������;</li>
<li>���������� "�������������" �������� ��� ��������� �� ����������� ������� �� �������� ����������;</li>
<li>����������� �� ������ ��������� �������;</li>
<li>���������� ��������� � �������� RTF � PDF;</li>
<li>���������� ����������� �������, ��� ������������ ���������</li>
<li>���������� ���� �������������� � �������������� ������ JSON � ���������� ExtJS</li>
</ul>
' . ( $isAll ? '
<p>���� ���� ������� ������ ������,- ��������� �� ��������� 
<span>"<a class="local hl" href="' . goTo('donate') . '">�����������</a>"</span>.</p>
' : '') . '
',


		'cont_e'=>'
<h1>Ability:</h1>
<ul>
<li>
printing group in report (including hierarchical) with summary both in header, and footer;
</li>
<li>
creating pivot table (cross-table);
</li>
<li>
preparing template as table breadboard model;
</li>
<li>
abilities to realization of interactivity report (in a future - editing value of cell report, in place);
</li>
<li>
merging of cells both in columns, and in the lines;
</li>
<li>
splitting query to database into set interconnected queries;
</li>
printing the set of SubDetal DataSet in a cycle in different lines/cells of the table or in single cell;
</li>
<li>
creating a report in type HTML, XML, Microsoft Office 2003 XML (in the future) or JSON (JavaScript Object Notation + JavaScript creating table);
</li>
<li>
wide abilities of creation non-standard reports.
</li>
</ul>

<h1>Planing evolution:</h1>
<ul>
<li>shift ReportBuilder into UTF-8;</li>
<li>dynamicaly create part of table under expand;</li>
<li>
interactivity editing report with the sum calculation and saving to database;</li>
<li>convert report into RTF and PDF;</li>
<li>realization biznes graphics</li>
',
),

/*
'news'=>array(
		'menu'=>'�������������',
		'title'=>'������� � ���������� � ������������ �������',
		'titleFull'=>'ReportBuilder. ������� � ���������� ����������� ������� � ������������ �������.',
		'hideX'=>true,
		'cont'=>'
11/07/2008<BR/>
<ul>
<li>����� ������� �������� � ������ THEAD. ������ ��� ������ �� Mozilla ����� ����������� �� ����� ����;</li>
</ul>
',
),
*/

'cp'=>array(
		'menu'=>'��������&nbsp;�������',
		'title'=>'�������� ������������ �������',
		'titleFull'=>'ReportBuilder. �������� ������������ ������� �� ��� reporting.',
		'cont'=>'<h1>�������� ������������ ������� (����� ��)</h1>
����������� ������ ������������ ����� ����� �� Postgres, ���������� �������:
<DL>
<DT>vendor</DT> <DD>- ������������� �������</DD>
<DT>store</DT> <DD>- ��������</DD>
<DT>personal</DT> <DD>- ���������� ��������</DD>
<DT>subdivision</DT> <DD>- ������������� ���������</DD>
<DT>type_subdiv</DT> <DD>- ���������� ���� �������������</DD>
<DT>teritory</DT> <DD>- ���������� ��������������� ��������� � ��������������</DD>
<DT>wares</DT> <DD>- �����</DD>
<DT>type_wares</DT> <DD>- ���������� ���� �������</DD>
<DT>realizations</DT> <DD>- �������</DD>
<DT>delivery</DT> <DD>- �������� (�������) �������</DD>
</DL>
<BR/>
<IMG src="image/schema_cp.gif"/>
',
		'menu_e'=>'Sample&nbsp;description',
		'title_e'=>'Sample&nbsp;description',
		'titleFull_e'=>'ReportBuilder. Description sample data DB for reporting.',
		'cont_e'=>'<h1>Description sample data DB (DB model)</h1>
Sample data is backup data for Postgres or MySQL, including a table:
<BR/>
<IMG src="image/schema_cp.gif"/>
',
),

'demo'=>array(
		'menu'=>'������������',
		'title'=>'������������ ������',
		'titleFull'=>'ReportBuilder. ������������ ������ ����������� �������, ����������� ���������� ������� (���������������, �����-������� � ��.).',
		'cont'=>'
<p>��� ������������ ������ ReportBuilder ����������� ��������� �������� �� ���� ����������� ������ (��. "�������� ������������ �������").
' . ( $isPrepared ? '
�� ��������������� ������� ����� �������� �������, ������� �������������� ������. 
����� ���������� ������������ ����-�������, �� ������ ������� ReportBuilder � ��������� �� �� ����� ������ ('.$readMe.').
' : '') . '
</p>
<table cellspacing=3>
<tr>
<th>�</th>
<th>������</th>
<th>������ ������</th>
<th>�������� �������</th>
</tr>
<tr>
	<td>1</td>
	<td>
		������ ������ �� ��������� � �������� �� �����������, �������� �����-������� �� ��������� � �������, ������������� ������ "���������������"
	</td>
	<td style="text-align: center;"><a href="' . goTo('sample1') . '">���������</a></td>
	<td style="text-align: center;"><a href="sample1.urp.xml">����������</a></td>
</tr>
<tr>
	<td>2</td>
	<td>
		������ ������ �� ��������� � �������� �� ����� �������, �������� �����-������� �� ���������, 
		�������������� �������������� � �������������� (�� �������������� ��������� ��� ������� - ������������� � �������� (�� MAIN)), 
		���������� �� �������� <b>��������</b> (�������) ����������� ������ �� ����� �������, ����� 3 ����� �������� � 3 ��������� � ������ ��������.<BR/>
		������������� ������������� ��� "�������������" (SET) �������: 
		����������� ��������� (��������� � ������ �������� ����� &lt;BR&gt;) �
		����� �� ������� ������� (����� �� ���������), ������� ��������� � ������ "TOTAL", � ����� �� ������� ������� &lt;SetBand&gt;
	</td>
	<td style="text-align: center;"><a href="' . goTo('sample2') . '">���������</a></td>
	<td style="text-align: center;"><a href="sample2.urp.xml">����������</a></td>
</tr>
<tr>
	<td>3</td>
	<td>
		������ ������ �� ��������� � �������� ������������ � JSON (���������� �������� � FireFox ��� Opera ������ 9.1, ����� ������� ����� ������������� �� ����� innerHTML, � DOM-���������, ��� ����������� ���������), ������������� ������������/�������������� ����� � �������
	</td>
	<td style="text-align: center;"><a href="' . goTo('sample3', '&urp_type=json') . '">���������</a></td>
	<td style="text-align: center;"><a href="sample3.urp.xml">����������</a></td>
</tr>
' . ( $isPrepared ? '
' : '
<tr>
	<td>4</td>
	<td>
		������ ������ � ���������� �������� ����� ��������� �� ������������
	</td>
	<td style="text-align: center;"><a href="../gen_report.php?R[]=id_store&C[]=id_quarter">���������</a></td>
	<td style="text-align: center;"></td>
</tr>
') . '
</table>

<h1>��� ��������� ���. ���������� ���������� ��������� � URL:</h1>
<ul>
<li>"urp_time=1" - ������� ������������ ���������;</li>
<li>"urp_' . ( $isAll ? 'print_data' : 'query') . '=1|�������� �������" - ���� ������/������ �������;</li>
<li>"urp_' . ( $isAll ? 'print_' : '') . 'band=1|�������� ������" - ������ ������/������</li>
<li>"urp_print_query=1" - ������ ��������</li>
</ul>
<BR/>

������ ������ �������� � XML ����� � ����� �������� ��� XML ���������.
��� ������� ����� ������� ���������� ��������, �� � �� ���� � ���� �������� ������.
��������� ����� ���������, �� � �� �� ����� �� ��������� ��������, �������� ���� ������
��� ���������� ������, "�������" �������� � ������� �������� "&lt;SwitchBand&gt;", ������������
PHP �������, �������, � ����������� �� ���������, ����������� ������ �� ������ ���������� �����,
�� � ��������� �������. ����� ���������������� � ���������� ��������� �������� ������ ��������.<BR/>

',
		'menu_e'=>'Demo',
		'title_e'=>'Demo reports',
		'titleFull_e'=>'ReportBuilder. Demonstration building report, report specifics.',
		'cont_e'=>'
<p>ReportBuilder demo include samples (see "Sample description").
' . '
</p>
<table cellspacing=3>
<tr>
<th>�</th>
<th>Sample</th>
<th>Report</th>
<th>Template</th>
</tr>
<tr>
	<td>1</td>
	<td>
		report of delivery and realization on employee, building pivot-table on quarters and month, hierarhy group for "Teritory"
	</td>
	<td style="text-align: center;"><a href="' . goTo('sample1') . '">run</a></td>
	<td style="text-align: center;"><a href="sample1.urp.xml">view</a></td>
</tr>
<tr>
	<td>2</td>
	<td>
		report of delivery and realization of wares kind, building pivot-table on quarters and teritory, 
		descending sort <b>summary</b> (year) rate values, printing 3 top and 3 bottom rate of a store.<BR/>
		Additional printing set of store chief (by &lt;BR&gt;), and total summary for wares kind in section "TOTAL" (by &lt;SetBand&gt;)
	</td>
	<td style="text-align: center;"><a href="' . goTo('sample2') . '">run</a></td>
	<td style="text-align: center;"><a href="sample2.urp.xml">view</a></td>
</tr>
<tr>
	<td>3</td>
	<td>
		interactive report (with expande/collapse) (desire run sample under FireFox or Opera since 9.1, for building table on JS by innerHTML)
	</td>
	<td style="text-align: center;"><a href="' . goTo('sample3', '&urp_type=json') . '">run</a></td>
	<td style="text-align: center;"><a href="sample3.urp.xml">view</a></td>
</tr>
' . ( $isPrepared ? '
' : '
<tr>
	<td>4</td>
	<td>
		������ ������ � ���������� �������� ����� ��������� �� ������������
	</td>
	<td style="text-align: center;"><a href="../gen_report.php?R[]=id_store&C[]=id_quarter">���������</a></td>
	<td style="text-align: center;"></td>
</tr>
') . '
</table>

<h1>For printing additional information use in URL arguments:</h1>
<ul>
<li>"urp_time=1" - time;</li>
<li>"urp_' . ( $isAll ? 'print_data' : 'query') . '=1|set of DataSet" - view all/DataSets data;</li>
<li>"urp_' . ( $isAll ? 'print_' : '') . 'band=1|set of band" - prepared template for section</li>
<li>"urp_print_query=1" - query text</li>
</ul>
<BR/>

Report template is a simple XML file.
Your can specify single band for several section. For different section structure use element "&lt;SwitchBand&gt;".
Also your can use php scriptlet.<BR/>

',
),

);

 if ($isAll)
 {
$a_ch['donate']=array(
		'menu'=>'�����������',
		'title'=>'�����������, ��������������, �������������',
		'titleFull'=>'ReportBuilder. �����������, ��������������, �������������.',
		'cont'=>'
<p>ReportBuilder ��������������� � ������������ �������.
</p>
<p>����� ������������� � ���������������� ReportBuilder � � ��������� �����-���� ������ �� ����� ������.</p>
<p>����� �������� ����������� �� ������������� ReportBuilder � ��������.</p>
<p>���� ���� ������� ������ ����������� � �������� ��������,- 
������������� ����������� �� �������� WEBMONEY: ' . $sWebmoney . '.
������� ���������� ���� ���������.
</p>
<a href="http://webmoney.ru/rus/addfunds/wmr/index.shtml">������� �������� ����� �� WEBMONEY</a>.
<h1>����� ����������:</h1>
<div style="width: 100%; height: 20px; border: 1px dotted blue;"></div>
',
		'menu_e'=>'Donate',
		'title_e'=>'Donate, cooperation',
		'titleFull_e'=>'ReportBuilder. Donate, cooperation, gratitude.',
		'cont_e'=>'
<p>ReportBuilder is opensource project.
</p>
<p>����� ������������� � ���������������� ReportBuilder � � ��������� �����-���� ������ �� ����� ������.</p>
<p>����� �������� ����������� �� ������������� ReportBuilder � ��������.</p>
<p>���� ���� ������� ������ ����������� � �������� ��������,- 
������������� ����������� �� �������� WEBMONEY: ' . $sWebmoney . '.
������� ���������� ���� ���������.
</p>
<a href="http://webmoney.ru/rus/addfunds/wmr/index.shtml">������� �������� ����� �� WEBMONEY</a>.
<h1>����� ����������:</h1>
<div style="width: 100%; height: 20px; border: 1px dotted blue;"></div>
');
}

 if ( !$ch || !$a_ch[$ch] )
 	$ch = 'index';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!--DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"-->
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<META NAME="Soft" CONTENT="AlfaReport"/>
<META NAME="Codesearch" CONTENT="PHP"/>
<META NAME="Keywords" CONTENT="�������� ������� � HTML report builder in HTML reporting cross-table ����� ������� opensource download"/>
<META NAME="Description" CONTENT="�������� ������� � HTML report builder in HTML reporting freeware"/>

<title><?echo $a_ch[$ch][$aInfRazd['titleFull']];?></title>

<style>
body {
background-color: #1C2C1C;
padding: 0px;
margin: 20px;
}
th {
border: 1px solid black;
}
td {
background-color: Khaki;
padding: 1px 3px;
}
td.m {
background-color: #4C4C4C;
border: 1px outset buttontext;
}
.arep {
background-image: url(image/arep.gif);
background-repeat: none;
margin: 5px;
height: 70px;
border: 2px inset black;
}

ul {
margin: 0px;
padding: 0px;
}
li.m {
display: block;
list-style-type: none;
padding: 5px;
height: 20px;
font-family: Georgia;
font-size: 15px;
font-weight: 900px;
color: black;
}
li.l {
float: left;
border-right: 2px solid black;
background-color: LightSkyBlue;
}
li.h {
margin-bottom: 5px;
border: 2px outset black;
background-color: GoldenRod;
color: black;
}

a.local {
text-decoration: none;
color: inherit;
}
span a.hl {
color: Crimson;
}

dt {
font-weight: 600;
display: compact;
height: 0px;
}
dd {
margin-left: 10em;
}
h1 {
font-size: 18px;
}
h2 {
font-size: 15px;
margin: 6px;
}
.radio {
font-size: 15px;
margin-left: 30px;
}
.rtext {
font-weight: bold;
color: lightBlue;
}
p {
x-text-indent: 10px;
}
</style>

<body>
<!-- report reporting builder ����� ���������� ������ -->

<table id="tabInf" width="100%" cellpadding=0 style="height: 98%;">
<tr>
	<td class="m" colspan="3" rowspan="2" style="border: 7px solid #1C2C1C; background-color: Peru;">
		<div class="arep" x-style="background-color: DarkGray; border: 2px inset LightSkyBlue;
			font-family: cursive; font-size: 31px; font-weight: 900;
			padding-left: 20px; text-align: left; color: black; ">
				<!--Report<BR/>&nbsp;&nbsp;Builder-->
		</div>
	</td>
	<td class="m" colspan="1" height="80px" width="80%" style="padding-left: 20px; color: Gold;">
		<span style="font-family: Verdana; font-size: 24px; font-weight: 900;"><?=$a_ch[$ch][$aInfRazd['title']]?></span>
	</td>
	<td id="reclama" class="m" style="padding: 0px; border-left: 2px solid #4C4C4C;">
		<a style="vertical-align: middle; position: relative; float: right;" href="
		<?
			if ($_GET['lang'] == 'e') {
				echo goTo('index', '&lang=') . "\"><img alt=\"ru\" src=\"image/rus.gif\"></a>";
			} else {
				echo goTo('index', '&lang=e') . "\"><img alt=\"eng\" src=\"image/eng.gif\"></a>";
			}
		?>
<?echo $reclama;?>
	</td>
</tr>
<tr height="20px">
	<td class="m" colspan="3" rowspan="2" style="padding: 0px; background-color: LightSkyBlue;">
		<ul style="color: black;">
		<?
			foreach($a_ch as $k=>$v)
				if ( !$v['hideX'] )
					echo "<li class=\"m l\"><a class=\"local\" href=\"" . goTo($k) . "\">{$v[$aInfRazd['menu']]}</a></li>";
			echo "<li class=\"m l\"><a class=\"local\" href=\"../forum\">$forum</a></li>";
			if ($isAll)
			{
//				echo "<li class=\"m l\">{$dnLoad}��������</a></li>";
				echo "<li class=\"m l\"><a class=\"local\" href=\"" . goTo('index', '#refDN') . "\">$sDownload</a></li>";
			}
		?>
		</ul>
	</td>
</tr>
<tr>
	<td class="m" height="10px" style="background-color: transparent; border: 0px; 
		background-image: url(image/expand.gif);
		background-repeat: repeat-x; style: padding: 0px;
		">
	</td>
</tr>
<tr>
	<td class="m" width="190px" style="vertical-align: top; padding: 4px;">
		<INPUT id="id-PHP" class="radio" type="radio" name="impl" value="PHP" <?=checkImpl('PHP')?> 
			onChange="javascript:wOpen(this)"> <span class="rtext">PHP</span><BR>
		<INPUT id="id-Java" class="radio" type="radio" name="impl" value="Java" <?=checkImpl('Java')?> 
			onChange="javascript:wOpen(this)"> <span class="rtext">Java</span><BR>

		<ul style="color: black; padding: 3px;">
		<?
			foreach($a_ch as $k=>$v)
				echo "<li class=\"m h\"><a class=\"local\" href=\"" . goTo($k) . "\">{$v[$aInfRazd['menu']]}</a></li>";
			echo "<li class=\"m h\"><a class=\"local\" href=\"../forum\">$forum</a></li>";
			if ($isAll)
			{
				echo "<li class=\"m h\" style=\"margin-top: 20px;\">{$mail}{$sMail}</a></li>";
				echo "<li class=\"m h\" style=\"margin-top: 20px;\"><a class=\"local\" href=\"../Compilao/application.html\">Compilao</a></li>";
			}
		?>
		</ul>
<div style="margin-top:30px; align: center;">
<!--LiveInternet counter--><script type="text/javascript"><!--
document.write("<a href='http://www.liveinternet.ru/click' "+
"target=_blank><img src='http://counter.yadro.ru/hit?t20.10;r"+
escape(document.referrer)+((typeof(screen)=="undefined")?"":
";s"+screen.width+"*"+screen.height+"*"+(screen.colorDepth?
screen.colorDepth:screen.pixelDepth))+";u"+escape(document.URL)+
";"+Math.random()+
"' alt='' title='LiveInternet: �������� ����� ���������� �� 24"+
" ����, ����������� �� 24 ���� � �� �������' "+
"border=0 width=88 height=31><\/a>")//--></script><!--/LiveInternet-->
<!--div style="width:88px; height:31px; background-color:white;"/-->
</div>
	</td>
	<td class="m" width="6px;" colspan="2" style="background-color: transparent; border: 0px; 
		background-image: url(image/ns-expand.gif);
		background-repeat: repeat-y;
		">
	</td>
	<td class="m" colspan="2" style="overflow: scroll; background-color: LightYellow; padding: 10px 20px; color: DarkBlue;
		font-family: Lucida; font-weight: 400;">
		<?=$a_ch[$ch][$aInfRazd['cont']]?>
	</td>
</tr>
</table>

<script>
var docUrl = '<?=goTo('index')?>';
function wOpen(obj) {
	window.open(document.location.href.replace(/index.*/, '') + docUrl.replace(/&impl[^&]*/, '') + "&impl=" + obj.value, "_self")
}

var pad = 0;
var oTab = document.getElementById("tabInf");
var tim;
function cPad()
{
	pad += 1;
	if ( pad > 11 )
		pad = 0;
	oTab.rows[2].cells[0].style.backgroundPosition = pad + "px 0px";
	oTab.rows[3].cells[1].style.backgroundPosition = "0px " + pad + "px";
	clearTimeout(tim);
	tim = setTimeout(cPad, 100);
}
//if ( !window.ActiveXObject )
//	setInterval(cPad, 100);
tim = setTimeout(cPad, 100);

</script>
</body>
</html>
