import { Head } from '@inertiajs/react';

type Props = {
    title: string;
    description?: string;
};

/**
 * Standard page head. The app name is appended to <title> automatically by
 * the title callback in app.tsx. Adds meta description and Open Graph tags.
 *
 * Convention: every page renders <Seo>; never use raw <Head> for titles.
 */
export default function Seo({ title, description }: Props) {
    return (
        <Head title={title}>
            <meta property="og:title" content={title} />
            {description ? (
                <>
                    <meta name="description" content={description} />
                    <meta property="og:description" content={description} />
                </>
            ) : null}
        </Head>
    );
}
