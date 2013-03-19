<xsl:stylesheet version="1.0"
					xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
					xmlns:fo="http://www.w3.org/1999/XSL/Format"
					xmlns="urn:schemas-microsoft-com:office:spreadsheet" 
					xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" 
>
<xsl:output method="xml" omit-xml-declaration="no" media-type="application/vnd.oasis.opendocument.spreadsheet"/>

<!--xsl:output method="xml" media-type="text/xls"/-->
<!--xsl:output method="text"  media-type="text/plain"/-->
<!--xsl:output method="html"  media-type="text/plain" standalone="yes"/-->

<xsl:template match="/REPORT">
	<!--xsl:text disable-output-escaping="yes">
		&lt;?xml version="1.0" encoding="UTF-8"?&gt;
	</xsl:text-->

	<Workbook 
	xmlns="urn:schemas-microsoft-com:office:spreadsheet" 
	xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" 
	>
	
	<Styles>
		<Style ss:ID="Default" ss:Name="Default">
			<Alignment ss:Vertical="Top"/>
		</Style>
		<Style ss:ID="ta1"/>
		<xsl:apply-templates select="STYLES"/>
	</Styles>

	<ss:Worksheet ss:Name="List1">
	<Table ss:StyleID="ta1">
		<xsl:apply-templates select="COLUMNS"/>
		<xsl:apply-templates select="R"/>
	</Table>
	</ss:Worksheet>
	
	<!--ss:Worksheet ss:Name="Лист2">
	</ss:Worksheet-->
	
	</Workbook>

</xsl:template>

<xsl:variable name="blank">
	<xsl:text> </xsl:text>
</xsl:variable>
<xsl:variable name="nms">
	<xsl:text>ss:</xsl:text>
</xsl:variable>
<xsl:variable name="head">
	<xsl:text> ColCaption </xsl:text>
</xsl:variable>
<xsl:variable name="group">
	<xsl:text> RowGrpHead</xsl:text>
</xsl:variable>
<xsl:variable name="bold">
	<xsl:text> b </xsl:text>
</xsl:variable>
<xsl:variable name="center">
	<xsl:text> c </xsl:text>
</xsl:variable>
<xsl:variable name="right">
	<xsl:text> r </xsl:text>
</xsl:variable>
<xsl:variable name="number">
	<xsl:text> r </xsl:text>
</xsl:variable>
<xsl:variable name="noWrap">
	<xsl:text> nw </xsl:text>
</xsl:variable>

<!--Alignment ss:Vertical="Top" ss:WrapText="1"/-->
<xsl:template match="STYLE">
	<xsl:element name="Style">
		<xsl:attribute name="ss:ID">
			<xsl:value-of select="@name"/>
		</xsl:attribute>
	<xsl:variable name="nmStyle">
		<xsl:text> </xsl:text><xsl:value-of select="@name"/><xsl:text> </xsl:text>
	</xsl:variable>
	<xsl:if test="contains($nmStyle, $head) or contains($nmStyle, $center) or contains($nmStyle, $number)">
		<xsl:element name="Alignment">
			<xsl:attribute name="ss:Horizontal">Center</xsl:attribute>
		</xsl:element>
	</xsl:if>
	<xsl:if test="contains($nmStyle, $head) or contains($nmStyle, $bold) or contains($nmStyle, $group)">
		<xsl:element name="Font">
			<xsl:attribute name="ss:Bold">1</xsl:attribute>
		</xsl:element>
	</xsl:if>
	<Borders>
			<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#000000"/>
			<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#000000"/>
			<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#000000"/>
			<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#000000"/>
	</Borders>
	</xsl:element>
</xsl:template>

<xsl:template match="Column">
	<Column ss:Width="{@Width}"/>
</xsl:template>

<xsl:template match="R">
	<Row>
	    <xsl:apply-templates/>
	</Row>
</xsl:template>

<xsl:template match="C">
	<xsl:element name="Cell">
		<xsl:attribute name="ss:StyleID">
			<xsl:value-of select="../@class"/>
		</xsl:attribute>
		<xsl:if test="@colspan">
			<xsl:attribute name="ss:MergeAcross">
				<xsl:value-of select="(number(@colspan)-1)"/>
			</xsl:attribute>
		</xsl:if>
		<xsl:if test="@rowspan">
			<xsl:attribute name="ss:MergeDown">
				<xsl:value-of select="(number(@rowspan)-1)"/>
			</xsl:attribute>
		</xsl:if>
		<xsl:if test="@index">
			<xsl:attribute name="ss:Index">
				<xsl:value-of select="@index"/>
			</xsl:attribute>
		</xsl:if>
		<xsl:element name="Data">
			<xsl:attribute name="ss:Type">
				<xsl:choose>
					<xsl:when test="contains(concat(@class, $blank), $number)">Number</xsl:when>
					<xsl:otherwise>String</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:element>
</xsl:template>

</xsl:stylesheet>
