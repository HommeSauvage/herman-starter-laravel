import ReactMarkdown from 'react-markdown';

/**
 * REFERENCE MODULE — delete with Notes when unused.
 */
export default function MarkdownBody({ content }: { content: string }) {
    return (
        <div className="note-markdown space-y-4 text-base leading-relaxed [&_a]:text-primary [&_a]:underline [&_blockquote]:border-l-2 [&_blockquote]:border-muted-foreground/40 [&_blockquote]:pl-4 [&_blockquote]:text-muted-foreground [&_code]:rounded [&_code]:bg-muted [&_code]:px-1 [&_code]:py-0.5 [&_h1]:text-3xl [&_h1]:font-semibold [&_h2]:text-2xl [&_h2]:font-semibold [&_h3]:text-xl [&_h3]:font-medium [&_li]:ml-5 [&_ol]:list-decimal [&_p]:text-foreground/90 [&_pre]:overflow-x-auto [&_pre]:rounded-lg [&_pre]:bg-muted [&_pre]:p-4 [&_ul]:list-disc">
            <ReactMarkdown>{content}</ReactMarkdown>
        </div>
    );
}
