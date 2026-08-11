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
                'url' => 'https://twoserendraofficial-my.sharepoint.com/personal/itdept_twoserendra_com/Documents/Metro%20Manila%20Subway%20Project/Cir%202026-027%20Metro%20Manila%20Subway%20Project.pdf?CT=1786430060136&OR=ItemsView',
            ],
            [
                'title' => "Circular 2026-075\nDOTR Subway Project Update\nMay 29, 2026",
                'url' => 'https://twoserendraofficial-my.sharepoint.com/personal/itdept_twoserendra_com/Documents/Metro%20Manila%20Subway%20Project/Cir%202026-075%20DOTR%20Subway%20Project%20Update.pdf?CT=1786430013744&OR=ItemsView',
            ],
            [
                'title' => "Circular 2026-085\nAdvisory on Tree Cutting and Earth Balling\nJune 19, 2026",
                'url' => 'https://twoserendraofficial-my.sharepoint.com/personal/itdept_twoserendra_com/_layouts/15/onedrive.aspx?ga=1&id=%2Fpersonal%2Fitdept%5Ftwoserendra%5Fcom%2FDocuments%2FMetro%20Manila%20Subway%20Project%2FCir%202026%2D085%20Advisory%20on%20Tree%20Cutting%20and%5FEarth%20Balling%20%2D%20Metro%20Manila%20Subway%20Project%2Epdf&parent=%2Fpersonal%2Fitdept%5Ftwoserendra%5Fcom%2FDocuments%2FMetro%20Manila%20Subway%20Project',
            ],
            [
                'title' => "Circular 2026-101\nTree Cutting Activities Along McKinley Parkway\nJuly 17, 2026",
                'url' => 'https://twoserendraofficial-my.sharepoint.com/personal/itdept_twoserendra_com/_layouts/15/onedrive.aspx?ga=1&id=%2Fpersonal%2Fitdept%5Ftwoserendra%5Fcom%2FDocuments%2FMetro%20Manila%20Subway%20Project%2FCirc%202026%2D101%20Tree%20Cutting%20Activities%20along%20McKinley%20Parkway%2Epdf&parent=%2Fpersonal%2Fitdept%5Ftwoserendra%5Fcom%2FDocuments%2FMetro%20Manila%20Subway%20Project',
            ],
        ];
    @endphp

    <div class="my-2 mb-3">

        <a href="{{ route('subway.faqs.mobile') }}" class="btn btn-success w-100 py-3">

            <i class="bx bx-help-circle me-1"></i>
            Frequently Asked Questions

        </a>

    </div>
    @foreach($documents as $document)

        <a href="{{ $document['url'] }}" class="text-decoration-none">

            <div class="card border-0 shadow-sm mb-3 document-card">

                <div class="card-body py-3">

                    <div class="d-flex align-items-center">

                        <div class="document-icon me-3">
                            <i class="bx bxs-file-pdf text-danger"></i>
                        </div>

                        <div class="flex-grow-1 pe-2">

                            <div class="fw-semibold text-dark document-title">
                                {!! nl2br(e($document['title'])) !!}
                            </div>

                            <small class="text-muted d-block mt-2">
                                <i class="bx bx-file me-1"></i>
                                Tap to view documents
                            </small>

                        </div>

                        <i class="bx bx-chevron-right text-muted fs-4"></i>

                    </div>

                </div>

            </div>

        </a>

    @endforeach


</div>

<style>
    .document-card {
        transition: transform 0.15s ease, box-shadow 0.15s ease;
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