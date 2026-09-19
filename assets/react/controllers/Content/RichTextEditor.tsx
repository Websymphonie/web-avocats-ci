import React, {useEffect, useMemo, useRef, useState} from "react";
import {EditorContent, useEditor} from "@tiptap/react";
import StarterKit from "@tiptap/starter-kit";
import Link from "@tiptap/extension-link";
import Placeholder from "@tiptap/extension-placeholder";
import Underline from "@tiptap/extension-underline";
import {Bold, Italic, Link as LinkIcon, List, ListOrdered, Quote, Redo2, Strikethrough, Underline as UnderlineIcon, Undo2, Unlink} from "lucide-react";

type Props = {
    inputId: string;
    value?: string;
    placeholder?: string;
    label?: string;
    invalid?: boolean;
    disabled?: boolean;
    readOnly?: boolean;
};

type ToolbarButtonProps = {
    label: string;
    pressed?: boolean;
    disabled?: boolean;
    onClick: () => void;
    children: React.ReactNode;
};

const ToolbarButton = ({label, pressed = false, disabled = false, onClick, children}: ToolbarButtonProps): JSX.Element => (
    <button
        type="button"
        title={label}
        aria-label={label}
        aria-pressed={pressed}
        disabled={disabled}
        onMouseDown={(event) => event.preventDefault()}
        onClick={onClick}
        className="inline-flex size-9 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-40"
    >
        {children}
    </button>
);

const normalizeUrl = (value: string): string | null => {
    const url = value.trim();
    if (url === "" || /^\s*(javascript|data|vbscript):/i.test(url)) return null;
    if (/^(https?|mailto|tel):/i.test(url) || url.startsWith("/") || url.startsWith("#")) return url;
    return `https://${url}`;
};

const RichTextEditor: React.FC<Props> = ({inputId, value = "", placeholder = "Commencez à rédiger le contenu…", label = "Contenu", invalid = false, disabled = false, readOnly = false}) => {
    const inputRef = useRef<HTMLTextAreaElement | null>(null);
    const [linkPanelOpen, setLinkPanelOpen] = useState(false);
    const [linkText, setLinkText] = useState("");
    const [linkUrl, setLinkUrl] = useState("");
    const initialValue = useMemo(() => value, [value]);
    const editor = useEditor({
        extensions: [
            StarterKit.configure({heading: {levels: [2, 3]}}),
            Underline,
            Link.configure({openOnClick: false, autolink: false, linkOnPaste: true}),
            Placeholder.configure({placeholder}),
        ],
        editable: !disabled && !readOnly,
        content: initialValue,
        editorProps: {
            attributes: {
                class: "rich-content min-h-72 w-full px-4 py-3 outline-none",
                spellcheck: "true",
                role: "textbox",
                "aria-label": label,
                "aria-multiline": "true",
                "aria-invalid": invalid ? "true" : "false",
                "aria-readonly": readOnly ? "true" : "false",
                "aria-disabled": disabled ? "true" : "false",
            },
        },
        onUpdate: ({editor: currentEditor}) => {
            if (inputRef.current) inputRef.current.value = currentEditor.getHTML();
        },
    });

    useEffect(() => {
        inputRef.current = document.getElementById(inputId) as HTMLTextAreaElement | null;
        if (inputRef.current && editor && inputRef.current.value === "") inputRef.current.value = editor.getHTML();
        const form = inputRef.current?.form;
        const sync = (): void => { if (inputRef.current && editor) inputRef.current.value = editor.getHTML(); };
        form?.addEventListener("submit", sync);
        return () => form?.removeEventListener("submit", sync);
    }, [editor, inputId]);

    useEffect(() => {
        if (!editor) return;
        const form = document.getElementById(inputId)?.closest("form");
        if (form) form.setAttribute("data-rich-text-editor-ready", "true");
    }, [editor, inputId]);

    if (!editor) return null;

    const openLinkPanel = (): void => {
        const selection = editor.state.selection;
        setLinkText(selection.empty ? "" : editor.state.doc.textBetween(selection.from, selection.to, " "));
        setLinkUrl(editor.getAttributes("link").href ?? "");
        setLinkPanelOpen(true);
    };

    const applyLink = (): void => {
        const safeUrl = normalizeUrl(linkUrl);
        if (!safeUrl) return;
        const chain = editor.chain().focus();
        if (editor.state.selection.empty && linkText.trim() !== "") {
            chain.insertContent({type: "text", text: linkText.trim(), marks: [{type: "link", attrs: {href: safeUrl, target: "_blank"}}]}).run();
        } else {
            chain.extendMarkRange("link").setLink({href: safeUrl, target: "_blank"}).run();
        }
        setLinkPanelOpen(false);
    };

    const toolbarDisabled = disabled || readOnly;

    return <div className={`rich-text-editor overflow-visible rounded-lg border bg-background shadow-xs focus-within:border-ring focus-within:ring-2 focus-within:ring-ring/30 ${disabled ? "cursor-not-allowed opacity-60" : ""} ${invalid ? "border-destructive ring-2 ring-destructive/20" : "border-input"}`} aria-disabled={disabled} aria-invalid={invalid}>
        <div className="relative flex flex-wrap items-center gap-1 border-b border-border bg-muted/40 p-2" role="toolbar" aria-label="Mise en forme du contenu">
            <div className="flex items-center gap-1" role="group" aria-label="Structure">
                <ToolbarButton label="Paragraphe" disabled={toolbarDisabled} pressed={editor.isActive("paragraph")} onClick={() => editor.chain().focus().setParagraph().run()}><span className="text-xs font-semibold">P</span></ToolbarButton>
                <ToolbarButton label="Titre 2" disabled={toolbarDisabled} pressed={editor.isActive("heading", {level: 2})} onClick={() => editor.chain().focus().toggleHeading({level: 2}).run()}><span className="text-xs font-semibold">H2</span></ToolbarButton>
                <ToolbarButton label="Titre 3" disabled={toolbarDisabled} pressed={editor.isActive("heading", {level: 3})} onClick={() => editor.chain().focus().toggleHeading({level: 3}).run()}><span className="text-xs font-semibold">H3</span></ToolbarButton>
            </div>
            <span className="mx-1 h-5 w-px bg-border" role="separator" aria-orientation="vertical" />
            <div className="flex items-center gap-1" role="group" aria-label="Caractères">
                <ToolbarButton label="Gras" disabled={toolbarDisabled} pressed={editor.isActive("bold")} onClick={() => editor.chain().focus().toggleBold().run()}><Bold className="size-4" aria-hidden="true" /></ToolbarButton>
                <ToolbarButton label="Italique" disabled={toolbarDisabled} pressed={editor.isActive("italic")} onClick={() => editor.chain().focus().toggleItalic().run()}><Italic className="size-4" aria-hidden="true" /></ToolbarButton>
                <ToolbarButton label="Souligné" disabled={toolbarDisabled} pressed={editor.isActive("underline")} onClick={() => editor.chain().focus().toggleUnderline().run()}><UnderlineIcon className="size-4" aria-hidden="true" /></ToolbarButton>
                <ToolbarButton label="Barré" disabled={toolbarDisabled} pressed={editor.isActive("strike")} onClick={() => editor.chain().focus().toggleStrike().run()}><Strikethrough className="size-4" aria-hidden="true" /></ToolbarButton>
            </div>
            <span className="mx-1 h-5 w-px bg-border" role="separator" aria-orientation="vertical" />
            <div className="flex items-center gap-1" role="group" aria-label="Listes et citation">
                <ToolbarButton label="Liste à puces" disabled={toolbarDisabled} pressed={editor.isActive("bulletList")} onClick={() => editor.chain().focus().toggleBulletList().run()}><List className="size-4" aria-hidden="true" /></ToolbarButton>
                <ToolbarButton label="Liste numérotée" disabled={toolbarDisabled} pressed={editor.isActive("orderedList")} onClick={() => editor.chain().focus().toggleOrderedList().run()}><ListOrdered className="size-4" aria-hidden="true" /></ToolbarButton>
                <ToolbarButton label="Citation" disabled={toolbarDisabled} pressed={editor.isActive("blockquote")} onClick={() => editor.chain().focus().toggleBlockquote().run()}><Quote className="size-4" aria-hidden="true" /></ToolbarButton>
            </div>
            <span className="mx-1 h-5 w-px bg-border" role="separator" aria-orientation="vertical" />
            <div className="flex items-center gap-1" role="group" aria-label="Liens et historique">
                <ToolbarButton label="Ajouter ou modifier un lien" disabled={toolbarDisabled} pressed={editor.isActive("link")} onClick={openLinkPanel}><LinkIcon className="size-4" aria-hidden="true" /></ToolbarButton>
                <ToolbarButton label="Retirer le lien" disabled={toolbarDisabled || !editor.isActive("link")} onClick={() => editor.chain().focus().unsetLink().run()}><Unlink className="size-4" aria-hidden="true" /></ToolbarButton>
                <ToolbarButton label="Annuler" disabled={toolbarDisabled || !editor.can().undo()} onClick={() => editor.chain().focus().undo().run()}><Undo2 className="size-4" aria-hidden="true" /></ToolbarButton>
                <ToolbarButton label="Rétablir" disabled={toolbarDisabled || !editor.can().redo()} onClick={() => editor.chain().focus().redo().run()}><Redo2 className="size-4" aria-hidden="true" /></ToolbarButton>
            </div>
            {linkPanelOpen && <div className="absolute left-2 top-full z-20 mt-2 grid w-[min(22rem,calc(100vw-2rem))] gap-3 rounded-lg border border-border bg-popover p-3 text-popover-foreground shadow-lg" role="dialog" aria-label="Configurer le lien">
                <label className="grid gap-1 text-xs font-medium">Texte du lien<input value={linkText} onChange={(event) => setLinkText(event.target.value)} className="h-9 rounded-md border border-input bg-background px-2 text-sm font-normal outline-none focus-visible:ring-2 focus-visible:ring-ring" /></label>
                <label className="grid gap-1 text-xs font-medium">URL<input value={linkUrl} onChange={(event) => setLinkUrl(event.target.value)} placeholder="https://…" className="h-9 rounded-md border border-input bg-background px-2 text-sm font-normal outline-none focus-visible:ring-2 focus-visible:ring-ring" /></label>
                <div className="flex justify-end gap-2"><button type="button" onClick={() => setLinkPanelOpen(false)} className="rounded-md px-3 py-2 text-xs font-medium text-muted-foreground hover:bg-muted">Annuler</button><button type="button" onClick={applyLink} className="rounded-md bg-primary px-3 py-2 text-xs font-medium text-primary-foreground hover:bg-primary/90">Appliquer</button></div>
            </div>}
        </div>
        <EditorContent editor={editor} />
    </div>;
};

export default RichTextEditor;
