import { HeroSection } from "@/components/hero-section"
import { PortfolioHighlights } from "@/components/portfolio-highlights"
import { ServicesOverview } from "@/components/services-overview"
import { WhyChooseSection } from "@/components/why-choose-section"
import { TestimonialsSection } from "@/components/testimonials-section"
import { LeadMagnet } from "@/components/lead-magnet"
import { PricingEstimator } from "@/components/pricing-estimator"
import { CalendlyBooking } from "@/components/calendly-booking"
import { SEOHead } from "@/components/seo-head"
import { PopupOffer } from "@/components/popup-offer"

export default function Home() {
  return (
    <>
      <SEOHead
        title="Adil GFX - Professional Logo Design, YouTube Thumbnails & Video Editing"
        description="Transform your brand with premium logo design, high-converting YouTube thumbnails, and professional video editing. Trusted by 500+ clients worldwide. Get results in 24-48 hours."
        keywords="logo design, youtube thumbnails, video editing, brand identity, graphic design, youtube optimization, channel setup, adil gfx"
        url="https://adilgfx.com"
      />
      <PopupOffer variant="first-time" />
      <main className="pt-16">
      <HeroSection />
      
      {/* Lead Magnet Banner */}
      <section className="py-8 px-4 sm:px-6 lg:px-8">
        <div className="max-w-7xl mx-auto">
          <LeadMagnet 
            variant="banner"
            title="🎁 5 Free YouTube Thumbnail Templates"
            description="Professional templates that have generated millions of views. Get instant access!"
          />
        </div>
      </section>
      
      <PortfolioHighlights />
      <ServicesOverview />
      
      {/* Pricing Estimator Section */}
      <section className="py-16 px-4 sm:px-6 lg:px-8">
        <div className="max-w-4xl mx-auto">
          <h2 className="text-3xl font-bold text-center text-foreground mb-8">
            Calculate Your <span className="text-gradient-youtube">Project Cost</span>
          </h2>
          <PricingEstimator />
        </div>
      </section>
      
      <WhyChooseSection />
      <TestimonialsSection />
      
      {/* Calendly Booking Section */}
      <section className="py-16 px-4 sm:px-6 lg:px-8 bg-gradient-subtle">
        <div className="max-w-4xl mx-auto">
          <CalendlyBooking variant="inline" />
        </div>
      </section>
    </main>
    </>
  )
}