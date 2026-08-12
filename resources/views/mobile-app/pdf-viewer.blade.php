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

    <div id="pdf-container" data-pdf-url="{{ route('mobile.pdf.file', ['filename' => $filename]) }}">
        <div id="loading">
            Loading PDF...
        </div>
    </div>
</body>

</html>