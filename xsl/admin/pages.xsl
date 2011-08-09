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

			<h2>Content</h2>
			<p>Use the dropdown to specify where the content is to show up on the page.<br />
			<strong>Template position: tag</strong></p>

			<xsl:if test="../meta/action = 'add_page'">
				<!-- List several for starters -->
				<xsl:call-template name="template_positions" />
			</xsl:if>

			<xsl:if test="../meta/action = 'edit_page'">
				<xsl:if test="tmp">
					<xsl:for-each select="tmp/template_field">
						<xsl:sort select="@id" data-type="number" />
						<xsl:call-template name="template_fields_tags">
							<xsl:with-param name="template_field" select="@id" />
						</xsl:call-template>
					</xsl:for-each>
				</xsl:if>
				<xsl:if test="not(tmp)">
					<xsl:for-each select="page/template_fields/template_field">
						<xsl:sort select="number(@id)" data-type="number" />
						<xsl:call-template name="template_fields_tags">
							<xsl:with-param name="template_field" select="@id" />
						</xsl:call-template>
					</xsl:for-each>
				</xsl:if>
				<xsl:call-template name="template_positions">
					<xsl:with-param name="i" select="30" />
				</xsl:call-template>
			</xsl:if>

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

	<xsl:template name="template_fields_tags">
		<xsl:param name="template_field" />

		<xsl:if test="/root/content/tmp">
			<xsl:for-each select="/root/content/tmp/template_field[@id = $template_field]/tag">
				<xsl:sort select="../@id" data-type="number" />
				<p class="custom_row">
					<select name="template_position[]">
						<xsl:call-template name="template_positions_nr">
							<xsl:with-param name="selected" select="$template_field" />
						</xsl:call-template>
					</select>:
					<select name="tag_id[]">
						<option value="">--- None ---</option>
						<xsl:call-template name="tags">
							<xsl:with-param name="selected" select="@id" />
						</xsl:call-template>
					</select>
				</p>
			</xsl:for-each>
		</xsl:if>
		<xsl:if test="not(/root/content/tmp)">
			<xsl:for-each select="/root/content/page/template_fields/template_field[@id = $template_field]/tag">
				<xsl:sort select="../@id" data-type="number" />
				<p class="custom_row">
					<select name="template_position[]">
						<xsl:call-template name="template_positions_nr">
							<xsl:with-param name="selected" select="$template_field" />
						</xsl:call-template>
					</select>:
					<select name="tag_id[]">
						<option value="">--- None ---</option>
						<xsl:call-template name="tags">
							<xsl:with-param name="selected" select="@id" />
						</xsl:call-template>
					</select>
				</p>
			</xsl:for-each>
		</xsl:if>

	</xsl:template>

	<xsl:template name="tags">
		<xsl:param name="selected" />

		<xsl:for-each select="/root/content/tags/tag">
			<xsl:sort select="." />
			<option value="{@id}">
				<xsl:if test="@id = $selected">
					<xsl:attribute name="selected">selected</xsl:attribute>
				</xsl:if>
				<xsl:value-of select="." />
			</option>
		</xsl:for-each>

	</xsl:template>

	<xsl:template name="template_positions">
		<xsl:param name="i">1</xsl:param>
			<p class="custom_row">
				<select name="template_position[]">
					<xsl:choose>
						<xsl:when test="/root/content/tmp/template_field[position() = $i]/@id">
							<xsl:call-template name="template_positions_nr">
								<xsl:with-param name="selected" select="/root/content/tmp/template_field[position() = $i]/@id" />
							</xsl:call-template>
						</xsl:when>
						<xsl:when test="not(/root/content/tmp/template_field[position() = $i]/@id) and /root/meta/action = 'add_page'">
							<xsl:call-template name="template_positions_nr">
								<xsl:with-param name="selected" select="$i" />
							</xsl:call-template>
						</xsl:when>
						<xsl:otherwise>
							<xsl:call-template name="template_positions_nr" />
						</xsl:otherwise>
					</xsl:choose>
				</select>:
				<select name="tag_id[]">
					<option value="">--- None ---</option>
					<xsl:for-each select="/root/content/tags/tag">
						<xsl:sort select="." />
						<option value="{@id}">
							<xsl:if test="/root/content/tmp/template_field[position() = $i]/tag/@id = @id">
								<xsl:attribute name="selected">selected</xsl:attribute>
							</xsl:if>
							<xsl:value-of select="." />
						</option>
					</xsl:for-each>
				</select>
			</p>
		<xsl:if test="$i &lt; 30">
			<xsl:call-template name="template_positions">
				<xsl:with-param name="i" select="number($i)+1" />
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
	<xsl:template name="template_positions_nr">
		<xsl:param name="i">1</xsl:param>
		<xsl:param name="selected">1</xsl:param>

		<option value="{$i}">
			<xsl:if test="number($i) = number($selected)">
				<xsl:attribute name="selected">selected</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="$i" />
		</option>

		<xsl:if test="$i &lt; 30">
			<xsl:call-template name="template_positions_nr">
				<xsl:with-param name="i"        select="number($i)+1" />
				<xsl:with-param name="selected" select="$selected"    />
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>
