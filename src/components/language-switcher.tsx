import { Globe } from 'lucide-react';
import { Button } from './ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from './ui/dropdown-menu';
import { useLanguage } from '@/contexts/LanguageContext';

export function LanguageSwitcher() {
  const { currentLanguage, availableLanguages, setLanguage } = useLanguage();

  const currentLang = availableLanguages.find(l => l.lang_code === currentLanguage);

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button variant="ghost" size="sm" className="gap-2">
          <Globe className="h-4 w-4" />
          <span className="hidden sm:inline">
            {currentLang?.native_name || 'Language'}
          </span>
          <span className="sm:hidden text-lg">
            {currentLang?.flag_icon || '🌐'}
          </span>
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="w-48">
        {availableLanguages.map((lang) => (
          <DropdownMenuItem
            key={lang.lang_code}
            onClick={() => setLanguage(lang.lang_code)}
            className={`cursor-pointer ${
              currentLanguage === lang.lang_code ? 'bg-accent' : ''
            }`}
          >
            <span className="mr-2 text-lg">{lang.flag_icon}</span>
            <span className="flex-1">{lang.native_name}</span>
            {lang.lang_code === currentLanguage && (
              <span className="text-xs text-muted-foreground">✓</span>
            )}
          </DropdownMenuItem>
        ))}
      </DropdownMenuContent>
    </DropdownMenu>
  );
}
