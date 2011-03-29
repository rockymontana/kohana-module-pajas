<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:include href="tpl.default.xsl" />

	<xsl:template name="tabs">
		<ul class="tabs">

			<xsl:call-template name="tab">
				<xsl:with-param name="href"   select="'pages'" />
				<xsl:with-param name="text"   select="'List pages'" />
			</xsl:call-template>

			<xsl:call-template name="tab">
				<xsl:with-param name="href"   select="'pages/add_page'" />
				<xsl:with-param name="action" select="'add_page'" />
				<xsl:with-param name="text"   select="'Add page'" />
			</xsl:call-template>

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

	<!-- Add or edit a page -->
	<xsl:template match="content[../meta/controller = 'pages' and (../meta/action = 'add_page' or ../meta/action = 'edit_page')]">
		<form method="post">

			<xsl:if test="../meta/action = 'add_page'">
				<xsl:attribute name="action">pages/add_page</xsl:attribute>
			</xsl:if>
			<xsl:if test="../meta/action = 'edit_page'">
				<xsl:attribute name="action">pages/edit_page/<xsl:value-of select="page/@id" /></xsl:attribute>
			</xsl:if>

			<h2>Page data</h2>

			<xsl:if test="../meta/action = 'edit_page'">
				<!-- Page ID -->
				<xsl:call-template name="form_line">
					<xsl:with-param name="type" select="'none'" />
					<xsl:with-param name="label" select="'Page ID:'" />
					<xsl:with-param name="value" select="page/@id" />
				</xsl:call-template>
			</xsl:if>

			<!-- Name -->
			<xsl:if test="errors/form_errors/name = 'Valid::not_empty'">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'name'" />
					<xsl:with-param name="label" select="'Page name:'" />
					<xsl:with-param name="error" select="'Page name is required'" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="errors/form_errors/name = 'Content_Page::page_name_available'">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'name'" />
					<xsl:with-param name="label" select="'Page name:'" />
					<xsl:with-param name="error" select="'This page name is already taken'" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="not(errors/form_errors/name)">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'name'" />
					<xsl:with-param name="label" select="'Page name:'" />
				</xsl:call-template>
			</xsl:if>

			<!-- URI -->
			<xsl:if test="errors/form_errors/URI">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'URI'" />
					<xsl:with-param name="label" select="'Page URI:'" />
					<xsl:with-param name="error" select="'This URI is already taken'" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="not(errors/form_errors/URI)">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'URI'" />
					<xsl:with-param name="label" select="'Page URI:'" />
				</xsl:call-template>
			</xsl:if>

			<p>If you dont know what URI is, leave it blank</p>

			<h2>Content types</h2>
			<p>Use the dropdown to specify where the content is to show up on the page</p>
			<xsl:for-each select="types/type">
				<label class="content_types">
					<xsl:attribute name="for"><xsl:value-of select="concat('type_',@id)" /></xsl:attribute>
					<xsl:value-of select="concat(name,':')" />
					<select>
						<xsl:attribute name="id">
							<xsl:value-of select="concat('template_for_type_', current()/@id)" />
						</xsl:attribute>
						<xsl:attribute name="name">
							<xsl:value-of select="concat('template_for_type_', current()/@id)" />
						</xsl:attribute>
						<xsl:call-template name="template_for_type" />
					</select>
					<input type="checkbox">
						<xsl:attribute name="id">  <xsl:value-of select="concat('type_',@id)" /></xsl:attribute>
						<xsl:attribute name="name"><xsl:value-of select="concat('type_',@id)" /></xsl:attribute>
						<xsl:if test="/root/content/formdata/field[@id = concat('type_',current()/@id)]">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:if>
					</input>
				</label>

				<!--xsl:call-template name="form_line">
					<xsl:with-param name="id" select="concat('type_',@id)" />
					<xsl:with-param name="label" select="concat(name,':')" />
					<xsl:with-param name="type" select="'checkbox'" />
				</xsl:call-template>

				<label>
					<xsl:attribute name="for">
						<xsl:value-of select="concat('template_for_type_', current()/@id)" />
					</xsl:attribute>
					<xsl:text>Template place:</xsl:text>
					<select>
						<xsl:attribute name="id">
							<xsl:value-of select="concat('template_for_type_', current()/@id)" />
						</xsl:attribute>
						<xsl:attribute name="name">
							<xsl:value-of select="concat('template_for_type_', current()/@id)" />
						</xsl:attribute>
						<xsl:call-template name="template_for_type" />
					</select>
				</label>
				<p class="error"> </p-->
			</xsl:for-each>

			<xsl:if test="../meta/action = 'add_page'">
				<xsl:call-template name="form_button">
					<xsl:with-param name="value" select="'Add page'" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="../meta/action = 'edit_page'">
				<xsl:call-template name="form_button">
					<xsl:with-param name="value" select="'Save changes'" />
				</xsl:call-template>
			</xsl:if>
		</form>
	</xsl:template>

	<xsl:template name="template_for_type">
		<xsl:param name="i">1</xsl:param>
		<option value="$i">
			<xsl:if test="/root/content/formdata/field[@id = concat('template_for_type_', current()/@id)] = $i">
				<xsl:attribute name="selected">selected</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="$i" />
		</option>
		<xsl:if test="$i &lt; 15">
			<xsl:call-template name="template_for_type">
				<xsl:with-param name="i" select="number($i)+1" />
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>
