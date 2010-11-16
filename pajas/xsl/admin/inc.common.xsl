<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template name="header">
		<div id="header">
			<p id="logo">Pajas</p>
			<p class="info">
				<!--LARV IT AB [<a href="#">edit</a>] <span>|</span> -->

				<xsl:if test="/root/meta/user_data">
					Logged in as: <xsl:value-of select="/root/meta/user_data/username" /> [<a href="logout">logout</a>]
				</xsl:if>
			</p>
		</div>
	</xsl:template>

	<xsl:template name="form_line">
		<xsl:param name="id" /><!-- This should always be set -->
		<xsl:param name="name" />
		<xsl:param name="value" />
		<xsl:param name="label" />
		<xsl:param name="options" />
		<xsl:param name="error" />
		<xsl:param name="disabled" />

		<xsl:param name="type">text</xsl:param>

		<!-- Only used if type is textarea -->
		<xsl:param name="rows" />
		<xsl:param name="cols" />

		<label for="{$id}">
			<xsl:value-of select="$label" />

			<!-- Options is not available, that means this is either an input or a textarea -->
			<xsl:if test="not($options) or not($options/*)">

				<!-- Textarea -->
				<xsl:if test="$type = 'textarea'">
					<textarea id="{$id}" name="{$name}">

						<xsl:if test="$disabled">
							<xsl:attribute name="disabled">disabled</xsl:attribute>
						</xsl:if>

						<xsl:for-each select="/root/content/errors/form_errors/*">
							<xsl:if test="name(.) = $id">
								<xsl:attribute name="class">error</xsl:attribute>
							</xsl:if>
						</xsl:for-each>

						<xsl:attribute name="name">
							<xsl:if test="$name = ''">
								<xsl:value-of select="$id" />
							</xsl:if>
							<xsl:if test="$name != ''">
								<xsl:value-of select="$name" />
							</xsl:if>
						</xsl:attribute>

						<xsl:if test="$value = '' and /root/content/formdata/field[@id = $id]">
							<xsl:value-of select="/root/content/formdata/field[@id = $id]" />
						</xsl:if>
						<xsl:if test="not($value = '' and /root/content/formdata/field[@id = $id])">
							<xsl:value-of select="$value" />
						</xsl:if>

					</textarea>
				</xsl:if>

				<!-- No input field, just plain text -->
				<xsl:if test="$type = 'none'">
					<span class="instead_of_input">
						<xsl:value-of select="$value" />
					</span>
				</xsl:if>

				<!-- All other input types -->
				<xsl:if test="$type != 'textarea' and $type != 'none'">
					<input type="{$type}" id="{$id}">

						<xsl:if test="$disabled">
							<xsl:attribute name="disabled">disabled</xsl:attribute>
						</xsl:if>

						<xsl:for-each select="/root/content/errors/form_errors/*">
							<xsl:if test="name(.) = $id">
								<xsl:attribute name="class">error</xsl:attribute>
							</xsl:if>
						</xsl:for-each>

						<xsl:attribute name="name">
							<xsl:if test="$name = ''">
								<xsl:value-of select="$id" />
							</xsl:if>
							<xsl:if test="$name != ''">
								<xsl:value-of select="$name" />
							</xsl:if>
						</xsl:attribute>

						<xsl:if test="$type != 'password'">
							<xsl:attribute name="value">
								<xsl:if test="$value = '' and /root/content/formdata/field[@id = $id]">
									<xsl:value-of select="/root/content/formdata/field[@id = $id]" />
								</xsl:if>
								<xsl:if test="not($value = '' and /root/content/formdata/field[@id = $id])">
									<xsl:value-of select="$value" />
								</xsl:if>
							</xsl:attribute>
						</xsl:if>

					</input>
				</xsl:if>
			</xsl:if>

			<!-- If options is present, this should be a select-input -->
			<xsl:if test="$options">

				<select id="{$id}">

					<xsl:if test="$disabled">
						<xsl:attribute name="disabled">disabled</xsl:attribute>
					</xsl:if>

					<xsl:attribute name="name">
						<xsl:if test="$name = ''">
							<xsl:value-of select="$id" />
						</xsl:if>
						<xsl:if test="$name != ''">
							<xsl:value-of select="$name" />
						</xsl:if>
					</xsl:attribute>

					<xsl:for-each select="$options/option">
						<xsl:sort select="@sorting" />

						<option value="{@value}">

							<xsl:if test="($value != '' and $value = @value) or ($value = '' and @value = /root/content/formdata/field[@id = $id])">
								<xsl:attribute name="selected">selected</xsl:attribute>
							</xsl:if>

							<xsl:value-of select="." />

						</option>

					</xsl:for-each>

				</select>

			</xsl:if>

		</label>

		<!-- Error message -->
		<p class="error"><xsl:value-of select="$error" />&#160;</p>

	</xsl:template>

	<xsl:template name="form_button">
		<xsl:param name="value" />

		<label>
			<input type="submit" class="button" value="{$value}" />
		</label>
	</xsl:template>

	<xsl:template name="input_field">
		<xsl:param name="id" />
		<xsl:param name="name" />
		<xsl:param name="value" />
		<xsl:param name="class" />
		<xsl:param name="options" />

		<xsl:param name="type">text</xsl:param>

		<xsl:if test="not($options) or not($options/*)">
			<input type="{$type}">

				<xsl:if test="$id != ''">
					<xsl:attribute name="id"><xsl:value-of select="$id" /></xsl:attribute>
				</xsl:if>

				<xsl:if test="$name != ''">
					<xsl:attribute name="name"><xsl:value-of select="$name" /></xsl:attribute>
				</xsl:if>

				<xsl:if test="$value != '' and $type != 'password'">
					<xsl:attribute name="value"><xsl:value-of select="$value" /></xsl:attribute>
				</xsl:if>
				<xsl:if test="$value = '' and $type != 'password'">
					<xsl:attribute name="value"><xsl:value-of select="/root/content/formdata/field[@name = $name]" /></xsl:attribute>
				</xsl:if>

				<xsl:if test="$class != ''">
					<xsl:attribute name="class"><xsl:value-of select="$class" /></xsl:attribute>
				</xsl:if>

				<xsl:if test="$class = ''">
					<xsl:for-each select="/root/content/errors/form_errors/*">
						<xsl:if test="name(.) = $name">
							<xsl:attribute name="class">error</xsl:attribute>
						</xsl:if>
					</xsl:for-each>
				</xsl:if>

			</input>
		</xsl:if>

		<xsl:if test="$options">
			<xsl:if test="$options/*">
				<select>
					<xsl:if test="$id != ''">
						<xsl:attribute name="id"><xsl:value-of select="$id" /></xsl:attribute>
					</xsl:if>

					<xsl:if test="$name != ''">
						<xsl:attribute name="name"><xsl:value-of select="$name" /></xsl:attribute>
					</xsl:if>

					<xsl:for-each select="$options/option">
						<xsl:sort select="@sorting" />

						<option value="{@value}">

							<xsl:if test="($value != '' and $value = @value) or ($value = '' and @value = /root/content/formdata/field[@name = $name])">
								<xsl:attribute name="selected">selected</xsl:attribute>
							</xsl:if>

							<xsl:value-of select="." />
						</option>

					</xsl:for-each>

				</select>
			</xsl:if>
		</xsl:if>

	</xsl:template>

</xsl:stylesheet>
