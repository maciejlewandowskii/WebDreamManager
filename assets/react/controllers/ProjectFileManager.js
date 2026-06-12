import React, { useEffect, useState, useCallback } from 'react';
import { createRoot } from 'react-dom/client';
import { Filemanager, WillowDark } from '@svar-ui/react-filemanager';
import { RestDataProvider } from '@svar-ui/filemanager-data-provider';
import { Document, Page, pdfjs } from 'react-pdf';

pdfjs.GlobalWorkerOptions.workerSrc = `https://unpkg.com/pdfjs-dist@${pdfjs.version}/build/pdf.worker.min.mjs`;

function PdfViewer({ url }) {
    const [numPages, setNumPages] = useState(null);
    const [pageNumber, setPageNumber] = useState(1);

    useEffect(() => {
        setPageNumber(1);
        setNumPages(null);
    }, [url]);

    const loadingEl = React.createElement('div', {
        style: { padding: '2rem', textAlign: 'center', opacity: 0.7, fontSize: '0.9rem', color: 'var(--color-on-surface)' }
    }, 'Loading PDF...');

    return React.createElement('div', { style: { width: '100%', maxHeight: '350px', overflowY: 'auto', display: 'flex', flexDirection: 'column', alignItems: 'center', background: 'var(--color-surface-container-low)', borderRadius: '8px', padding: '10px' } },
        React.createElement(Document, {
            file: url,
            onLoadSuccess: ({ numPages }) => setNumPages(numPages),
            loading: loadingEl
        },
            React.createElement(Page, {
                pageNumber: pageNumber,
                renderTextLayer: false,
                renderAnnotationLayer: false,
                width: 220
            })
        ),
        numPages && React.createElement('div', { style: { marginTop: '10px', display: 'flex', gap: '10px', alignItems: 'center', fontSize: '12px', color: 'var(--color-on-surface)' } },
            React.createElement('button', {
                disabled: pageNumber <= 1,
                onClick: () => setPageNumber(p => p - 1),
                style: { padding: '2px 8px', borderRadius: '4px', border: '1px solid var(--color-outline)', background: 'var(--color-surface-container-high)', color: 'var(--color-on-surface)', cursor: pageNumber <= 1 ? 'default' : 'pointer', opacity: pageNumber <= 1 ? 0.5 : 1 }
            }, 'Prev'),
            React.createElement('span', null, `${pageNumber} of ${numPages}`),
            React.createElement('button', {
                disabled: pageNumber >= numPages,
                onClick: () => setPageNumber(p => p + 1),
                style: { padding: '2px 8px', borderRadius: '4px', border: '1px solid var(--color-outline)', background: 'var(--color-surface-container-high)', color: 'var(--color-on-surface)', cursor: pageNumber >= numPages ? 'default' : 'pointer', opacity: pageNumber >= numPages ? 0.5 : 1 }
            }, 'Next')
        )
    );
}

export default function ProjectFileManager({ apiUrl, projectName }) {
    const [data, setData]   = useState([]);
    const [drive, setDrive] = useState({});
    const [error, setError] = useState(null);

    const provider = React.useMemo(() => new RestDataProvider(apiUrl), [apiUrl]);

    useEffect(() => {
        Promise.all([provider.loadFiles(), provider.loadInfo()])
            .then(([files, info]) => {
                setData(files);
                setDrive(info.stats ?? {});
            })
            .catch(() => setError('Failed to load files.'));
    }, [provider]);

    useEffect(() => {
        const timer = setInterval(() => {
            if (!projectName) return;
            const items = document.querySelectorAll('.wx-item, .wx-name, .wx-name-cell, .wx-folder .wx-name');
            items.forEach(el => {
                if (el.textContent.trim() === 'My files') {
                    el.textContent = projectName;
                }
            });
        }, 50);
        return () => clearInterval(timer);
    }, [projectName]);

    useEffect(() => {
        const updateOffset = () => {
            const fm = document.querySelector('.wdm-filemanager-wrapper');
            if (fm) {
                const rect = fm.getBoundingClientRect();
                document.documentElement.style.setProperty('--fm-top', `${rect.top + window.scrollY}px`);
                document.documentElement.style.setProperty('--fm-left', `${rect.left + window.scrollX}px`);
                document.documentElement.style.setProperty('--fm-width', `${rect.width}px`);
                document.documentElement.style.setProperty('--fm-height', `${rect.height}px`);
            }
        };
        updateOffset();
        window.addEventListener('resize', updateOffset);
        window.addEventListener('scroll', updateOffset);
        const timer = setInterval(updateOffset, 1000); // Poll just in case sidebar toggles
        return () => {
            window.removeEventListener('resize', updateOffset);
            window.removeEventListener('scroll', updateOffset);
            clearInterval(timer);
        };
    }, []);

    const apiRef = React.useRef(null);

    const init = useCallback((api) => {
        api.setNext(provider);
        apiRef.current = api;

        api.intercept("download-file", (ev) => {
            const id = ev.id || (api.getState().activePanel && api.getState().panels[api.getState().activePanel].selected[0]);
            if (id) {
                const downloadUrl = `${apiUrl}/files${id}?download=true`;
                window.location.href = downloadUrl;
            }
            return false;
        });
    }, [provider, apiUrl]);

    useEffect(() => {
        const handleGlobalClick = (e) => {
            const item = e.target.closest('.wx-item');
            if (item && item.textContent.trim() === 'Download' && apiRef.current) {
                const api = apiRef.current;
                const id = api.getState().activePanel && api.getState().panels[api.getState().activePanel]?.selected[0];
                if (id) {
                    const downloadUrl = `${apiUrl}/files${id}?download=true`;
                    window.location.href = downloadUrl;
                }
            }
        };
        document.addEventListener('click', handleGlobalClick);
        return () => document.removeEventListener('click', handleGlobalClick);
    }, [apiUrl]);

    const previews = useCallback((file) => {
        if (!file || file.type === 'folder') return null;
        if (file.name.toLowerCase().match(/\.(jpg|jpeg|png|gif|svg|webp)$/)) {
            return `${apiUrl}/files${file.id}`;
        }
        return null;
    }, [apiUrl]);

    useEffect(() => {
        const timer = setInterval(() => {
            const infoPanel = document.querySelector('.wx-info-panel');
            if (!infoPanel) return;

            const previewContainer = infoPanel.previousElementSibling;
            if (!previewContainer || !previewContainer.classList.contains('wx-preview')) return;

            const selectedItem = document.querySelector('.wx-item.wx-selected');
            if (!selectedItem) return;

            let id = selectedItem.getAttribute('data-id');
            if (!id) return;

            // Remove the ':/' prefix if it exists to match the backend path structure
            if (id.startsWith(':/')) {
                id = '/' + id.substring(2);
            }

            if (id.toLowerCase().endsWith('.pdf')) {
                const url = `${apiUrl}/files${id}`;
                const existing = previewContainer.querySelector('.wdm-pdf-preview');

                if (!existing) {
                    // Hide original icon
                    const iconWrapper = previewContainer.querySelector('.wx-icon-wrapper');
                    if (iconWrapper) iconWrapper.style.display = 'none';

                    const wrapper = document.createElement('div');
                    wrapper.className = 'wdm-pdf-preview';
                    wrapper.style.width = '100%';
                    wrapper.style.marginTop = '10px';
                    previewContainer.appendChild(wrapper);
                    previewContainer.style.height = 'max-content';

                    const root = createRoot(wrapper);
                    root.render(React.createElement(PdfViewer, { url: url }));
                    wrapper.__reactRoot = root;
                    wrapper.__pdfUrl = url;
                } else if (existing.__pdfUrl !== url) {
                    // Update if switching to a different PDF
                    existing.__pdfUrl = url;
                    if (existing.__reactRoot) {
                        existing.__reactRoot.render(React.createElement(PdfViewer, { url: url }));
                    }
                }
            } else {
                const existing = document.querySelector('.wdm-pdf-preview');
                if (existing) {
                    if (existing.__reactRoot) {
                        existing.__reactRoot.unmount();
                    }
                    existing.remove();
                }
                const iconWrapper = previewContainer.querySelector('.wx-icon-wrapper');
                if (iconWrapper) iconWrapper.style.display = '';
            }
        }, 200);
        return () => clearInterval(timer);
    }, [apiUrl]);

    if (error) {
        return React.createElement('div', { style: { padding: '1rem', color: 'rgb(var(--destructive))', fontSize: '0.875rem' } }, error);
    }

    return React.createElement(WillowDark, null,
        React.createElement('div', { className: 'wdm-filemanager' },
            React.createElement(Filemanager, { data: data, drive: drive, init: init, previews: previews })
        )
    );
}
