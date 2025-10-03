import { useState, useEffect } from 'react';
import { useLanguage } from '@/contexts/LanguageContext';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/backend';

interface TranslationField {
  text: string;
  manual: boolean;
}

interface UseTranslatedContentOptions {
  contentType: string;
  contentId: number | string;
  enabled?: boolean;
}

export function useTranslatedContent({ contentType, contentId, enabled = true }: UseTranslatedContentOptions) {
  const { currentLanguage } = useLanguage();
  const [translations, setTranslations] = useState<Record<string, string>>({});
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!enabled || currentLanguage === 'en') {
      setTranslations({});
      return;
    }

    const fetchTranslations = async () => {
      setLoading(true);
      setError(null);

      try {
        const response = await fetch(
          `${API_BASE_URL}/api/translations.php/batch?content_type=${contentType}&content_id=${contentId}&lang_code=${currentLanguage}`
        );

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success && data.translations) {
          const translationMap: Record<string, string> = {};
          Object.keys(data.translations).forEach((key) => {
            const field = data.translations[key] as TranslationField;
            translationMap[key] = field.text;
          });
          setTranslations(translationMap);
        }
      } catch (err) {
        console.error('Failed to fetch translations:', err);
        setError(err instanceof Error ? err.message : 'Unknown error');
      } finally {
        setLoading(false);
      }
    };

    fetchTranslations();
  }, [contentType, contentId, currentLanguage, enabled]);

  const getTranslation = (field: string, fallback: string): string => {
    if (currentLanguage === 'en') {
      return fallback;
    }
    return translations[field] || fallback;
  };

  return {
    translations,
    loading,
    error,
    getTranslation,
    currentLanguage,
  };
}
