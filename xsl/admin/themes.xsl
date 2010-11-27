<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:include href="tpl.default.xsl" />

	<xsl:template name="title">
		<title>Admin - Themes</title>
	</xsl:template>

  <xsl:template match="/">
    <xsl:call-template name="template" />
  </xsl:template>

  <xsl:template match="content">
		<h1>Themes</h1>

		<p>Select themes etc</p>

  </xsl:template>


</xsl:stylesheet>
