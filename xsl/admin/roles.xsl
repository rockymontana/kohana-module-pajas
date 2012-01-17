<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:include href="tpl.default.xsl" />

	<xsl:template name="tabs">
		<ul class="tabs">

			<xsl:call-template name="tab">
				<xsl:with-param name="href"   select="'roles'" />
				<xsl:with-param name="text"   select="'List roles'" />
			</xsl:call-template>

			<xsl:call-template name="tab">
				<xsl:with-param name="href"   select="'roles/role'" />
				<xsl:with-param name="action" select="'role'" />
				<xsl:with-param name="text"   select="'Add role'" />
				<xsl:with-param name="url_param" select="''" />
			</xsl:call-template>

		</ul>
	</xsl:template>

		<xsl:template match="/">
		<xsl:if test="/root/content[../meta/controller = 'roles' and ../meta/action = 'index']">
			<xsl:call-template name="template">
				<xsl:with-param name="title" select="'Admin - Roles'" />
				<xsl:with-param name="h1"    select="'Roles'" />
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="/root/content[../meta/controller = 'roles' and ../meta/action = 'role' and ../meta/url_params/id]">
			<xsl:call-template name="template">
				<xsl:with-param name="title" select="'Admin - roles'" />
				<xsl:with-param name="h1"    select="'Edit role'" />
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="/root/content[../meta/controller = 'roles' and ../meta/action = 'role' and not(../meta/url_params/id)]">
			<xsl:call-template name="template">
				<xsl:with-param name="title" select="'Admin - role'" />
				<xsl:with-param name="h1"    select="'Add role'" />
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

	<!-- List Roles -->
	<xsl:template match="content[../meta/action = 'index']">
		<table>
			<thead>
				<tr>
					<th>Role</th>
					<th>Uri</th>
					<th class="medium_row">Action</th>
				</tr>
			</thead>
			<tbody>
				<xsl:for-each select="roles/*">
					<tr>
						<xsl:if test="position() mod 2 = 1">
							<xsl:attribute name="class">odd</xsl:attribute>
						</xsl:if>
						<td><xsl:value-of select="name" /></td>
						<td><xsl:value-of select="uri" /></td>
						<td>
							[<a>
							<xsl:attribute name="href">
								<xsl:text>roles/role/</xsl:text>
								<xsl:value-of select="name" />
							</xsl:attribute>
							<xsl:text>Edit</xsl:text>
							</a>]
							[<a>
							<xsl:attribute name="href">
								<xsl:text>roles/rm?id=</xsl:text>
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

	<xsl:template match="content[../meta/action = 'role']">
		<form method="post" action="roles/role">

			<xsl:if test="../meta/url_params/id">
				<xsl:attribute name="action">
					<xsl:text>roles/role?id=</xsl:text>
					<xsl:value-of select="../meta/url_params/id" />
				</xsl:attribute>

				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'id'" />
					<xsl:with-param name="label" select="'ID:'" />
					<xsl:with-param name="type" select="'none'" />
					<xsl:with-param name="value" select="../meta/url_params/id" />
				</xsl:call-template>
			</xsl:if>

			<xsl:call-template name="form_line">
				<xsl:with-param name="id" select="'name'" />
				<xsl:with-param name="label" select="'Name:'" />
			</xsl:call-template>

			<xsl:if test="../meta/url_params/id">
				<xsl:call-template name="form_button">
					<xsl:with-param name="value" select="'Save'" />
				</xsl:call-template>
			</xsl:if>

			<xsl:if test="not(../meta/url_params/id)">
				<xsl:call-template name="form_button">
					<xsl:with-param name="value" select="'Create'" />
				</xsl:call-template>
			</xsl:if>

		</form>
	</xsl:template>

</xsl:stylesheet>
