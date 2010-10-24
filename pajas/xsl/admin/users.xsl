<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:include href="tpl.default.xsl" />

	<xsl:template name="title">
		<title>Admin - Users</title>
	</xsl:template>

  <xsl:template match="/">
    <xsl:call-template name="template" />
  </xsl:template>

	<!-- List fields -->
  <xsl:template match="content[../meta/controller = 'fields' and ../meta/action = 'index']">
		<h1>Fields</h1>

		<p><a href="fields/add_field">Add field</a></p>

		<table>
			<thead>
				<tr>
					<th>Field ID</th>
					<th>Name</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<xsl:for-each select="users/field">
					<tr>
						<td><xsl:value-of select="@id" /></td>
						<td>
							<a>
								<xsl:attribute name="href">
									<xsl:text>fields/edit_field/</xsl:text>
									<xsl:value-of select="@id" />
								</xsl:attribute>
								<xsl:value-of select="." />
							</a>
						</td>
						<td>[<a>
							<xsl:attribute name="href">
								<xsl:text>fields/rm_field/</xsl:text>
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

	<!-- Add field -->
  <xsl:template match="content[../meta/controller = 'fields' and ../meta/action = 'add_field']">
		<h1>Add field</h1>

		<form method="post" action="fields/add_field">
			<table>
			  <tr>
					<td>Field name:</td>
					<td>
						<xsl:call-template name="input_field">
							<xsl:with-param name="name" select="'field_name'" />
						</xsl:call-template>
					</td>
			  </tr>
			  <tr>
			  	<td colspan="2" class="button"><input type="submit" value="Add" /></td>
			  </tr>
			</table>
		</form>
  </xsl:template>

	<!-- Edit field -->
  <xsl:template match="content[../meta/controller = 'fields' and ../meta/action = 'edit_field']">
		<h1>Edit field</h1>

		<form method="post" action="fields/edit_field/{field/@id}">
			<table>
			  <tr>
					<td>Field name:</td>
					<td>
						<xsl:call-template name="input_field">
							<xsl:with-param name="name" select="'field_name'" />
						</xsl:call-template>
					</td>
			  </tr>
			  <tr>
			  	<td colspan="2" class="button"><input type="submit" value="Save" /></td>
			  </tr>
			</table>
		</form>
  </xsl:template>

	<!-- List users -->
  <xsl:template match="content[../meta/controller = 'users' and ../meta/action = 'index']">
		<h1>Users</h1>

		<p><a href="users/add_user">Add user</a></p>

		<table>
			<thead>
				<tr>
					<th>User ID</th>
					<th>Username</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<xsl:for-each select="users/user">
					<tr>
						<td><xsl:value-of select="user_id" /></td>
						<td>
							<a>
								<xsl:attribute name="href">
									<xsl:text>users/edit_user/</xsl:text>
									<xsl:value-of select="user_id" />
								</xsl:attribute>
								<xsl:value-of select="username" />
							</a>
						</td>
						<td>[<a>
							<xsl:attribute name="href">
								<xsl:text>users/rm_user/</xsl:text>
								<xsl:value-of select="user_id" />
							</xsl:attribute>
							<xsl:text>Delete</xsl:text>
							</a>]
						</td>
					</tr>
				</xsl:for-each>
			</tbody>
		</table>
  </xsl:template>

	<!-- Add user -->
  <xsl:template match="content[../meta/controller = 'users' and ../meta/action = 'add_user']">
		<h1>Add user</h1>

		<form method="post" action="users/add_user">
			<table>
			  <tr>
					<td>Username:</td>
					<td>
						<xsl:call-template name="input_field">
							<xsl:with-param name="name" select="'username'" />
						</xsl:call-template>
					</td>
			  </tr>
			  <tr>
					<td>Password:</td>
					<td>
						<xsl:call-template name="input_field">
							<xsl:with-param name="name" select="'password'" />
							<xsl:with-param name="type" select="'password'" />
						</xsl:call-template>
					</td>
			  </tr>
			  <tr>
			  	<th colspan="2">Additional data (Handle fields <a href="fields">here</a>)</th>
			  </tr>
			  <xsl:for-each select="users/field">
			  	<tr>
			  		<td><xsl:value-of select="." /></td>
			  		<td>
							<xsl:call-template name="input_field">
								<xsl:with-param name="name" select="concat('field_',.)" />
							</xsl:call-template>
			  		</td>
			  	</tr>
			  </xsl:for-each>
			  <tr>
			  	<td colspan="2" class="button"><input type="submit" value="Save" /></td>
			  </tr>
			</table>
		</form>
  </xsl:template>

	<!-- Edit user -->
  <xsl:template match="content[../meta/controller = 'users' and ../meta/action = 'edit_user']">
		<h1>Edit user</h1>

		<form method="post" action="users/edit_user/{user/user_id}">
			<table>
				<tr>
					<td>User ID:</td>
					<td><strong><xsl:value-of select="user/user_id" /></strong></td>
				</tr>
			  <tr>
					<td>Username:</td>
					<td>
						<xsl:call-template name="input_field">
							<xsl:with-param name="name" select="'username'" />
						</xsl:call-template>
					</td>
			  </tr>
			  <tr>
					<td>Password:</td>
					<td>
						<xsl:call-template name="input_field">
							<xsl:with-param name="name" select="'password'" />
							<xsl:with-param name="type" select="'password'" />
						</xsl:call-template>
						(Leave blank to not change)
					</td>
			  </tr>
			  <tr>
			  	<th colspan="2">Additional data (Handle fields <a href="fields">here</a>)</th>
			  </tr>
			  <xsl:for-each select="users/field">
			  	<tr>
			  		<td><xsl:value-of select="." /></td>
			  		<td>
							<xsl:call-template name="input_field">
								<xsl:with-param name="name" select="concat('field_',.)" />
							</xsl:call-template>
			  		</td>
			  	</tr>
			  </xsl:for-each>
			  <tr>
			  	<td colspan="2" class="button"><input type="submit" value="Save" /></td>
			  </tr>
			</table>
		</form>
  </xsl:template>



</xsl:stylesheet>
