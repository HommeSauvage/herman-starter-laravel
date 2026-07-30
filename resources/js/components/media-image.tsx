import { ImageIcon } from 'lucide-react';
import { useState } from 'react';
import { cn } from '@/lib/utils';

const ratios = {
    square: 'aspect-square',
    video: 'aspect-video',
    wide: 'aspect-[2/1]',
} as const;

type Props = {
    src?: string | null;
    alt?: string;
    ratio?: keyof typeof ratios;
    className?: string;
};

/**
 * Fixed-aspect media container: `object-cover`, lazy loading, and a
 * token-styled, zero-network inline placeholder when `src` is missing
 * or fails to load.
 */
export default function MediaImage({
    src,
    alt = '',
    ratio = 'video',
    className,
}: Props) {
    const [errored, setErrored] = useState(false);

    return (
        <div
            className={cn(
                'relative overflow-hidden bg-muted',
                ratios[ratio],
                className,
            )}
        >
            {src && !errored ? (
                <img
                    src={src}
                    alt={alt}
                    loading="lazy"
                    onError={() => setErrored(true)}
                    className="absolute inset-0 h-full w-full object-cover"
                />
            ) : (
                <div
                    className="absolute inset-0 flex items-center justify-center text-muted-foreground/40"
                    role="img"
                    aria-label={alt || 'Image placeholder'}
                >
                    <ImageIcon className="size-10" aria-hidden />
                </div>
            )}
        </div>
    );
}
