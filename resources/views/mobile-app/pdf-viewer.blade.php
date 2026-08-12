<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <title>PDF Viewer</title>

    @vite('resources/js/pdf-viewer.js')

    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background: #525659;
        }

        #pdf-container {
            width: 100%;
            height: 100%;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 10px;
            box-sizing: border-box;
        }

        .pdf-page {
            display: block;
            margin: 0 auto 15px auto;
            max-width: 100%;
            height: auto;
            background: white;
        }

        #loading {
            color: white;
            text-align: center;
            padding: 30px;
            font-family: Arial, sans-serif;
        }

        #error {
            color: #ffdddd;
            text-align: center;
            padding: 30px;
            font-family: Arial, sans-serif;
        }
    </style>
</head>

<body>

    <div id="pdf-container">
        <div id="loading">
            Loading PDF...
        </div>
    </div>

    <script>
        const pdfUrl = @json(route('mobile.pdf.file', ['filename' => $filename]));

        async function loadPdf() {
            const container = document.getElementById('pdf-container');
            const loading = document.getElementById('loading');

            try {
                const pdf = await window.pdfjsLib.getDocument(pdfUrl).promise;

                loading.remove();

                console.log('PDF loaded:', pdf.numPages);

                for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
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

            document.getElementById('pdf-container').appendChild(canvas);

            await page.render({
                canvasContext: context,
                viewport: viewport
            }).promise;
        }

        loadPdf();
    </script>

</body>

</html>