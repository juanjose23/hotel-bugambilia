import AboutGallery from '@/modules/acerca-de/components/AboutGallery';
import AboutHero from '@/modules/acerca-de/components/AboutHero';
import AboutStory from '@/modules/acerca-de/components/AboutStory';
import AboutValues from '@/modules/acerca-de/components/AboutValues';
import LayoutPublico from '@/modules/shared/layouts/LayoutPublico';

export default function AcercaDe() {
    return (
        <LayoutPublico>
            <AboutHero />
            <AboutStory />
            <AboutValues />
            <AboutGallery />
        </LayoutPublico>
    );
}
