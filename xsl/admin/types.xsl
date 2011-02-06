<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:include href="tpl.default.xsl" />

	<xsl:template name="tabs">
		<ul class="tabs">
			<li>
				<a href="types">
					<xsl:if test="/root/meta/action = 'index'">
						<xsl:attribute name="class">selected</xsl:attribute>
					</xsl:if>
					<xsl:text>List content types</xsl:text>
				</a>
			</li>
			<li>
				<a href="types/add_type">
					<xsl:if test="/root/meta/action = 'add_type'">
						<xsl:attribute name="class">selected</xsl:attribute>
					</xsl:if>
					<xsl:text>Add content type</xsl:text>
				</a>
			</li>
		</ul>
	</xsl:template>


  <xsl:template match="/">
  	<xsl:if test="/root/meta/action = 'index'">
  		<xsl:call-template name="template">
		  	<xsl:with-param name="title" select="'Admin - Content Types'" />
		  	<xsl:with-param name="h1" select="'List content types'" />
  		</xsl:call-template>
  	</xsl:if>
  	<xsl:if test="/root/meta/action = 'add_type'">
  		<xsl:call-template name="template">
		  	<xsl:with-param name="title" select="'Admin - Content Types'" />
		  	<xsl:with-param name="h1" select="'Add content type'" />
  		</xsl:call-template>
  	</xsl:if>
  	<xsl:if test="/root/meta/action = 'edit_type'">
  		<xsl:call-template name="template">
		  	<xsl:with-param name="title" select="'Admin - Content Types'" />
		  	<xsl:with-param name="h1" select="'Edit content type'" />
  		</xsl:call-template>
  	</xsl:if>
  </xsl:template>

	<!-- List types -->
  <xsl:template match="content[../meta/controller = 'types' and ../meta/action = 'index']">
		<table>
			<thead>
				<tr>
					<th>Name</th>
					<th>Description</th>
					<th class="medium_row">Action</th>
				</tr>
			</thead>
			<tbody>
				<xsl:for-each select="types/type">
					<tr>
						<xsl:if test="position() mod 2 = 1">
							<xsl:attribute name="class">odd</xsl:attribute>
						</xsl:if>
						<td><xsl:value-of select="name" /></td>
						<td><xsl:value-of select="description" /></td>
						<td>
							[<a>
							<xsl:attribute name="href">
								<xsl:text>types/edit_type/</xsl:text>
								<xsl:value-of select="@id" />
							</xsl:attribute>
							<xsl:text>Edit</xsl:text>
							</a>]
							[<a>
							<xsl:attribute name="href">
								<xsl:text>types/rm_type/</xsl:text>
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

	<!-- Add type -->
  <xsl:template match="content[../meta/controller = 'types' and ../meta/action = 'add_type']">
  	<form method="post" action="types/add_type">

			<h2>Content type data</h2>

			<!-- Name -->
			<xsl:if test="/root/content/errors/form_errors/name = 'not_empty'">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'name'" />
					<xsl:with-param name="label" select="'Content type name:'" />
					<xsl:with-param name="error" select="'Content type name is required'" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="/root/content/errors/form_errors/name = 'Content_Type::type_name_available'">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'name'" />
					<xsl:with-param name="label" select="'Content type name:'" />
					<xsl:with-param name="error" select="'This content type name is already taken'" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="not(/root/content/errors/form_errors/name)">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'name'" />
					<xsl:with-param name="label" select="'Content type name:'" />
				</xsl:call-template>
			</xsl:if>

			<xsl:call-template name="form_line">
				<xsl:with-param name="id" select="'description'" />
				<xsl:with-param name="label" select="'Content type description:'" />
			</xsl:call-template>

			<xsl:call-template name="form_button">
				<xsl:with-param name="value" select="'Add content type'" />
			</xsl:call-template>
  	</form>
  </xsl:template>

	<!-- Edit type -->
  <xsl:template match="content[../meta/controller = 'types' and ../meta/action = 'edit_type']">
  	<form method="post" action="types/edit_type/{type_data/@id}">

			<h2>Content type data</h2>

			<!-- Type ID -->
			<xsl:call-template name="form_line">
				<xsl:with-param name="type" select="'none'" />
				<xsl:with-param name="label" select="'Content type ID:'" />
				<xsl:with-param name="value" select="type_data/@id" />
			</xsl:call-template>

			<!-- Name -->
			<xsl:if test="/root/content/errors/form_errors/name = 'not_empty'">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'name'" />
					<xsl:with-param name="label" select="'Content type name:'" />
					<xsl:with-param name="error" select="'Content type name is required'" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="/root/content/errors/form_errors/name = 'Content_Type::type_name_available'">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'name'" />
					<xsl:with-param name="label" select="'Content type name:'" />
					<xsl:with-param name="error" select="'This content type name is already taken'" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="not(/root/content/errors/form_errors/name)">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'name'" />
					<xsl:with-param name="label" select="'Content type name:'" />
				</xsl:call-template>
			</xsl:if>

			<!-- Description -->
			<xsl:call-template name="form_line">
				<xsl:with-param name="id" select="'description'" />
				<xsl:with-param name="label" select="'Content type description:'" />
			</xsl:call-template>

			<xsl:call-template name="form_button">
				<xsl:with-param name="value" select="'Save changes'" />
			</xsl:call-template>
  	</form>
  </xsl:template>

</xsl:stylesheet>
