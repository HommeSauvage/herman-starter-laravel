import { usePage } from '@inertiajs/react';
import Pagination from '@/components/pagination';
import CardGrid from '@/components/sections/card-grid';
import PageHeader from '@/components/sections/page-header';
import Seo from '@/components/seo';
import { show } from '@/routes/posts';
import type { PaginatedData } from '@/types';

type PostCard = {
    title: string;
    slug: string;
    excerpt: string;
    image_path: string | null;
    published_at: string | null;
};

/**
 * REFERENCE MODULE — public Posts index (card grid + pagination).
 * Delete the posts feature when unused.
 */
export default function PostsIndex({
    posts,
}: {
    posts: PaginatedData<PostCard>;
}) {
    const { name } = usePage().props;

    return (
        <>
            <Seo
                title="Blog"
                description={`Guides, release notes, and lessons from the ${name} team.`}
            />

            <PageHeader
                eyebrow="Blog"
                title="From the blog"
                lede={`Guides, release notes, and lessons from the ${name} team.`}
                className="pb-10"
            />

            <CardGrid
                className="py-0"
                items={posts.data.map((post) => ({
                    title: post.title,
                    excerpt: post.excerpt,
                    image: post.image_path,
                    href: show(post.slug),
                    meta: post.published_at
                        ? new Date(post.published_at).toLocaleDateString(
                              undefined,
                              {
                                  year: 'numeric',
                                  month: 'long',
                                  day: 'numeric',
                              },
                          )
                        : null,
                }))}
            />

            <div className="mx-auto w-full max-w-6xl px-4 pb-16 md:px-6">
                <Pagination pagination={posts} />
            </div>
        </>
    );
}
