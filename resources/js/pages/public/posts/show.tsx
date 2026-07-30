import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import MarkdownBody from '@/components/markdown-body';
import MediaImage from '@/components/media-image';
import Seo from '@/components/seo';
import { Button } from '@/components/ui/button';
import { index } from '@/routes/posts';

type Post = {
    title: string;
    slug: string;
    excerpt: string;
    body: string;
    image_path: string | null;
    published_at: string | null;
};

/**
 * REFERENCE MODULE — public Posts detail (markdown body, slug routing,
 * 404 for unpublished). Delete the posts feature when unused.
 */
export default function PostsShow({ post }: { post: Post }) {
    return (
        <>
            <Seo title={post.title} description={post.excerpt} />

            <article className="mx-auto flex w-full max-w-3xl flex-col gap-8 px-4 py-12 md:px-6 md:py-16">
                <Button
                    variant="ghost"
                    size="sm"
                    asChild
                    className="self-start"
                >
                    <Link href={index()}>
                        <ArrowLeft /> All posts
                    </Link>
                </Button>

                <header className="flex flex-col gap-4">
                    {post.published_at ? (
                        <p className="text-sm text-muted-foreground">
                            {new Date(post.published_at).toLocaleDateString(
                                undefined,
                                {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                },
                            )}
                        </p>
                    ) : null}
                    <h1 className="text-3xl font-semibold tracking-tight text-balance md:text-4xl">
                        {post.title}
                    </h1>
                    <p className="text-lg text-pretty text-muted-foreground">
                        {post.excerpt}
                    </p>
                </header>

                <MediaImage
                    src={post.image_path}
                    alt={post.title}
                    ratio="wide"
                    className="rounded-xl"
                />

                <MarkdownBody content={post.body} />
            </article>
        </>
    );
}
