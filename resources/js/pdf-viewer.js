console.log('🔥 PDF VIEWER JS LOADED');

import * as pdfjsLib from 'pdfjs-dist';

pdfjsLib.GlobalWorkerOptions.workerSrc = new URL(
    'pdfjs-dist/build/pdf.worker.mjs',
    import.meta.url
).toString();

const container = document.getElementById('pdf-container');

console.log('PDF container:', container);

const pdfUrl = container?.dataset.pdfUrl;

console.log('Loading PDF:', pdfUrl);

async function loadPdf() {
    const loading = document.getElementById('loading');

    try {
        if (!pdfUrl) {
            throw new Error('PDF URL is missing.');
        }

        const loadingTask = pdfjsLib.getDocument({
            url: pdfUrl
        });

        loadingTask.onProgress = (progress) => {
            console.log('PDF progress:', progress);
        };

        const pdf = await loadingTask.promise;

        console.log('PDF loaded:', pdf.numPages);

        loading.remove();

        for (
            let pageNumber = 1;
            pageNumber <= pdf.numPages;
            pageNumber++
        ) {
            await renderPage(pdf, pageNumber);
        }

    } catch (error) {
        console.error('PDF loading error:', error);

        loading.innerHTML = `
            <div id="error">
                Failed to load PDF.
                <br>
                <small>${error.message}</small>
            </div>
        `;
    }
}

async function renderPage(pdf, pageNumber) {

    const page = await pdf.getPage(pageNumber);

    const viewport = page.getViewport({
        scale: 1.5
    });

    const canvas = document.createElement('canvas');

    canvas.className = 'pdf-page';

    const context = canvas.getContext('2d');

    canvas.width = viewport.width;
    canvas.height = viewport.height;

    container.appendChild(canvas);

    await page.render({
        canvasContext: context,
        viewport: viewport
    }).promise;

}

loadPdf();