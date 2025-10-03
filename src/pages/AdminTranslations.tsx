import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useToast } from '@/hooks/use-toast';
import { Globe, RefreshCw, Save, Languages, TrendingUp, CircleCheck as CheckCircle } from 'lucide-react';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/backend';

interface Language {
  lang_code: string;
  lang_name: string;
  native_name: string;
  flag_icon: string;
  active: boolean;
}

interface Translation {
  id: number;
  content_type: string;
  content_id: number | null;
  field_name: string;
  lang_code: string;
  original_text: string;
  translated_text: string;
  manual_override: boolean;
  translation_method: string;
  last_updated: string;
}

interface TranslationStat {
  lang_code: string;
  lang_name: string;
  native_name: string;
  total_translations: number;
  manual_overrides: number;
  avg_completion: number;
}

export default function AdminTranslations() {
  const { toast } = useToast();
  const [languages, setLanguages] = useState<Language[]>([]);
  const [selectedLang, setSelectedLang] = useState<string>('es');
  const [translations, setTranslations] = useState<Translation[]>([]);
  const [stats, setStats] = useState<TranslationStat[]>([]);
  const [loading, setLoading] = useState(false);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [editedText, setEditedText] = useState('');
  const [contentTypeFilter, setContentTypeFilter] = useState<string>('all');
  const [bulkTranslating, setBulkTranslating] = useState(false);

  useEffect(() => {
    fetchLanguages();
    fetchStats();
  }, []);

  useEffect(() => {
    if (selectedLang) {
      fetchTranslations();
    }
  }, [selectedLang, contentTypeFilter]);

  const fetchLanguages = async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/api/translations.php/languages?active_only=false`);
      const data = await response.json();
      if (data.success) {
        setLanguages(data.languages);
      }
    } catch (error) {
      console.error('Failed to fetch languages:', error);
    }
  };

  const fetchTranslations = async () => {
    setLoading(true);
    try {
      const token = localStorage.getItem('auth_token');
      const url = new URL(`${API_BASE_URL}/api/admin/translations.php`);
      url.searchParams.append('lang_code', selectedLang);
      if (contentTypeFilter !== 'all') {
        url.searchParams.append('content_type', contentTypeFilter);
      }

      const response = await fetch(url.toString(), {
        headers: {
          'Authorization': `Bearer ${token}`,
        },
      });

      const data = await response.json();
      if (data.success) {
        setTranslations(data.data);
      }
    } catch (error) {
      console.error('Failed to fetch translations:', error);
      toast({
        title: 'Error',
        description: 'Failed to load translations',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  const fetchStats = async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/api/translations.php/stats`);
      const data = await response.json();
      if (data.success) {
        setStats(data.stats);
      }
    } catch (error) {
      console.error('Failed to fetch stats:', error);
    }
  };

  const handleBulkTranslate = async (contentType: string) => {
    setBulkTranslating(true);
    try {
      const token = localStorage.getItem('auth_token');
      const response = await fetch(`${API_BASE_URL}/api/translations.php`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          action: 'bulk_translate',
          content_type: contentType,
          target_lang: selectedLang,
          limit: 100,
        }),
      });

      const data = await response.json();
      if (data.success) {
        toast({
          title: 'Success',
          description: `Translated ${data.result.translated} items`,
        });
        fetchTranslations();
        fetchStats();
      }
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to run bulk translation',
        variant: 'destructive',
      });
    } finally {
      setBulkTranslating(false);
    }
  };

  const handleSaveTranslation = async (translation: Translation) => {
    try {
      const token = localStorage.getItem('auth_token');
      const response = await fetch(`${API_BASE_URL}/api/admin/translations.php`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          id: translation.id,
          content_type: translation.content_type,
          content_id: translation.content_id,
          field_name: translation.field_name,
          lang_code: translation.lang_code,
          original_text: translation.original_text,
          translated_text: editedText || translation.translated_text,
        }),
      });

      const data = await response.json();
      if (data.success) {
        toast({
          title: 'Success',
          description: 'Translation updated successfully',
        });
        setEditingId(null);
        setEditedText('');
        fetchTranslations();
        fetchStats();
      }
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to update translation',
        variant: 'destructive',
      });
    }
  };

  const contentTypes = ['all', 'blog', 'testimonial', 'service', 'portfolio', 'ui_string', 'page_section'];

  return (
    <div className="container mx-auto px-4 py-8 mt-16">
      <div className="mb-8">
        <h1 className="text-3xl font-bold mb-2 flex items-center gap-2">
          <Globe className="h-8 w-8 text-youtube-red" />
          Translation Management System
        </h1>
        <p className="text-muted-foreground">
          Manage multilingual content with auto-translation and manual overrides
        </p>
      </div>

      <Tabs defaultValue="overview" className="space-y-6">
        <TabsList>
          <TabsTrigger value="overview">Overview</TabsTrigger>
          <TabsTrigger value="manage">Manage Translations</TabsTrigger>
          <TabsTrigger value="bulk">Bulk Operations</TabsTrigger>
        </TabsList>

        <TabsContent value="overview" className="space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {stats.map((stat) => (
              <Card key={stat.lang_code}>
                <CardHeader className="pb-3">
                  <CardTitle className="text-lg flex items-center justify-between">
                    {stat.native_name}
                    <Badge variant={stat.avg_completion > 70 ? 'default' : 'secondary'}>
                      {Math.round(stat.avg_completion)}%
                    </Badge>
                  </CardTitle>
                  <CardDescription>{stat.lang_name}</CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="space-y-2 text-sm">
                    <div className="flex justify-between">
                      <span className="text-muted-foreground">Total:</span>
                      <span className="font-medium">{stat.total_translations}</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-muted-foreground">Manual:</span>
                      <span className="font-medium">{stat.manual_overrides}</span>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        </TabsContent>

        <TabsContent value="manage" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Translation Manager</CardTitle>
              <CardDescription>
                View and edit translations for specific languages
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex flex-wrap gap-4">
                <div className="flex-1 min-w-[200px]">
                  <Select value={selectedLang} onValueChange={setSelectedLang}>
                    <SelectTrigger>
                      <SelectValue placeholder="Select language" />
                    </SelectTrigger>
                    <SelectContent>
                      {languages
                        .filter(l => l.lang_code !== 'en')
                        .map((lang) => (
                          <SelectItem key={lang.lang_code} value={lang.lang_code}>
                            {lang.flag_icon} {lang.native_name}
                          </SelectItem>
                        ))}
                    </SelectContent>
                  </Select>
                </div>

                <div className="flex-1 min-w-[200px]">
                  <Select value={contentTypeFilter} onValueChange={setContentTypeFilter}>
                    <SelectTrigger>
                      <SelectValue placeholder="Filter by type" />
                    </SelectTrigger>
                    <SelectContent>
                      {contentTypes.map((type) => (
                        <SelectItem key={type} value={type}>
                          {type === 'all' ? 'All Types' : type}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <Button onClick={fetchTranslations} disabled={loading}>
                  <RefreshCw className="mr-2 h-4 w-4" />
                  Refresh
                </Button>
              </div>

              <div className="space-y-4">
                {loading ? (
                  <div className="text-center py-8 text-muted-foreground">
                    Loading translations...
                  </div>
                ) : translations.length === 0 ? (
                  <div className="text-center py-8 text-muted-foreground">
                    No translations found
                  </div>
                ) : (
                  translations.map((translation) => (
                    <Card key={translation.id}>
                      <CardContent className="pt-6">
                        <div className="space-y-3">
                          <div className="flex items-start justify-between">
                            <div className="space-y-1">
                              <Badge variant="outline">{translation.content_type}</Badge>
                              <Badge variant="secondary" className="ml-2">
                                {translation.field_name}
                              </Badge>
                              {translation.manual_override && (
                                <Badge className="ml-2">Manual</Badge>
                              )}
                            </div>
                            <span className="text-xs text-muted-foreground">
                              {new Date(translation.last_updated).toLocaleDateString()}
                            </span>
                          </div>

                          <div className="grid grid-cols-1 gap-4">
                            <div>
                              <label className="text-sm font-medium text-muted-foreground">
                                Original (English)
                              </label>
                              <p className="mt-1 text-sm bg-muted p-3 rounded">
                                {translation.original_text}
                              </p>
                            </div>

                            <div>
                              <label className="text-sm font-medium text-muted-foreground">
                                Translation ({selectedLang})
                              </label>
                              {editingId === translation.id ? (
                                <Textarea
                                  value={editedText || translation.translated_text}
                                  onChange={(e) => setEditedText(e.target.value)}
                                  className="mt-1"
                                  rows={3}
                                />
                              ) : (
                                <p className="mt-1 text-sm bg-muted p-3 rounded">
                                  {translation.translated_text}
                                </p>
                              )}
                            </div>
                          </div>

                          <div className="flex justify-end gap-2">
                            {editingId === translation.id ? (
                              <>
                                <Button
                                  variant="outline"
                                  size="sm"
                                  onClick={() => {
                                    setEditingId(null);
                                    setEditedText('');
                                  }}
                                >
                                  Cancel
                                </Button>
                                <Button
                                  size="sm"
                                  onClick={() => handleSaveTranslation(translation)}
                                >
                                  <Save className="mr-2 h-4 w-4" />
                                  Save
                                </Button>
                              </>
                            ) : (
                              <Button
                                variant="outline"
                                size="sm"
                                onClick={() => {
                                  setEditingId(translation.id);
                                  setEditedText(translation.translated_text);
                                }}
                              >
                                Edit
                              </Button>
                            )}
                          </div>
                        </div>
                      </CardContent>
                    </Card>
                  ))
                )}
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="bulk" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Languages className="h-5 w-5" />
                Bulk Translation Operations
              </CardTitle>
              <CardDescription>
                Auto-translate missing content for selected language
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex gap-4">
                <div className="flex-1">
                  <Select value={selectedLang} onValueChange={setSelectedLang}>
                    <SelectTrigger>
                      <SelectValue placeholder="Target language" />
                    </SelectTrigger>
                    <SelectContent>
                      {languages
                        .filter(l => l.lang_code !== 'en')
                        .map((lang) => (
                          <SelectItem key={lang.lang_code} value={lang.lang_code}>
                            {lang.flag_icon} {lang.native_name}
                          </SelectItem>
                        ))}
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {['blog', 'service', 'portfolio', 'testimonial', 'ui_string'].map((type) => (
                  <Card key={type}>
                    <CardContent className="pt-6">
                      <div className="flex items-center justify-between">
                        <div>
                          <h3 className="font-medium capitalize">{type}</h3>
                          <p className="text-sm text-muted-foreground">
                            Auto-translate all {type} content
                          </p>
                        </div>
                        <Button
                          onClick={() => handleBulkTranslate(type)}
                          disabled={bulkTranslating}
                          size="sm"
                        >
                          <TrendingUp className="mr-2 h-4 w-4" />
                          Translate
                        </Button>
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </div>

              <div className="bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-900 rounded-lg p-4">
                <div className="flex gap-3">
                  <CheckCircle className="h-5 w-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                  <div className="text-sm text-blue-900 dark:text-blue-100">
                    <p className="font-medium mb-1">Auto-Translation Notes:</p>
                    <ul className="list-disc list-inside space-y-1 text-blue-800 dark:text-blue-200">
                      <li>Uses Google Translate API for automatic translations</li>
                      <li>Only translates missing items (won't overwrite existing)</li>
                      <li>Results are cached to reduce API costs</li>
                      <li>Manual overrides always take precedence</li>
                      <li>Review and edit translations as needed</li>
                    </ul>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
}
