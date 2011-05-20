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
		<xsl:if test="/root/meta/action = 'edit_image'">
			<xsl:call-template name="template">
				<xsl:with-param name="title" select="'Admin - Images'" />
				<xsl:with-param name="h1" select="'Edit image'" />
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

	<!-- List images -->
	<xsl:template match="content[../meta/controller = 'images' and ../meta/action = 'index']">
		<table>
			<thead>
				<tr>
					<th class="medium_row"></th>
					<th>Name</th>
					<th class="medium_row">Dimensions</th>
					<th class="medium_row">Action</th>
				</tr>
			</thead>
			<tbody>
				<xsl:for-each select="/root/content/images/image">
					<xsl:sort select="@name" />
					<tr>
						<xsl:if test="position() mod 2 = 1">
							<xsl:attribute name="class">odd</xsl:attribute>
						</xsl:if>
						<td><a href="../{URL}"><img src="../{URL}?width=99" alt="{@name}" /></a></td>
						<td><xsl:value-of select="name" /></td>
						<td><xsl:value-of select="width" />x<xsl:value-of select="height" /></td>
						<td>
							<xsl:text>[</xsl:text>
							<a>
							  <xsl:attribute name="href">
								  <xsl:text>images/edit_image/</xsl:text>
								  <xsl:value-of select="@name" />
							  </xsl:attribute>
							  <xsl:text>Edit</xsl:text>
							</a>
							<xsl:text>] [</xsl:text>
							<a>
							  <xsl:attribute name="href">
								  <xsl:text>images/rm_image/</xsl:text>
								  <xsl:value-of select="@name" />
							  </xsl:attribute>
							  <xsl:text>Delete</xsl:text>
							</a>
							<xsl:text>]</xsl:text>
						</td>
					</tr>
				</xsl:for-each>
			</tbody>
		</table>
	</xsl:template>

	<!-- Add or edit an image -->
	<xsl:template match="content[../meta/controller = 'images' and ../meta/action = 'add_image']">
		<p>Upload images to application/user_content/images/</p>
	</xsl:template>
	<!--xsl:template match="content[../meta/controller = 'images' and (../meta/action = 'add_image' or ../meta/action = 'edit_image')]"-->
	<xsl:template match="content[../meta/controller = 'images' and ../meta/action = 'edit_image']">
		<a href="../{image/field[@name = 'URL']}" class="column"><img src="../{image/field[@name = 'URL']}?width=300" alt="{image/name}" /></a>

		<form method="post" class="column">

			<xsl:if test="../meta/action = 'add_image'">
				<xsl:attribute name="action">images/add_image</xsl:attribute>
			</xsl:if>
			<xsl:if test="../meta/action = 'edit_image'">
				<xsl:attribute name="action">images/edit_image/<xsl:value-of select="image/@name" /></xsl:attribute>
			</xsl:if>

			<h2>Image data</h2>

			<!-- Name -->
			<xsl:if test="errors/form_errors/name = 'Valid::not_empty'">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id"    select="'name'" />
					<xsl:with-param name="label" select="'Name:'" />
					<xsl:with-param name="error" select="'Image name is required'" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="errors/form_errors/name = 'Content_Page::page_name_available'">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id"    select="'name'" />
					<xsl:with-param name="label" select="'Name:'" />
					<xsl:with-param name="error" select="'This image name is already taken'" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="not(errors/form_errors/name)">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id"    select="'name'" />
					<xsl:with-param name="label" select="'Image name:'" />
				</xsl:call-template>
			</xsl:if>

			<!-- Description -->
			<xsl:call-template name="form_line">
				<xsl:with-param name="id"    select="'description'" />
				<xsl:with-param name="label" select="'Description:'" />
			</xsl:call-template>

			<!-- Name -->
			<xsl:call-template name="form_line">
				<xsl:with-param name="id"    select="'date'" />
				<xsl:with-param name="label" select="'Date:'" />
			</xsl:call-template>

			<!-- Already stored data - ->
			<xsl:for-each select="image/field">
				<xsl:if test="@name != 'name' and @name != 'URL'">
					<xsl:call-template name="form_line">
						<xsl:with-param name="id"    select="concat(@name,'_',position())" />
						<!- -xsl:with-param name="name"  select="concat(@name),'[]'" /- ->
						<xsl:with-param name="label" select="@name" />
					</xsl:call-template>
				</xsl:if>
			</xsl:for-each>

			<!- - New custom fields - ->
			<xsl:for-each select="/root/content/users/custom_detail_field">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id"    select="concat('field_',.,'_',(position() + count(/root/content/user/field)))" />
					<xsl:with-param name="name"  select="concat('fieldid_',.,'[]')" />
					<xsl:with-param name="label" select="/root/content/users/field[@id = current()]" />
				</xsl:call-template>
			</xsl:for-each>

			<p>(To remove a field, just leave it blank)</p-->


			<xsl:if test="../meta/action = 'add_image'">
				<xsl:call-template name="form_button">
					<xsl:with-param name="value" select="'Add image'" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="../meta/action = 'edit_image'">
				<xsl:call-template name="form_button">
					<xsl:with-param name="value" select="'Save changes'" />
				</xsl:call-template>
			</xsl:if>
		</form>
	</xsl:template>

</xsl:stylesheet>
