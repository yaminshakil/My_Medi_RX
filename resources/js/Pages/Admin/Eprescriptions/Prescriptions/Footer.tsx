export default function Footer({ content }) {
    return <div className="ql-editor text-center" dangerouslySetInnerHTML={{ __html: content }} />;
}
