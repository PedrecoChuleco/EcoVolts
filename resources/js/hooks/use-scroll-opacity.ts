import { useEffect, useState } from 'react';

export function useScrollOpacity(maxScroll = 160) {
    const [opacity, setOpacity] = useState(0);

    useEffect(() => {
        const handleScroll = () => {
            const progress = Math.min(window.scrollY / maxScroll, 1);
            setOpacity(progress);
        };

        handleScroll();
        window.addEventListener('scroll', handleScroll, { passive: true });
        return () => window.removeEventListener('scroll', handleScroll);
    }, [maxScroll]);

    return opacity;
}