<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" encoding="utf-8" indent="yes"/>
	<xsl:template match="/">
		<extension>
			<xsl:attribute name="type"><xsl:value-of select="install/@type"/></xsl:attribute>
			<xsl:attribute name="version">1.6</xsl:attribute>
			<xsl:attribute name="client">site</xsl:attribute>
			<xsl:attribute name="method">upgrade</xsl:attribute>
			<xsl:copy-of select="install/files"/>
			<config>
				<fields>
					<xsl:attribute name="name">params</xsl:attribute>
					<xsl:apply-templates select="install/params"/>
				</fields>
			</config>
		</extension>
	</xsl:template>

	<xsl:template match="name">
		<name><xsl:value-of select="."/></name>
	</xsl:template>

	<xsl:template match="params">
		<xsl:if test="./@group">
			<fieldset><xsl:attribute name="name"><xsl:value-of select="@group"/></xsl:attribute></fieldset>
		</xsl:if>
		<xsl:if test="not(./@group)">
			<fieldset><xsl:attribute name="name">basic</xsl:attribute></fieldset>
		</xsl:if>
		<xsl:apply-templates/>
	</xsl:template>

	<xsl:template match="param[@type='list']">
		<field>
			<xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
			<xsl:attribute name="type">list</xsl:attribute>
			<xsl:copy-of select="./option"/>
		</field>
	</xsl:template>
		
</xsl:stylesheet>

