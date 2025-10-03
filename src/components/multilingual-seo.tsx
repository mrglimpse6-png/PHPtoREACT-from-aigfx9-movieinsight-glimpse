import { Helmet } from 'react-helmet-async';
import { useLanguage } from '@/contexts/LanguageContext';

interface MultilingualSEOProps {
  title: string;
  description: string;
  canonicalPath: string;
  ogImage?: string;
}

export function MultilingualSEO({
  title,
  description,
  canonicalPath,
  ogImage,
}: MultilingualSEOProps) {
  const { currentLanguage, availableLanguages } = useLanguage();
  const baseUrl = import.meta.env.VITE_SITE_URL || 'https://adilgfx.com';

  const canonicalUrl = `${baseUrl}${canonicalPath}`;

  const getLocalizedUrl = (langCode: string) => {
    if (langCode === 'en') {
      return `${baseUrl}${canonicalPath}`;
    }
    return `${baseUrl}/${langCode}${canonicalPath}`;
  };

  return (
    <Helmet>
      <html lang={currentLanguage} dir={availableLanguages.find(l => l.lang_code === currentLanguage)?.rtl ? 'rtl' : 'ltr'} />

      <title>{title}</title>
      <meta name="description" content={description} />

      <link rel="canonical" href={canonicalUrl} />

      {availableLanguages
        .filter(lang => lang.active)
        .map((lang) => (
          <link
            key={lang.lang_code}
            rel="alternate"
            hrefLang={lang.lang_code}
            href={getLocalizedUrl(lang.lang_code)}
          />
        ))}

      <link rel="alternate" hrefLang="x-default" href={canonicalUrl} />

      <meta property="og:title" content={title} />
      <meta property="og:description" content={description} />
      <meta property="og:url" content={canonicalUrl} />
      <meta property="og:locale" content={currentLanguage} />
      {ogImage && <meta property="og:image" content={ogImage} />}

      {availableLanguages
        .filter(lang => lang.active && lang.lang_code !== currentLanguage)
        .map((lang) => (
          <meta
            key={lang.lang_code}
            property="og:locale:alternate"
            content={lang.lang_code}
          />
        ))}

      <meta name="twitter:card" content="summary_large_image" />
      <meta name="twitter:title" content={title} />
      <meta name="twitter:description" content={description} />
      {ogImage && <meta name="twitter:image" content={ogImage} />}
    </Helmet>
  );
}
