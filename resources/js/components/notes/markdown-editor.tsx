import MDEditor from '@uiw/react-md-editor';
import { useAppearance } from '@/hooks/use-appearance';

import '@uiw/react-md-editor/markdown-editor.css';

/**
 * REFERENCE MODULE — markdown editor for Notes. Delete with Notes when unused.
 */
export default function MarkdownEditor({
    id,
    name = 'body',
    value,
    onChange,
}: {
    id?: string;
    name?: string;
    value: string;
    onChange: (value: string) => void;
}) {
    const { resolvedAppearance } = useAppearance();
    const colorMode = resolvedAppearance;

    return (
        <div
            data-color-mode={colorMode}
            className="overflow-hidden rounded-lg border"
        >
            <input type="hidden" name={name} value={value} />
            <MDEditor
                id={id}
                value={value}
                onChange={(next) => onChange(next ?? '')}
                height={320}
                preview="edit"
                visibleDragbar={false}
                textareaProps={{
                    'aria-label': 'Note body',
                    name: `${name}_editor`,
                }}
            />
        </div>
    );
}
