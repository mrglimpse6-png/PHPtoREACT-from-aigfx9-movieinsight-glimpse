/**
 * Analytics Consent Modal Component
 * GDPR-compliant analytics opt-in modal
 */

import { useState, useEffect } from 'react';
import { Shield, Cookie, Settings2, Check, X as XIcon } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { enableAnalytics, disableAnalytics, hasAnalyticsConsent } from '@/utils/analytics';
import { motion, AnimatePresence } from 'framer-motion';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Switch } from "@/components/ui/switch";
import { Label } from "@/components/ui/label";

const CONSENT_SHOWN_KEY = 'analytics_consent_shown';

export function AnalyticsConsentModal() {
  const [isVisible, setIsVisible] = useState(false);
  const [showCustomize, setShowCustomize] = useState(false);
  const [preferences, setPreferences] = useState({
    necessary: true,
    analytics: true,
    marketing: false,
  });

  useEffect(() => {
    checkConsentStatus();
  }, []);

  const checkConsentStatus = () => {
    const hasConsent = hasAnalyticsConsent();
    const hasBeenShown = localStorage.getItem(CONSENT_SHOWN_KEY);

    if (!hasConsent && !hasBeenShown) {
      setTimeout(() => {
        setIsVisible(true);
      }, 2000);
    }
  };

  const handleAcceptAll = () => {
    enableAnalytics();
    localStorage.setItem(CONSENT_SHOWN_KEY, 'true');
    setIsVisible(false);
  };

  const handleDenyAll = () => {
    disableAnalytics();
    localStorage.setItem(CONSENT_SHOWN_KEY, 'true');
    setIsVisible(false);
  };

  const handleSavePreferences = () => {
    if (preferences.analytics) {
      enableAnalytics();
    } else {
      disableAnalytics();
    }
    localStorage.setItem(CONSENT_SHOWN_KEY, 'true');
    setShowCustomize(false);
    setIsVisible(false);
  };

  return (
    <>
      <AnimatePresence>
        {isVisible && (
          <motion.div
            initial={{ y: 100, opacity: 0 }}
            animate={{ y: 0, opacity: 1 }}
            exit={{ y: 100, opacity: 0 }}
            transition={{ type: "spring", damping: 25, stiffness: 300 }}
            className="fixed bottom-0 left-0 right-0 z-50 p-4 md:bottom-6 md:left-6 md:right-auto md:max-w-lg"
          >
            <div className="bg-card border border-border rounded-2xl shadow-2xl overflow-hidden backdrop-blur-xl">
              <div className="bg-gradient-to-r from-youtube-red/10 to-transparent p-6 pb-4">
                <div className="flex items-start gap-4">
                  <div className="w-12 h-12 bg-white dark:bg-slate-900 rounded-xl flex items-center justify-center flex-shrink-0 shadow-md">
                    <Cookie className="h-6 w-6 text-youtube-red" />
                  </div>

                  <div className="flex-1">
                    <h3 className="font-bold text-lg text-foreground mb-1.5">
                      Cookie Preferences
                    </h3>
                    <p className="text-sm text-muted-foreground leading-relaxed">
                      We use cookies to enhance your browsing experience and analyze site traffic. Choose your preferences below.
                    </p>
                  </div>
                </div>
              </div>

              <div className="px-6 py-4">
                <div className="flex items-center gap-2 text-xs text-muted-foreground mb-4">
                  <Shield className="h-3.5 w-3.5 text-green-500" />
                  <span>Your privacy matters. No data is sold to third parties.</span>
                </div>

                <div className="flex flex-col gap-2">
                  <Button
                    onClick={handleAcceptAll}
                    className="w-full bg-gradient-youtube hover:shadow-glow transition-all duration-300 font-semibold h-11"
                  >
                    <Check className="h-4 w-4 mr-2" />
                    Accept All
                  </Button>

                  <div className="grid grid-cols-2 gap-2">
                    <Button
                      onClick={handleDenyAll}
                      variant="outline"
                      className="border-border hover:bg-muted font-medium h-10"
                    >
                      <XIcon className="h-3.5 w-3.5 mr-1.5" />
                      Deny All
                    </Button>
                    <Button
                      onClick={() => setShowCustomize(true)}
                      variant="outline"
                      className="border-border hover:bg-muted font-medium h-10"
                    >
                      <Settings2 className="h-3.5 w-3.5 mr-1.5" />
                      Customize
                    </Button>
                  </div>
                </div>

                <p className="text-[10px] text-center text-muted-foreground mt-3 leading-relaxed">
                  By accepting, you agree to our use of cookies as described in our Privacy Policy.
                </p>
              </div>
            </div>
          </motion.div>
        )}
      </AnimatePresence>

      <Dialog open={showCustomize} onOpenChange={setShowCustomize}>
        <DialogContent className="sm:max-w-md">
          <DialogHeader>
            <DialogTitle className="flex items-center gap-2">
              <Settings2 className="h-5 w-5 text-youtube-red" />
              Customize Cookie Preferences
            </DialogTitle>
            <DialogDescription>
              Manage your cookie settings. Essential cookies cannot be disabled as they are required for the site to function.
            </DialogDescription>
          </DialogHeader>

          <div className="space-y-6 py-4">
            <div className="flex items-center justify-between space-x-4 p-4 rounded-lg bg-muted/50">
              <div className="flex-1">
                <Label htmlFor="necessary" className="font-semibold text-sm">
                  Essential Cookies
                </Label>
                <p className="text-xs text-muted-foreground mt-1">
                  Required for core site functionality. Always enabled.
                </p>
              </div>
              <Switch
                id="necessary"
                checked={preferences.necessary}
                disabled
                className="data-[state=checked]:bg-green-500"
              />
            </div>

            <div className="flex items-center justify-between space-x-4 p-4 rounded-lg bg-muted/50">
              <div className="flex-1">
                <Label htmlFor="analytics" className="font-semibold text-sm">
                  Analytics Cookies
                </Label>
                <p className="text-xs text-muted-foreground mt-1">
                  Help us understand how visitors use our site.
                </p>
              </div>
              <Switch
                id="analytics"
                checked={preferences.analytics}
                onCheckedChange={(checked) =>
                  setPreferences({ ...preferences, analytics: checked })
                }
              />
            </div>

            <div className="flex items-center justify-between space-x-4 p-4 rounded-lg bg-muted/50">
              <div className="flex-1">
                <Label htmlFor="marketing" className="font-semibold text-sm">
                  Marketing Cookies
                </Label>
                <p className="text-xs text-muted-foreground mt-1">
                  Used to deliver personalized content and ads.
                </p>
              </div>
              <Switch
                id="marketing"
                checked={preferences.marketing}
                onCheckedChange={(checked) =>
                  setPreferences({ ...preferences, marketing: checked })
                }
              />
            </div>
          </div>

          <div className="flex gap-2">
            <Button
              onClick={handleSavePreferences}
              className="flex-1 bg-gradient-youtube hover:shadow-glow"
            >
              Save Preferences
            </Button>
            <Button
              onClick={() => setShowCustomize(false)}
              variant="outline"
            >
              Cancel
            </Button>
          </div>
        </DialogContent>
      </Dialog>
    </>
  );
}
