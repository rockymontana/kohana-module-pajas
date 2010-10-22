<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="html" encoding="utf-8" />

	<!-- TEMPLATE -->
	<xsl:template match="/">
		<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<link type="text/css" href="/css/admin/style.css" rel="stylesheet" media="all" />

				<!-- Example of how to obtain XML data in an attribute -->
				<base href="http://{root/meta/domain}/admin/" />

				<title>Login</title>
			</head>
			<body>
				<h1>Administration system</h1>
				<div class="loginbox">
					<h2>Login</h2>
					<form method="post" action="login/do">
						<table>
							<xsl:if test="root/content/error">
								<tr>
									<td colspan="2" class="error"><xsl:value-of select="root/content/error" /></td>
								</tr>
							</xsl:if>
							<tr>
								<td><label for="username">Username:</label></td>
								<td><input type="text" name="username" id="username" /></td>
							</tr>
							<tr>
								<td><label for="password">Password:</label></td>
								<td><input type="password" name="password" /></td>
							</tr>
							<tr>
								<td colspan="2"><input type="submit" value="Login" /></td>
							</tr>
						</table>
					</form>
				</div>
			</body>
		</html>
	</xsl:template>

</xsl:stylesheet>
