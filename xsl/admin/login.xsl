<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<!-- INCLUDES -->
	<!--xsl:include href="inc.elements.xsl" /-->
	<xsl:include href="inc.common.xsl" />

	<xsl:output method="html" encoding="utf-8" />

	<!-- TEMPLATE -->
	<xsl:template match="/">
		<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<link type="text/css" href="../css/admin/style.css" rel="stylesheet" media="all" />
				<link href='http://fonts.googleapis.com/css?family=Cuprum&amp;subset=latin' rel='stylesheet' type='text/css' />
				<base href="http://{root/meta/domain}{/root/meta/base}admin/" />
				<title>Admin - Login</title>
				<!--[if lt IE 7]>
					<style media="screen" type="text/css">
						.contentwrap2
						{
							width: 100%;
						}
					</style>
				<![endif]-->
			</head>
			<body>
				<xsl:call-template name="header" />

				<div id="loginboxcontainer">
					<div id="loginbox">
						<h1>Login</h1>
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
									<td colspan="2"><input type="submit" value="Login" class="button" /></td>
								</tr>
							</table>
						</form>
					</div>
				</div>

			</body>
		</html>
	</xsl:template>

</xsl:stylesheet>
