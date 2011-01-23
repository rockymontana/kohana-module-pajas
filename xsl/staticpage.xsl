<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="html" encoding="utf-8" />
	<xsl:include href="inc.elements.xsl" />


	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<link type="text/css" href="css/style.css" rel="stylesheet" media="all" />

				<base href="http://{root/meta/domain}{root/meta/base}" />

				<title>Pajas</title>
			</head>
			<body>
				<div class="main_container">
					<xsl:if test="/root/content/h1">
						<h1><xsl:value-of select="/root/content/h1" /></h1>
					</xsl:if>

					<xsl:apply-templates select="/root/content/page/html_content" mode="elements" />

				</div>
				<footer>
					<p>Generated in <xsl:value-of select="round(root/meta/benchmark/current/time * 1000)" /> ms using

					<xsl:choose>
						<xsl:when test="round(root/meta/benchmark/current/memory div 1024) &lt; 1"><xsl:value-of select="root/meta/benchmark/current/memory" /> Bytes</xsl:when>
						<xsl:when test="round(root/meta/benchmark/current/memory div (1024 *1024)) &lt; 1"><xsl:value-of select="round(root/meta/benchmark/current/memory div 1024)" /> kb</xsl:when>
						<xsl:otherwise><xsl:value-of select="format-number(root/meta/benchmark/current/memory div (1024 * 1024),'#.##')" /> Mb</xsl:otherwise>
					</xsl:choose>

					of memory.</p>
				</footer>
				<a href="http://kohanaframework.org" id="kohana_logo"><img src="img/kohana.png" alt="Kohana" /></a>
			</body>
		</html>
	</xsl:template>

</xsl:stylesheet>
