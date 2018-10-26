<script type="application/ld+json">
{
  "@context": "http://schema.org",
  "@type": "BlogPosting",
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": "$Article.AbsoluteLink"
  },
  "headline": "$Article.Title",
  "image": [
    "$Article.HeroImage.AbsoluteLink"
   ],
  "datePublished": "$Article.Date.Format(yyyy-MM-dd'T'HH:mm:ssxxx)",
  "dateModified": "$Article.Updated.Format(yyyy-MM-dd'T'HH:mm:ssxxx)",
  "author": {
    "@type": "Person",
    "name": "$Article.Author"
  },
   "publisher": {
    "@type": "Organization",
    "name": "$Article.Organisation.Title",
    "logo": {
      "@type": "ImageObject",
      "url": "$SiteConfig.AMPCompanyLogo.AbsoluteLink"
    }
  },
  "description": "$Article.Lead"
}
</script>