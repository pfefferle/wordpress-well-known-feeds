<?xml version="1.0" encoding="utf-8"?>
<!--

# Feedback

This file is in BETA. Please test and contribute to the discussion:

   https://github.com/pfefferle/wordpress-well-known-feeds/issues

-->
<xsl:stylesheet version="3.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
				xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/"
				xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd">
	<xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<title><xsl:value-of select="/opml/head/title"/></title>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
				<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
				<style type="text/css">
					* { box-sizing: border-box; }
					svg { max-width: 100%; }
					body { --gap: 5vw; margin: 0; font-family: system-ui; line-height: 1.7; }
					h1,h2,h3 { margin-block-start: 0; margin-block-end: 0; }
					.pb-5 { padding-bottom: calc(var(--gap) / 3); }
					.meta { color: #676767; }
					.container {
						display: grid;
						gap: var(--gap);
						max-width: 46rem;
						width: 95%;
						margin: auto;
					}
					.intro {
						background-color: #F0F8FF;
						margin-block-end: var(--gap);
						padding-block: calc(var(--gap) / 2);
					}
					.intro .container {
						gap: 1rem;
						grid-template-columns:  4fr 2fr;
						align-items: top;
					}
					@media (min-width: 40rem) {
						.intro .container {
						grid-template-columns:  4fr 1fr;
						align-items: center;
						}
					}
					.recent {
						padding-block-end: var(--gap);
					}
					header img {
						width: 5em;
						border-radius: 20%;
					}
				</style>
			</head>
			<body>
				<nav class="intro">
					<div class="container">
						<div>
							<h1>
								<xsl:value-of select="/opml/head/title"/>
							</h1>
							<p>
								<xsl:value-of select="/opml/head/dateCreated"/>
							</p>
						</div>
						<svg xmlns="http://www.w3.org/2000/svg" version="1.1" id="OPMLicon" width="128" height="128" viewBox="0 0 256 256">
							<title>OPML icon</title>
							<defs>
								<linearGradient x1="0.903" y1="0.903" x2="0.096" y2="0.096" id="OPMLg">
									<stop  offset="0.0" stop-color="#264fa1"/>
									<stop  offset="0.1134" stop-color="#2658a1"/>
									<stop  offset="0.2984" stop-color="#2570a1"/>
									<stop  offset="0.5" stop-color="#2492a1"/>
									<stop  offset="0.6147" stop-color="#2489a0"/>
									<stop  offset="0.8017" stop-color="#26719f"/>
									<stop  offset="1.0" stop-color="#27509d"/>
								</linearGradient>
							</defs>
							<rect width="256" height="256" rx="55" ry="55" x="0"  y="0"  fill="#244fa1"/>
							<rect width="246" height="246" rx="50" ry="50" x="5"  y="5"  fill="#2492a1"/>
							<rect width="236" height="236" rx="47" ry="47" x="10" y="10" fill="url(#OPMLg)"/>
							<circle cx="128" cy="128" r="23" fill="#fff"/>
							<path fill="#fff" d="M128 35a93 93 0 1 0 93 93A93 93 0 0 0 128 35Zm0 158a65 65 0 1 1 65-65A65 65 0 0 1 128 193Z"/>
						</svg>
					</div>
				</nav>

				<div class="container">
					<section class="recent">
						<!-- RSS -->
						<xsl:for-each select="/opml/body/outline/outline">
						<div class="pb-5">
							<h2>
								<a target="_blank">
									<xsl:attribute name="href">
										<xsl:value-of select="@xmlUrl"/>
									</xsl:attribute>
									<xsl:value-of select="@text"/>
								</a>
							</h2>
							<small class="meta">
								<xsl:value-of select="@description" />
							</small>
						</div>
						</xsl:for-each>
					</section>
				</div>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>