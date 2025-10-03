import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';

interface Language {
  id: number;
  lang_code: string;
  lang_name: string;
  native_name: string;
  flag_icon: string;
  rtl: boolean;
  active: boolean;
  default_lang: boolean;
}

interface TranslationContextType {
  currentLanguage: string;
  availableLanguages: Language[];
  setLanguage: (langCode: string) => void;
  t: (key: string, fallback?: string) => string;
  isRTL: boolean;
  loading: boolean;
}

const LanguageContext = createContext<TranslationContextType | undefined>(undefined);

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/backend';

export function LanguageProvider({ children }: { children: ReactNode }) {
  const [currentLanguage, setCurrentLanguage] = useState<string>('en');
  const [availableLanguages, setAvailableLanguages] = useState<Language[]>([]);
  const [translations, setTranslations] = useState<Record<string, string>>({});
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const savedLang = localStorage.getItem('preferred_language') || navigator.language.split('-')[0] || 'en';
    setCurrentLanguage(savedLang);

    fetchLanguages();
  }, []);

  useEffect(() => {
    if (currentLanguage) {
      fetchTranslations(currentLanguage);
      document.documentElement.lang = currentLanguage;

      const isRTL = availableLanguages.find(l => l.lang_code === currentLanguage)?.rtl || false;
      document.documentElement.dir = isRTL ? 'rtl' : 'ltr';
    }
  }, [currentLanguage, availableLanguages]);

  const fetchLanguages = async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/api/translations.php/languages?active_only=true`);
      const data = await response.json();

      if (data.success && data.languages) {
        setAvailableLanguages(data.languages);
      }
    } catch (error) {
      console.error('Failed to fetch languages:', error);
    }
  };

  const fetchTranslations = async (langCode: string) => {
    setLoading(true);
    try {
      const response = await fetch(
        `${API_BASE_URL}/api/translations.php/batch?content_type=ui_string&content_id=null&lang_code=${langCode}`
      );
      const data = await response.json();

      if (data.success && data.translations) {
        const translationMap: Record<string, string> = {};
        Object.keys(data.translations).forEach((key) => {
          translationMap[key] = data.translations[key].text;
        });
        setTranslations(translationMap);
      }
    } catch (error) {
      console.error('Failed to fetch translations:', error);
    } finally {
      setLoading(false);
    }
  };

  const setLanguage = (langCode: string) => {
    setCurrentLanguage(langCode);
    localStorage.setItem('preferred_language', langCode);

    document.cookie = `language=${langCode}; path=/; max-age=31536000; SameSite=Lax`;
  };

  const t = (key: string, fallback?: string): string => {
    return translations[key] || fallback || key;
  };

  const isRTL = availableLanguages.find(l => l.lang_code === currentLanguage)?.rtl || false;

  return (
    <LanguageContext.Provider
      value={{
        currentLanguage,
        availableLanguages,
        setLanguage,
        t,
        isRTL,
        loading,
      }}
    >
      {children}
    </LanguageContext.Provider>
  );
}

export function useLanguage() {
  const context = useContext(LanguageContext);
  if (context === undefined) {
    throw new Error('useLanguage must be used within a LanguageProvider');
  }
  return context;
}
