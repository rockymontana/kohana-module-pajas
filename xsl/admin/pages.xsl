<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:include href="tpl.default.xsl" />

	<xsl:template name="tabs">
		<ul class="tabs">
			<li>
				<a href="pages">
					<xsl:if test="/root/meta/action = 'index'">
						<xsl:attribute name="class">selected</xsl:attribute>
					</xsl:if>
					<xsl:text>List pages</xsl:text>
				</a>
			</li>
			<li>
				<a href="pages/add_page">
					<xsl:if test="/root/meta/action = 'add_page'">
						<xsl:attribute name="class">selected</xsl:attribute>
					</xsl:if>
					<xsl:text>Add page</xsl:text>
				</a>
			</li>
		</ul>
	</xsl:template>


  <xsl:template match="/">
  	<xsl:if test="/root/meta/action = 'index'">
  		<xsl:call-template name="template">
		  	<xsl:with-param name="title" select="'Admin - Pages'" />
		  	<xsl:with-param name="h1" select="'List pages'" />
  		</xsl:call-template>
  	</xsl:if>
  	<xsl:if test="/root/meta/action = 'add_page'">
  		<xsl:call-template name="template">
		  	<xsl:with-param name="title" select="'Admin - Pages'" />
		  	<xsl:with-param name="h1" select="'Add page'" />
  		</xsl:call-template>
  	</xsl:if>
  	<xsl:if test="/root/meta/action = 'edit_page'">
  		<xsl:call-template name="template">
		  	<xsl:with-param name="title" select="'Admin - Pages'" />
		  	<xsl:with-param name="h1" select="'Edit page'" />
  		</xsl:call-template>
  	</xsl:if>
  </xsl:template>

	<!-- List pages -->
  <xsl:template match="content[../meta/controller = 'pages' and ../meta/action = 'index']">
		<table>
			<thead>
				<tr>
					<th>Name</th>
					<th>URI</th>
					<th class="medium_row">Action</th>
				</tr>
			</thead>
			<tbody>
				<xsl:for-each select="pages/page">
					<tr>
						<xsl:if test="position() mod 2 = 1">
							<xsl:attribute name="class">odd</xsl:attribute>
						</xsl:if>
						<td><xsl:value-of select="name" /></td>
						<td><xsl:value-of select="URI" /></td>
						<td>
							[<a>
							<xsl:attribute name="href">
								<xsl:text>pages/edit_page/</xsl:text>
								<xsl:value-of select="@id" />
							</xsl:attribute>
							<xsl:text>Edit</xsl:text>
							</a>]
							[<a>
							<xsl:attribute name="href">
								<xsl:text>pages/rm_page/</xsl:text>
								<xsl:value-of select="@id" />
							</xsl:attribute>
							<xsl:text>Delete</xsl:text>
							</a>]
						</td>
					</tr>
				</xsl:for-each>
			</tbody>
		</table>
  </xsl:template>

	<!-- Add page -->
  <xsl:template match="content[../meta/controller = 'pages' and ../meta/action = 'add_page']">
  	<form method="post" action="pages/add_page">

			<h2>Page data</h2>

			<!-- Name -->
			<xsl:if test="/root/content/errors/form_errors/name = 'not_empty'">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'name'" />
					<xsl:with-param name="label" select="'Page name:'" />
					<xsl:with-param name="error" select="'Page name is required'" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="/root/content/errors/form_errors/name = 'Content_Page::page_name_available'">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'name'" />
					<xsl:with-param name="label" select="'Page name:'" />
					<xsl:with-param name="error" select="'This page name is already taken'" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="not(/root/content/errors/form_errors/name)">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'name'" />
					<xsl:with-param name="label" select="'Page name:'" />
				</xsl:call-template>
			</xsl:if>

			<!-- URI -->
			<xsl:if test="/root/content/errors/form_errors/URI">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'URI'" />
					<xsl:with-param name="label" select="'Page URI:'" />
					<xsl:with-param name="error" select="'This URI is already taken'" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="not(/root/content/errors/form_errors/URI)">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'URI'" />
					<xsl:with-param name="label" select="'Page URI:'" />
				</xsl:call-template>
			</xsl:if>

			<h2>Content types</h2>
			<xsl:for-each select="types/type">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="concat('type_',@id)" />
					<xsl:with-param name="label" select="concat(name,':')" />
					<xsl:with-param name="type" select="'checkbox'" />
				</xsl:call-template>
			</xsl:for-each>

			<xsl:call-template name="form_button">
				<xsl:with-param name="value" select="'Add page'" />
			</xsl:call-template>
  	</form>
  </xsl:template>

	<!-- Edit page -->
  <xsl:template match="content[../meta/controller = 'pages' and ../meta/action = 'edit_page']">
  	<form method="post" action="pages/edit_page/{page/@id}">

			<h2>Page data</h2>

			<!-- Page ID -->
			<xsl:call-template name="form_line">
				<xsl:with-param name="type" select="'none'" />
				<xsl:with-param name="label" select="'Page ID:'" />
				<xsl:with-param name="value" select="page/@id" />
			</xsl:call-template>

			<!-- Name -->
			<xsl:if test="/root/content/errors/form_errors/name = 'not_empty'">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'name'" />
					<xsl:with-param name="label" select="'Page name:'" />
					<xsl:with-param name="error" select="'Page name is required'" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="/root/content/errors/form_errors/name = 'Content_Page::page_name_available'">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'name'" />
					<xsl:with-param name="label" select="'Page name:'" />
					<xsl:with-param name="error" select="'This page name is already taken'" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="not(/root/content/errors/form_errors/name)">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'name'" />
					<xsl:with-param name="label" select="'Page name:'" />
				</xsl:call-template>
			</xsl:if>

			<!-- URI -->
			<xsl:if test="/root/content/errors/form_errors/URI">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'URI'" />
					<xsl:with-param name="label" select="'Page URI:'" />
					<xsl:with-param name="error" select="'This URI is already taken'" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="not(/root/content/errors/form_errors/URI)">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'URI'" />
					<xsl:with-param name="label" select="'Page URI:'" />
				</xsl:call-template>
			</xsl:if>

			<h2>Content types</h2>
			<xsl:for-each select="types/type">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="concat('type_',@id)" />
					<xsl:with-param name="label" select="concat(name,':')" />
					<xsl:with-param name="type" select="'checkbox'" />
				</xsl:call-template>
			</xsl:for-each>

			<xsl:call-template name="form_button">
				<xsl:with-param name="value" select="'Save changes'" />
			</xsl:call-template>
  	</form>
  </xsl:template>

</xsl:stylesheet>
