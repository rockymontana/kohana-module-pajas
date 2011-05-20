<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:include href="tpl.default.xsl" />

	<xsl:template name="tabs">
		<ul class="tabs">

			<xsl:call-template name="tab">
				<xsl:with-param name="href"   select="'details'" />
				<xsl:with-param name="text"   select="'List details'" />
			</xsl:call-template>

			<xsl:call-template name="tab">
				<xsl:with-param name="href"   select="'details/add_detail'" />
				<xsl:with-param name="action" select="'add_detail'" />
				<xsl:with-param name="text"   select="'Add detail'" />
			</xsl:call-template>

		</ul>
	</xsl:template>


	<xsl:template match="/">
		<xsl:if test="/root/meta/action = 'index'">
			<xsl:call-template name="template">
				<xsl:with-param name="title" select="'Admin - Details'" />
				<xsl:with-param name="h1" select="'List details'" />
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="/root/meta/action = 'add_detail'">
			<xsl:call-template name="template">
				<xsl:with-param name="title" select="'Admin - Detail'" />
				<xsl:with-param name="h1" select="'Add detail'" />
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="/root/meta/action = 'edit_detail'">
			<xsl:call-template name="template">
				<xsl:with-param name="title" select="'Admin - Detail'" />
				<xsl:with-param name="h1" select="'Edit detail'" />
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

	<!-- List details -->
	<xsl:template match="content[../meta/controller = 'details' and ../meta/action = 'index']">
		<p class="error">Warning! Deleting a detail means all data related to this detail in all content, images etc.</p>
		<table>
			<thead>
				<tr>
					<th class="medium_row">Detail ID</th>
					<th>Name</th>
					<th class="medium_row">Action</th>
				</tr>
			</thead>
			<tbody>
				<xsl:for-each select="details/detail">
					<xsl:sort select="name" />
					<tr>
						<xsl:if test="position() mod 2 = 1">
							<xsl:attribute name="class">odd</xsl:attribute>
						</xsl:if>
						<td><xsl:value-of select="@id" /></td>
						<td><xsl:value-of select="." /></td>
						<td>
							<xsl:text>[</xsl:text>
							<!--a>
							  <xsl:attribute name="href">
								  <xsl:text>details/edit_detail/</xsl:text>
								  <xsl:value-of select="@id" />
							  </xsl:attribute>
							  <xsl:text>Edit</xsl:text>
							</a>
							<xsl:text>] [</xsl:text-->
							<a>
							  <xsl:attribute name="href">
								  <xsl:text>details/rm_detail/</xsl:text>
								  <xsl:value-of select="@id" />
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

	<!-- Add or edit detail -->
	<xsl:template match="content[../meta/controller = 'details' and (../meta/action = 'add_detail' or ../meta/action = 'edit_detail')]">
		<form method="post" action="details/add_detail">

			<xsl:if test="../meta/action = 'edit_detail'">
				<xsl:attribute name="action">
					<xsl:text>details/edit_detail/</xsl:text>
					<xsl:value-of select="detail/@id" />
				</xsl:attribute>
			</xsl:if>

			<xsl:if test="../meta/action = 'add_detail'">
				<h2>Add detail</h2>
			</xsl:if>
			<xsl:if test="../meta/action = 'edit_detail'">
				<h2>Edit <xsl:value-of select="detail" /></h2>
			</xsl:if>

			<xsl:if test="errors/form_errors/name = 'Content_Detail::name_available'">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'name'" />
					<xsl:with-param name="label" select="'Name:'" />
					<xsl:with-param name="error" select="'This detail name is already taken'" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="not(errors/form_errors/name)">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'name'" />
					<xsl:with-param name="label" select="'Name:'" />
				</xsl:call-template>
			</xsl:if>

			<xsl:if test="../meta/action = 'add_detail'">
				<xsl:call-template name="form_button">
					<xsl:with-param name="value" select="'Add detail'" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="../meta/action = 'edit_detail'">
				<xsl:call-template name="form_button">
					<xsl:with-param name="value" select="'Save changes'" />
				</xsl:call-template>
			</xsl:if>

		</form>
	</xsl:template>

</xsl:stylesheet>
