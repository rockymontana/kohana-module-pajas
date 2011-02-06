<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output
		method="html"
		encoding="utf-8"
		omit-xml-declaration="yes"
		doctype-public="HTML"
	/>
	<xsl:include href="inc.elements.xsl" />


	<xsl:template match="/">
		<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<link type="text/css" href="css/style.css" rel="stylesheet" media="all" />

				<base href="http://{root/meta/domain}{root/meta/base}" />

				<title>Pajas</title>
			</head>
			<body>
				<p id="header_address">Stockholm<br />Sweden</p>

				<ul id="menu">
					<li><a href="" class="active">Home</a></li>
					<li><a href="#">Portfolio</a></li>
					<li><a href="#">About</a></li>
					<li><a href="#">Contact</a></li>
				</ul>

				<div id="main_content">
					<xsl:apply-templates select="/root/content/page/type[name = 'page_welcome']/contents/content[1]/html" mode="elements" />
				</div>

				<h2>Some columns</h2>

				<ul id="columns">

					<li id="col1">
						<xsl:apply-templates select="/root/content/page/type[name = 'Puff 1']/contents/content[1]/html" mode="elements" />
					</li>

					<li id="col2">
						<xsl:apply-templates select="/root/content/page/type[name = 'Puff 2']/contents/content[1]/html" mode="elements" />
					</li>

					<li id="col3">
						<xsl:apply-templates select="/root/content/page/type[name = 'Puff 3']/contents/content[1]/html" mode="elements" />
					</li>

				</ul>

				<div id="footer">Design by <a href="http://www.megancreative.com/">Megan Sullivan</a>. Powered by <a href="http://larvit.se/pajas">Pajas</a>.</div>

				<!--footer>
					<p>Generated in <xsl:value-of select="round(root/meta/benchmark/current/time * 1000)" /> ms using

					<xsl:choose>
						<xsl:when test="round(root/meta/benchmark/current/memory div 1024) &lt; 1"><xsl:value-of select="root/meta/benchmark/current/memory" /> Bytes</xsl:when>
						<xsl:when test="round(root/meta/benchmark/current/memory div (1024 *1024)) &lt; 1"><xsl:value-of select="round(root/meta/benchmark/current/memory div 1024)" /> kb</xsl:when>
						<xsl:otherwise><xsl:value-of select="format-number(root/meta/benchmark/current/memory div (1024 * 1024),'#.##')" /> Mb</xsl:otherwise>
					</xsl:choose>

					of memory.</p>
				</footer-->
			</body>
		</html>
	</xsl:template>

</xsl:stylesheet>
