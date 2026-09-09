import { cn } from '@/lib/utils';

export function BrandMark({ className }: { className?: string }) {
    return (
        <div
            aria-hidden
            className={cn('size-[30px] shrink-0 rounded-[4px] bg-eco-ink', className)}
            style={{
                backgroundImage:
                    'repeating-linear-gradient(0deg, transparent 0 5px, rgba(232,163,61,0.55) 5px 6px), repeating-linear-gradient(90deg, transparent 0 5px, rgba(232,163,61,0.55) 5px 6px)',
            }}
        />
    );
}

export function RoofTick() {
    return <span aria-hidden className="inline-block size-0 border-x-[6px] border-b-[8px] border-x-transparent border-b-eco-amber" />;
}
