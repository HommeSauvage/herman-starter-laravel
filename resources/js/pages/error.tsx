import { Link, usePage } from '@inertiajs/react';
import Seo from '@/components/seo';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import PublicLayout from '@/layouts/public-layout';
import { home } from '@/routes';

const messages: Record<number, { title: string; description: string }> = {
    403: {
        title: 'Forbidden',
        description: 'You do not have permission to view this page.',
    },
    404: {
        title: 'Page not found',
        description:
            'The page you are looking for does not exist or has moved.',
    },
    500: {
        title: 'Something went wrong',
        description: 'An unexpected error occurred. Please try again.',
    },
    503: {
        title: 'Service unavailable',
        description: 'We are performing maintenance. Please check back soon.',
    },
};

/**
 * Friendly error page for 403/404/500 responses (see bootstrap/app.php).
 * Guests get the public chrome; signed-in users stay in the app shell.
 * The page opts out of the automatic layout in app.tsx and picks its own.
 */
export default function ErrorPage({ status }: { status: number }) {
    const { auth } = usePage().props;

    const message = messages[status] ?? {
        title: 'Error',
        description: 'An unexpected error occurred.',
    };

    const content = (
        <>
            <Seo title={`${status} — ${message.title}`} />
            <div className="flex flex-1 flex-col items-center justify-center gap-6 px-4 py-24 text-center">
                <p className="text-6xl font-semibold tracking-tight text-muted-foreground/60">
                    {status}
                </p>
                <div className="space-y-2">
                    <h1 className="text-2xl font-semibold tracking-tight">
                        {message.title}
                    </h1>
                    <p className="max-w-md text-muted-foreground">
                        {message.description}
                    </p>
                </div>
                <Button asChild>
                    <Link href={home()}>Back to home</Link>
                </Button>
            </div>
        </>
    );

    return auth.user ? (
        <AppLayout>{content}</AppLayout>
    ) : (
        <PublicLayout>{content}</PublicLayout>
    );
}
