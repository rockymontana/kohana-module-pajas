<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template name="load_content">
		<xsl:param name="field_id" />
		<xsl:apply-templates select="/root/content/page/type[@template_field_id = $field_id]/contents/content[1]/html" mode="elements" />
	</xsl:template>

	<xsl:template match="h1" mode="elements">
		<h1>
			<xsl:copy-of select="@class" />
			<xsl:apply-templates select="node()" mode="elements" />
		</h1>
	</xsl:template>

	<xsl:template match="h2" mode="elements">
		<h2>
			<xsl:copy-of select="@class" />
			<xsl:apply-templates select="node()" mode="elements" />
		</h2>
	</xsl:template>

	<xsl:template match="h3" mode="elements">
		<h3>
			<xsl:copy-of select="@class" />
			<xsl:apply-templates select="node()" mode="elements" />
		</h3>
	</xsl:template>

	<xsl:template match="p" mode="elements">
		<p>
			<xsl:copy-of select="@class" />
			<xsl:apply-templates select="node()" mode="elements" />
		</p>
	</xsl:template>

	<xsl:template match="ul" mode="elements">
		<ul>
			<xsl:copy-of select="@class" />
			<xsl:apply-templates select="node()" mode="elements" />
		</ul>
	</xsl:template>

	<xsl:template match="ol" mode="elements">
		<ol>
			<xsl:copy-of select="@class" />
			<xsl:apply-templates select="node()" mode="elements" />
		</ol>
	</xsl:template>

	<xsl:template match="li" mode="elements">
		<li>
			<xsl:copy-of select="@class" />
			<xsl:apply-templates select="node()" mode="elements" />
		</li>
	</xsl:template>

	<xsl:template match="strong" mode="elements">
		<strong>
			<xsl:copy-of select="@class" />
			<xsl:apply-templates select="node()" mode="elements" />
		</strong>
	</xsl:template>

	<xsl:template match="em" mode="elements">
		<em>
			<xsl:copy-of select="@class" />
			<xsl:apply-templates select="node()" mode="elements" />
		</em>
	</xsl:template>

	<xsl:template match="span" mode="elements">
		<span>
			<xsl:copy-of select="@class" />
			<xsl:apply-templates select="node()" mode="elements" />
		</span>
	</xsl:template>

	<xsl:template match="sup" mode="elements">
		<sup><xsl:apply-templates select="node()" mode="elements" /></sup>
	</xsl:template>

	<xsl:template match="sub" mode="elements">
		<sub><xsl:apply-templates select="node()" mode="elements" /></sub>
	</xsl:template>

	<xsl:template match="br" mode="elements">
		<br/>
	</xsl:template>

	<xsl:template match="a" mode="elements">
		<a>
			<xsl:copy-of select="@*" />
			<xsl:apply-templates select="node()" mode="elements" />
		</a>
	</xsl:template>

	<xsl:template match="img" mode="elements">
		<img>
			<xsl:copy-of select="@*" />
			<xsl:apply-templates select="node()" mode="elements" />
		</img>
	</xsl:template>

	<xsl:template match="code" mode="elements">
		<code>
			<xsl:copy-of select="@class" />
			<xsl:apply-templates select="node()" mode="elements" />
		</code>
	</xsl:template>

	<xsl:template match="pre" mode="elements">
		<pre><xsl:apply-templates select="node()" mode="elements" /></pre>
	</xsl:template>

	<!-- Table stuff -->
	<xsl:template match="table" mode="elements">
		<table><xsl:apply-templates select="node()" mode="elements" /></table>
	</xsl:template>
	<xsl:template match="thead" mode="elements">
		<thead><xsl:apply-templates select="node()" mode="elements" /></thead>
	</xsl:template>
	<xsl:template match="tbody" mode="elements">
		<tbody><xsl:apply-templates select="node()" mode="elements" /></tbody>
	</xsl:template>
	<xsl:template match="tfooter" mode="elements">
		<tfooter><xsl:apply-templates select="node()" mode="elements" /></tfooter>
	</xsl:template>
	<xsl:template match="tr" mode="elements">
		<tr><xsl:apply-templates select="node()" mode="elements" /></tr>
	</xsl:template>
	<xsl:template match="th" mode="elements">
		<th><xsl:apply-templates select="node()" mode="elements" /></th>
	</xsl:template>
	<xsl:template match="td" mode="elements">
		<td><xsl:apply-templates select="node()" mode="elements" /></td>
	</xsl:template>

	<!--xsl:template match="flash" mode="elements">
		<object type="application/x-shockwave-flash" data="{@src}" class="flash">
			<xsl:copy-of select="@width|@height"/>
			<xsl:if test="@params != ''">
				<xsl:attribute name="data"><xsl:value-of select="@src"/>?<xsl:value-of select="@params"/></xsl:attribute>
			</xsl:if>
			<xsl:attribute name="style">
				<xsl:if test="@width	!= ''">width: <xsl:value-of select="@width" />px;</xsl:if>
				<xsl:if test="@height	!= ''">height: <xsl:value-of select="@height" />px;</xsl:if>
			</xsl:attribute>
			<param name="movie" value="{@src}">
				<xsl:if test="@params != ''">
					<xsl:attribute name="value"><xsl:value-of select="@src"/>?<xsl:value-of select="@params"/></xsl:attribute>
				</xsl:if>
			</param>
		</object>
	</xsl:template-->

</xsl:stylesheet>
