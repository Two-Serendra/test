@extends('layouts.app')

<div class="container py-3">

    <div class="text-center mb-4">
        <div class="mb-2">
            <i class="bx bx-folder-open text-success" style="font-size: 42px;"></i>
        </div>

        <h4 class="fw-bold text-success mb-1">
            Subway Project
        </h4>

        <small class="text-muted">
            Important notices and updates for Two Serendra residents
        </small>
    </div>

    @php
        $documents = [
            [
                'title' => "Circular 2026-027\nMetro Manila Subway Project – BGC Station\nFebruary 19, 2026",
                'file' => 'Cir 2026-027 Metro Manila Subway Project.pdf',
            ],
            [
                'title' => "Circular 2026-075\nDOTR Subway Project Update\nMay 29, 2026",
                'file' => 'Cir 2026-075 DOTR Subway Project Update.pdf',
            ],
            [
                'title' => "Circular 2026-085\nAdvisory on Tree Cutting and Earth Balling\nJune 19, 2026",
                'file' => 'Cir 2026-085 Advisory on Tree Cutting and_Earth Balling - Metro Manila Subway Project.pdf',
            ],
            [
                'title' => "Circular 2026-101\nTree Cutting Activities Along McKinley Parkway\nJuly 17, 2026",
                'file' => 'Circ 2026-101 Tree Cutting Activities along McKinley Parkway.pdf',
            ],
        ];
    @endphp

    {{-- FAQ BUTTON --}}
    <div class="my-2 mb-3">

        <a href="{{ route('subway.faqs.mobile') }}" class="btn btn-success w-100 py-3">
            <i class="bx bx-help-circle me-1"></i>
            Frequently Asked Questions
        </a>

    </div>

    {{-- DOCUMENTS --}}
    @foreach($documents as $document)

        <a href="{{ route('mobile.pdf.viewer', ['filename' => $document['file']]) }}" class="text-decoration-none">

            <div class="card border-0 shadow-sm mb-3 document-card">

                <div class="card-body py-3">

                    <div class="d-flex align-items-center">

                        {{-- PDF ICON --}}
                        <div class="document-icon me-3">

                            <i class="bx bxs-file-pdf text-danger"></i>

                        </div>

                        {{-- DOCUMENT INFORMATION --}}
                        <div class="flex-grow-1 pe-2">

                            <div class="fw-semibold text-dark document-title">
                                {!! nl2br(e($document['title'])) !!}
                            </div>

                            <small class="text-muted d-block mt-2">

                                <i class="bx bx-file me-1"></i>

                                Tap to view document

                            </small>

                        </div>

                        {{-- ARROW --}}
                        <i class="bx bx-chevron-right text-muted fs-4"></i>

                    </div>

                </div>

            </div>

        </a>

    @endforeach

</div>

<style>
    .document-card {
        transition:
            transform 0.15s ease,
            box-shadow 0.15s ease;

        border-radius: 12px;
    }

    .document-card:active {
        transform: scale(0.98);
    }

    .document-icon {
        width: 44px;
        min-width: 44px;
        height: 44px;

        border-radius: 10px;

        background: #fff1f1;

        display: flex;
        align-items: center;
        justify-content: center;
    }

    .document-icon i {
        font-size: 28px;
    }

    .document-title {
        line-height: 1.45;
        font-size: 14px;
    }
</style>