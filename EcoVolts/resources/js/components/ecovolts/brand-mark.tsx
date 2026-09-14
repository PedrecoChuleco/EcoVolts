import { cn } from '@/lib/utils';
import logo from '@/assets/brand-small.png'

export function BrandMark({ className }: { className?: string }) {
    return (
        <div
            aria-hidden
            className={cn('w-[55px] h-[48px] shrink-1', className)}
            style={{
                backgroundImage: `url(${logo})`,
                backgroundSize: 'cover',
                backgroundPosition: 'center',
            }}
        />
    );
}

export function RoofTick() {
    return <span aria-hidden className="inline-block size-0 border-x-[6px] border-b-[8px] border-x-transparent border-b-eco-amber" />;
}
