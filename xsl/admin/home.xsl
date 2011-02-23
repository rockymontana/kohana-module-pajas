<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:include href="tpl.default.xsl" />

	<xsl:template match="/">
		<xsl:call-template name="template">
			<xsl:with-param name="title" select="'Admin - Home'" />
			<xsl:with-param name="h1" select="'Administration system'" />
		</xsl:call-template>
	</xsl:template>

	<xsl:key name="nav_categories" match="/root/content/menuoptions/menuoption" use="@category" />

	<xsl:template name="tabs">
		<!--ul class="tabs">
			<li><a href="#" class="selected">fields</a></li>
			<li><a href="#">korv</a></li>
		</ul-->
	</xsl:template>

	<xsl:template match="content">
		<xsl:for-each select="/root/content/menuoptions/menuoption">
			<xsl:sort select="@category" />
			<xsl:if test="generate-id() = generate-id(key('nav_categories',@category))">
				<xsl:if test="@category != ''">
					<h2><xsl:value-of select="@category" /></h2>
				</xsl:if>
				<xsl:call-template name="menuoptions_descriptions">
					<xsl:with-param name="cat_name" select="@category" />
				</xsl:call-template>
			</xsl:if>
		</xsl:for-each>
	</xsl:template>

	<xsl:template name="menuoptions_descriptions">
		<xsl:param name="cat_name" />
		<xsl:for-each select="/root/content/menuoptions/menuoption">
			<xsl:sort select="position" />
			<xsl:if test="@category = $cat_name">
				<p><a href="{href}"><xsl:value-of select="name" /></a> - <xsl:value-of select="description" /></p>
			</xsl:if>
		</xsl:for-each>
	</xsl:template>

</xsl:stylesheet>
