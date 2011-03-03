<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:include href="tpl.default.xsl" />

	<xsl:template name="tabs">
		<ul class="tabs">

			<xsl:call-template name="tab">
				<xsl:with-param name="href"   select="'content'" />
				<xsl:with-param name="text"   select="'List content'" />
			</xsl:call-template>

			<xsl:call-template name="tab">
				<xsl:with-param name="href"   select="'content/add_content'" />
				<xsl:with-param name="action" select="'add_content'" />
				<xsl:with-param name="text"   select="'Add content'" />
			</xsl:call-template>

		</ul>
	</xsl:template>


	<xsl:template match="/">
		<xsl:if test="/root/meta/action = 'index'">
			<xsl:call-template name="template">
				<xsl:with-param name="title" select="'Admin - Content'" />
				<xsl:with-param name="h1" select="'List content'" />
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="/root/meta/action = 'add_content'">
			<xsl:call-template name="template">
				<xsl:with-param name="title" select="'Admin - Content'" />
				<xsl:with-param name="h1" select="'Add content'" />
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="/root/meta/action = 'edit_content'">
			<xsl:call-template name="template">
				<xsl:with-param name="title" select="'Admin - Content'" />
				<xsl:with-param name="h1" select="'Edit content'" />
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

	<!-- List content -->
	<xsl:template match="content[../meta/controller = 'content' and ../meta/action = 'index']">
		<table>
			<thead>
				<tr>
					<th class="medium_row">Content ID</th>
					<th>Content Type</th>
					<th>Content</th>
					<th class="medium_row">Action</th>
				</tr>
			</thead>
			<tbody>
				<xsl:for-each select="contents/content">
					<xsl:sort select="types" />
					<xsl:sort select="@id" />
					<tr>
						<xsl:if test="position() mod 2 = 1">
							<xsl:attribute name="class">odd</xsl:attribute>
						</xsl:if>
						<td><xsl:value-of select="@id" /></td>
						<td>
							<xsl:for-each select="types/type">
								<xsl:value-of select="." />
								<xsl:if test="position() != last()">
									<xsl:text>, </xsl:text>
								</xsl:if>
							</xsl:for-each>
						</td>
						<td><xsl:value-of select="concat(substring(content,1,60), '...')" /></td>
						<td>
							[<a>
							<xsl:attribute name="href">
								<xsl:text>content/edit_content/</xsl:text>
								<xsl:value-of select="@id" />
							</xsl:attribute>
							<xsl:text>Edit</xsl:text>
							</a>]
							[<a>
							<xsl:attribute name="href">
								<xsl:text>content/rm_content/</xsl:text>
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

	<!-- Add content -->
	<xsl:template match="content[../meta/controller = 'content' and ../meta/action = 'add_content']">
		<form method="post" action="content/add_content">

			<h2>Content types</h2>

			<xsl:for-each select="types/type">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="concat('type_id_',@id)" />
					<xsl:with-param name="label" select="concat(name,':')" />
					<xsl:with-param name="type" select="'checkbox'" />
				</xsl:call-template>
			</xsl:for-each>

			<h2>Content</h2>
			<xsl:call-template name="form_line">
				<xsl:with-param name="id" select="'content'" />
				<xsl:with-param name="label" select="'Content:'" />
				<xsl:with-param name="type" select="'textarea'" />
				<xsl:with-param name="rows" select="'20'" />
			</xsl:call-template>

			<xsl:call-template name="form_button">
				<xsl:with-param name="value" select="'Add content'" />
			</xsl:call-template>
		</form>
	</xsl:template>

	<!-- Edit content -->
	<xsl:template match="content[../meta/controller = 'content' and ../meta/action = 'edit_content']">
		<form method="post" action="content/edit_content/{content_id}">

			<h2>Content types</h2>

			<xsl:for-each select="types/type">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="concat('type_id_',@id)" />
					<xsl:with-param name="label" select="concat(name,':')" />
					<xsl:with-param name="type" select="'checkbox'" />
				</xsl:call-template>
			</xsl:for-each>

			<h2>Content</h2>
			<xsl:call-template name="form_line">
				<xsl:with-param name="id" select="'content'" />
				<xsl:with-param name="label" select="'Content:'" />
				<xsl:with-param name="type" select="'textarea'" />
				<xsl:with-param name="rows" select="'20'" />
			</xsl:call-template>

			<xsl:call-template name="form_button">
				<xsl:with-param name="value" select="'Save'" />
			</xsl:call-template>
		</form>
	</xsl:template>

</xsl:stylesheet>
