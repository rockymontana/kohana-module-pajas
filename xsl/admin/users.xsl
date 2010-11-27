<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:include href="tpl.default.xsl" />

	<xsl:template name="title">
		<title>Admin - Users</title>
	</xsl:template>

	<xsl:template name="tabs">
		<ul class="tabs">
		  <xsl:if test="/root/meta/controller = 'users'">
				<li>
					<a href="users">
						<xsl:if test="/root/meta/action = 'index'">
							<xsl:attribute name="class">selected</xsl:attribute>
						</xsl:if>
						<xsl:text>List users</xsl:text>
					</a>
				</li>
				<li>
					<a href="users/add_user">
						<xsl:if test="/root/meta/action = 'add_user'">
							<xsl:attribute name="class">selected</xsl:attribute>
						</xsl:if>
						<xsl:text>Add user</xsl:text>
					</a>
				</li>
			</xsl:if>
		  <xsl:if test="/root/meta/controller = 'fields'">
				<li>
					<a href="fields">
						<xsl:if test="/root/meta/action = 'index'">
							<xsl:attribute name="class">selected</xsl:attribute>
						</xsl:if>
						<xsl:text>List fields</xsl:text>
					</a>
				</li>
				<li>
					<a href="fields/add_field">
						<xsl:if test="/root/meta/action = 'add_field'">
							<xsl:attribute name="class">selected</xsl:attribute>
						</xsl:if>
						<xsl:text>Add field</xsl:text>
					</a>
				</li>
			</xsl:if>
		</ul>
	</xsl:template>


  <xsl:template match="/">
  	<xsl:if test="/root/content[../meta/controller = 'fields' and ../meta/action = 'index']">
		  <xsl:call-template name="template">
		  	<xsl:with-param name="title" select="'Admin - Users'" />
		  	<xsl:with-param name="h1" select="'Fields'" />
		  </xsl:call-template>
  	</xsl:if>
  	<xsl:if test="/root/content[../meta/controller = 'fields' and ../meta/action = 'add_field']">
		  <xsl:call-template name="template">
		  	<xsl:with-param name="title" select="'Admin - Users'" />
		  	<xsl:with-param name="h1" select="'Add field'" />
		  </xsl:call-template>
  	</xsl:if>
  	<xsl:if test="/root/content[../meta/controller = 'fields' and ../meta/action = 'edit_field']">
		  <xsl:call-template name="template">
		  	<xsl:with-param name="title" select="'Admin - Users'" />
		  	<xsl:with-param name="h1" select="'Edit field'" />
		  </xsl:call-template>
  	</xsl:if>
  	<xsl:if test="/root/content[../meta/controller = 'users' and ../meta/action = 'index']">
		  <xsl:call-template name="template">
		  	<xsl:with-param name="title" select="'Admin - Users'" />
		  	<xsl:with-param name="h1" select="'Users'" />
		  </xsl:call-template>
  	</xsl:if>
  	<xsl:if test="/root/content[../meta/controller = 'users' and ../meta/action = 'add_user']">
		  <xsl:call-template name="template">
		  	<xsl:with-param name="title" select="'Admin - Users'" />
		  	<xsl:with-param name="h1" select="'Add user'" />
		  </xsl:call-template>
  	</xsl:if>
  	<xsl:if test="/root/content[../meta/controller = 'users' and ../meta/action = 'edit_user']">
		  <xsl:call-template name="template">
		  	<xsl:with-param name="title" select="'Admin - Users'" />
		  	<xsl:with-param name="h1" select="'Edit user'" />
		  </xsl:call-template>
  	</xsl:if>
  </xsl:template>

	<!-- List fields -->
  <xsl:template match="content[../meta/controller = 'fields' and ../meta/action = 'index']">
		<table>
			<thead>
				<tr>
					<th class="small_row">Field ID</th>
					<th>Name</th>
					<th class="medium_row">Action</th>
				</tr>
			</thead>
			<tbody>
				<xsl:for-each select="users/field">
					<tr>
						<xsl:if test="position() mod 2 = 1">
							<xsl:attribute name="class">odd</xsl:attribute>
						</xsl:if>
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
		<form method="post" action="fields/add_field">

			<!-- Include an error -->
			<xsl:if test="/root/content/errors/form_errors/field_name = 'User::field_name_available'">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'field_name'" />
					<xsl:with-param name="label" select="'Field name:'" />
					<xsl:with-param name="error" select="'This field name is already taken'" />
				</xsl:call-template>
			</xsl:if>

			<!-- no error -->
			<xsl:if test="not(/root/content/errors/form_errors/field_name = 'User::field_name_available')">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'field_name'" />
					<xsl:with-param name="label" select="'Field name:'" />
				</xsl:call-template>
			</xsl:if>

			<xsl:call-template name="form_button">
				<xsl:with-param name="value" select="'Add'" />
			</xsl:call-template>

		</form>
  </xsl:template>

	<!-- Edit field -->
  <xsl:template match="content[../meta/controller = 'fields' and ../meta/action = 'edit_field']">
		<form method="post" action="fields/edit_field/{field/@id}">

			<!-- Include an error -->
			<xsl:if test="/root/content/errors/form_errors/field_name = 'User::field_name_available'">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'field_name'" />
					<xsl:with-param name="label" select="'Field name:'" />
					<xsl:with-param name="error" select="'This field name is already taken'" />
				</xsl:call-template>
			</xsl:if>

			<!-- no error -->
			<xsl:if test="not(/root/content/errors/form_errors/field_name = 'User::field_name_available')">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'field_name'" />
					<xsl:with-param name="label" select="'Field name:'" />
				</xsl:call-template>
			</xsl:if>

			<xsl:call-template name="form_button">
				<xsl:with-param name="value" select="'Save'" />
			</xsl:call-template>

		</form>
  </xsl:template>

	<!-- List users -->
  <xsl:template match="content[../meta/controller = 'users' and ../meta/action = 'index']">
		<table>
			<thead>
				<tr>
					<th class="small_row">User ID</th>
					<th>Username</th>
					<th class="medium_row">Action</th>
				</tr>
			</thead>
			<tbody>
				<xsl:for-each select="users/user">
					<tr>
						<xsl:if test="position() mod 2 = 1">
							<xsl:attribute name="class">odd</xsl:attribute>
						</xsl:if>
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
  	<form method="post" action="users/add_user">

			<h2>Basic user information</h2>

			<!-- Username -->
			<xsl:if test="/root/content/errors/form_errors/username">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'username'" />
					<xsl:with-param name="label" select="'Username:'" />
					<xsl:with-param name="error" select="'This username already taken'" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="not(/root/content/errors/form_errors/username)">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'username'" />
					<xsl:with-param name="label" select="'Username:'" />
				</xsl:call-template>
			</xsl:if>

			<!-- Password -->
			<xsl:if test="/root/content/errors/form_errors/password">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'password'" />
					<xsl:with-param name="label" select="'Password:'" />
					<xsl:with-param name="type" select="'password'" />
					<xsl:with-param name="error" select="'A password is required'" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="not(/root/content/errors/form_errors/password)">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'password'" />
					<xsl:with-param name="label" select="'Password:'" />
					<xsl:with-param name="type" select="'password'" />
				</xsl:call-template>
			</xsl:if>

			<h2>User details</h2>

			<xsl:for-each select="/root/content/users/custom_detail_field">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="concat('field_',.,'_',position())" />
					<xsl:with-param name="name" select="concat('fieldid_',.,'[]')" />
					<xsl:with-param name="label" select="/root/content/users/field[@id = current()]" />
				</xsl:call-template>
			</xsl:for-each>

			<p>(To remove a field, just leave it blank)</p>

			<h2>Add user detail field:</h2>

			<label for="add_field">
				Add field:
				<select name="add_field">
					<option selected="selected"></option>
					<xsl:for-each select="/root/content/users/field">
						<option value="{@id}"><xsl:value-of select="." /></option>
					</xsl:for-each>
				</select>
			</label>
			<input type="submit" name="do_add_field" value="Add Field" class="button add_field" />

			<xsl:call-template name="form_button">
				<xsl:with-param name="value" select="'Add User'" />
			</xsl:call-template>
  	</form>
  </xsl:template>

	<!-- Edit user -->
  <xsl:template match="content[../meta/controller = 'users' and ../meta/action = 'edit_user']">
  	<form method="post" action="users/edit_user/{user/user_id}" autocomplete="off">

			<h2>Basic user information</h2>

			<!-- User ID -->
			<xsl:call-template name="form_line">
				<xsl:with-param name="type" select="'none'" />
				<xsl:with-param name="label" select="'User ID:'" />
				<xsl:with-param name="value" select="user/user_id" />
			</xsl:call-template>

			<!-- Username -->
			<xsl:if test="/root/content/errors/form_errors/username">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'username'" />
					<xsl:with-param name="label" select="'Username:'" />
					<xsl:with-param name="error" select="'This username already taken'" />
				</xsl:call-template>
			</xsl:if>
			<xsl:if test="not(/root/content/errors/form_errors/username)">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="'username'" />
					<xsl:with-param name="label" select="'Username:'" />
				</xsl:call-template>
			</xsl:if>

			<!-- Password -->
			<xsl:call-template name="form_line">
				<xsl:with-param name="id" select="'password'" />
				<xsl:with-param name="label" select="'Password:'" />
				<xsl:with-param name="type" select="'password'" />
			</xsl:call-template>
			<p>(Leave password field blank to use the old password)</p>

			<h2>User details</h2>

			<!-- Already stored data -->
			<xsl:for-each select="/root/content/user/field">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="concat('field_',@id,'_',position())" />
					<xsl:with-param name="name" select="concat('fieldid_',@id,'[]')" />
					<xsl:with-param name="label" select="/root/content/users/field[@id = current()/@id]" />
				</xsl:call-template>
			</xsl:for-each>

			<!-- New custom fields -->
			<xsl:for-each select="/root/content/users/custom_detail_field">
				<xsl:call-template name="form_line">
					<xsl:with-param name="id" select="concat('field_',.,'_',(position() + count(/root/content/user/field)))" />
					<xsl:with-param name="name" select="concat('fieldid_',.,'[]')" />
					<xsl:with-param name="label" select="/root/content/users/field[@id = current()]" />
				</xsl:call-template>
			</xsl:for-each>

			<p>(To remove a field, just leave it blank)</p>

			<h2>Add user detail field:</h2>

			<label for="add_field">
				Add field:
				<select name="add_field">
					<option selected="selected"></option>
					<xsl:for-each select="/root/content/users/field">
						<option value="{@id}"><xsl:value-of select="." /></option>
					</xsl:for-each>
				</select>
			</label>
			<input type="submit" name="do_add_field" value="Add Field" class="button add_field" />

			<xsl:call-template name="form_button">
				<xsl:with-param name="value" select="'Save'" />
			</xsl:call-template>
  	</form>

  </xsl:template>

</xsl:stylesheet>
