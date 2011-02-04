<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" encoding="utf-8" indent="yes"/>
	<xsl:template match="/">
		<extension>
			<xsl:attribute name="type"><xsl:value-of select="install/@type"/></xsl:attribute>
			<xsl:attribute name="version">1.6</xsl:attribute>
			<xsl:copy-of select="install/name"/>
			<xsl:copy-of select="install/creationDate"/>
			<xsl:copy-of select="install/author"/>
			<xsl:copy-of select="install/copyright"/>
			<xsl:copy-of select="install/authorEmail"/>
			<xsl:copy-of select="install/authorUrl"/>
			<xsl:copy-of select="install/version"/>
			<xsl:copy-of select="install/license"/>
			<xsl:copy-of select="install/description"/>
			<xsl:copy-of select="install/installfile"/>
			<xsl:copy-of select="install/uninstallfile"/>
			<xsl:copy-of select="install/scriptfile"/>
			<xsl:copy-of select="install/install"/>
			<xsl:copy-of select="install/uninstall"/>
			<xsl:copy-of select="install/files"/>
			<xsl:call-template name="administrator"/>
		</extension>
	</xsl:template>
	<xsl:template name="administrator" match="/install/administration">
		<administration>
			<xsl:copy-of select="/install/administration/menu"/>
			<xsl:copy-of select="/install/administration/submenu"/>
			<xsl:copy-of select="/install/administration/files"/>
		</administration>
	</xsl:template>

</xsl:stylesheet>

