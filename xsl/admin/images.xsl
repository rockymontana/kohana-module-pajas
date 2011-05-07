<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:include href="tpl.default.xsl" />

	<xsl:template name="tabs">
		<ul class="tabs">

			<xsl:call-template name="tab">
				<xsl:with-param name="href"   select="'images'" />
				<xsl:with-param name="text"   select="'List images'" />
			</xsl:call-template>

			<xsl:call-template name="tab">
				<xsl:with-param name="href"   select="'images/add_image'" />
				<xsl:with-param name="action" select="'add_image'" />
				<xsl:with-param name="text"   select="'Add image'" />
			</xsl:call-template>

		</ul>
	</xsl:template>

	<xsl:template match="/">
		<xsl:if test="/root/meta/action = 'index'">
			<xsl:call-template name="template">
				<xsl:with-param name="title" select="'Admin - Images'" />
				<xsl:with-param name="h1" select="'List images'" />
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="/root/meta/action = 'add_image'">
			<xsl:call-template name="template">
				<xsl:with-param name="title" select="'Admin - Images'" />
				<xsl:with-param name="h1" select="'Add images'" />
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="/root/meta/action = 'edit_page'">
			<xsl:call-template name="template">
				<xsl:with-param name="title" select="'Admin - Images'" />
				<xsl:with-param name="h1" select="'Edit images'" />
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

	<!-- List images -->
	<xsl:template match="content[../meta/controller = 'images' and ../meta/action = 'index']">
		<table>
			<thead>
				<tr>
					<th class="large_row"></th>
					<th class="large_row">Name</th>
					<th class="medium_row">Dimensions</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<xsl:for-each select="/root/content/images/image">
					<xsl:sort select="@name" />
					<tr>
						<xsl:if test="position() mod 2 = 1">
							<xsl:attribute name="class">odd</xsl:attribute>
						</xsl:if>
						<td><a href="../{URL}"><img src="../{URL}?width=250" alt="{@name}" /></a></td>
						<td><xsl:value-of select="@name" /></td>
						<td><xsl:value-of select="width" />x<xsl:value-of select="height" /></td>
						<td></td>
					</tr>
				</xsl:for-each>
			</tbody>
		</table>
	</xsl:template>

	<!-- Add or edit an image -->
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
		<option value="{$i}">
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
