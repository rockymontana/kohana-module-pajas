<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

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
