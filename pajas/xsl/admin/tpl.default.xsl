<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <!-- INCLUDES -->
  <!--xsl:include href="inc.elements.xsl" /-->
  <xsl:include href="inc.common.xsl" />

	<xsl:output method="html" encoding="utf-8" />

	<xsl:key name="nav_categories" match="/root/content/menuoptions/menuoption" use="@category" />

  <!-- TEMPLATE -->
  <xsl:template name="template">
    <html>
      <head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<link type="text/css" href="{/root/meta/base}css/admin/style.css" rel="stylesheet" media="all" />

				<base href="http://{root/meta/domain}{/root/meta/base}admin/" />

				<xsl:call-template name="title" />
      </head>
      <body>
      	<xsl:for-each select="/root/content/errors/error">
      		<div class="error"><xsl:value-of select="." /></div>
      	</xsl:for-each>
      	<xsl:for-each select="/root/content/messages/message">
      		<div class="message"><xsl:value-of select="." /></div>
      	</xsl:for-each>
        <xsl:apply-templates select="/root/content" />
      	<nav>

					<xsl:for-each select="/root/content/menuoptions/menuoption">
						<xsl:sort select="@category" />
						<xsl:if test="generate-id() = generate-id(key('nav_categories',@category))">
							<xsl:if test="@category != ''">
								<p><xsl:value-of select="@category" /></p>
							</xsl:if>
							<xsl:call-template name="menuoptions">
								<xsl:with-param name="cat_name" select="@category" />
							</xsl:call-template>
						</xsl:if>
					</xsl:for-each>
					<xsl:if test="/root/meta/user_data">
						<a href="logout">Logout</a>
					</xsl:if>
      	</nav>
      </body>
    </html>
  </xsl:template>

  <xsl:template name="menuoptions">
  	<xsl:param name="cat_name" />
  	<div>
  		<xsl:if test="$cat_name = ''">
  			<xsl:attribute name="class">biglinks</xsl:attribute>
  		</xsl:if>
			<xsl:for-each select="/root/content/menuoptions/menuoption">
				<xsl:sort select="position" />
				<xsl:if test="@category = $cat_name">
					<a href="{href}">
						<xsl:if test="/root/meta/admin_page = name">
							<xsl:attribute name="class">active</xsl:attribute>
						</xsl:if>
						<xsl:if test="not(/root/meta/admin_page)">
							<xsl:if test="concat('admin/',href) = /root/meta/path or (href = '' and /root/meta/path = 'admin')">
								<xsl:attribute name="class">active</xsl:attribute>
							</xsl:if>
						</xsl:if>
						<xsl:value-of select="name" />
					</a>
				</xsl:if>
			</xsl:for-each>
		</div>
  </xsl:template>

</xsl:stylesheet>
